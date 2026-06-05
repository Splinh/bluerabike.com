<?php
/**
 * Gateway response cache.
 *
 * @package SPLAT\Gateway
 */

namespace SPLAT\Gateway;

defined( 'ABSPATH' ) || exit;

use SPLAT\DB;
use SPLAT\Gateway\DTO\GatewayResponse;
use SPLAT\Gateway\DTO\GatewayUsage;

final class ResponseCache {

	public const TABLE = 'splat_response_cache';

	/**
	 * Get a cached response by hash key.
	 *
	 * @param string $hashKey SHA-256 hex string (64 chars).
	 *
	 * @return GatewayResponse|null
	 */
	public static function get( string $hashKey ): ?GatewayResponse {
		$table = DB::backtickedTable( self::TABLE );
		$now   = current_time( 'mysql', true );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = DB::db()->get_row(
			DB::db()->prepare( "SELECT * FROM {$table} WHERE hash_key = %s AND expires_at > %s LIMIT 1", $hashKey, $now ),
			ARRAY_A
		);

		if ( ! $row || empty( $row['response_json'] ) ) {
			return null;
		}

		$data = json_decode( $row['response_json'], true );
		if ( ! is_array( $data ) ) {
			return null;
		}

		return self::hydrateResponse( $data, (string) ( $row['provider'] ?? '' ), (string) ( $row['model'] ?? '' ) );
	}

	/**
	 * Store a response in cache.
	 *
	 * @param string          $hashKey    SHA-256 hex string.
	 * @param GatewayResponse $response   The response to cache.
	 * @param int             $ttlSeconds Cache lifetime in seconds.
	 *
	 * @return int|\WP_Error
	 */
	public static function put( string $hashKey, GatewayResponse $response, int $ttlSeconds = 86400 ): int|\WP_Error {
		$table     = DB::backtickedTable( self::TABLE );
		$now       = current_time( 'mysql', true );
		$expiresAt = gmdate( 'Y-m-d H:i:s', time() + max( 1, $ttlSeconds ) );

		$data = wp_json_encode( $response->toOpenAiArray() );
		if ( false === $data ) {
			return new \WP_Error( 'cache_encode_error', 'Failed to encode response for cache.' );
		}

		$totalTokens = 0;
		if ( $response->usage ) {
			$totalTokens = $response->usage->totalTokens;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = DB::db()->query(
			DB::db()->prepare(
				"REPLACE INTO {$table} (hash_key, provider, model, response_json, tokens_used, created_at, expires_at) VALUES (%s, %s, %s, %s, %d, %s, %s)",
				$hashKey,
				$response->provider,
				$response->model,
				$data,
				$totalTokens,
				$now,
				$expiresAt
			)
		);

		return false === $result ? new \WP_Error( 'cache_write_error', 'Failed to write cache row.' ) : (int) $result;
	}

	/**
	 * Delete expired cache rows.
	 *
	 * @return int|false Rows deleted or false on error.
	 */
	public static function gc(): int|false {
		$table = DB::backtickedTable( self::TABLE );
		$now   = current_time( 'mysql', true );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return DB::db()->query( DB::db()->prepare( "DELETE FROM {$table} WHERE expires_at <= %s", $now ) );
	}

	/**
	 * Truncate the entire cache table.
	 *
	 * @return int|false
	 */
	public static function flush(): int|false {
		$table = DB::backtickedTable( self::TABLE );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return DB::db()->query( "TRUNCATE TABLE {$table}" );
	}

	/**
	 * Hydrate a GatewayResponse from cached OpenAI-format data.
	 *
	 * @param array  $data     Decoded response_json.
	 * @param string $provider Cached provider.
	 * @param string $model    Cached model.
	 *
	 * @return GatewayResponse
	 */
	private static function hydrateResponse( array $data, string $provider, string $model ): GatewayResponse {
		$content      = '';
		$finishReason = 'stop';
		$toolCalls    = [];

		if ( ! empty( $data['choices'][0]['message']['content'] ) ) {
			$content = $data['choices'][0]['message']['content'];
		}
		if ( ! empty( $data['choices'][0]['finish_reason'] ) ) {
			$finishReason = $data['choices'][0]['finish_reason'];
		}
		if ( ! empty( $data['choices'][0]['message']['tool_calls'] ) && is_array( $data['choices'][0]['message']['tool_calls'] ) ) {
			$toolCalls = $data['choices'][0]['message']['tool_calls'];
		}

		$usage = null;
		if ( ! empty( $data['usage'] ) ) {
			$usage = new GatewayUsage(
				promptTokens:     (int) ( $data['usage']['prompt_tokens'] ?? 0 ),
				completionTokens: (int) ( $data['usage']['completion_tokens'] ?? 0 ),
				totalTokens:      (int) ( $data['usage']['total_tokens'] ?? 0 ),
			);
		}

		return new GatewayResponse(
			content:      $content,
			provider:     $provider,
			model:        $model,
			usage:        $usage,
			finishReason: $finishReason,
			rawId:        $data['id'] ?? null,
			created:      isset( $data['created'] ) ? (int) $data['created'] : null,
			toolCalls:    $toolCalls,
		);
	}
}

