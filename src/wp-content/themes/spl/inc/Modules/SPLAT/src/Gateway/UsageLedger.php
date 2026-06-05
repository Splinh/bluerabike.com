<?php
/**
 * Metadata-only AI usage ledger.
 *
 * @package SPLAT\Gateway
 */

namespace SPLAT\Gateway;

defined( 'ABSPATH' ) || exit;

use SPLAT\DB;
use SPLAT\Gateway\DTO\GatewayResponse;

final class UsageLedger {

	public const TABLE = 'splat_usage_ledger';

	/**
	 * @param GatewayResponse $response
	 * @param int|null        $consumerTokenId
	 * @param int             $durationMs
	 *
	 * @return int|\WP_Error
	 */
	public static function recordSuccess( GatewayResponse $response, ?int $consumerTokenId = null, int $durationMs = 0 ): int|\WP_Error {
		$usage = $response->usage?->toArray() ?? [];

		return self::record(
			[
				'consumer_token_id' => $consumerTokenId,
				'credential_id'     => $response->credentialId,
				'provider'          => $response->provider,
				'model'             => $response->model,
				'status'            => 'success',
				'prompt_tokens'     => (int) ( $usage['prompt_tokens'] ?? 0 ),
				'completion_tokens' => (int) ( $usage['completion_tokens'] ?? 0 ),
				'total_tokens'      => (int) ( $usage['total_tokens'] ?? 0 ),
				'duration_ms'       => $durationMs,
			]
		);
	}

	/**
	 * @param array     $credential
	 * @param \WP_Error $error
	 * @param int|null  $consumerTokenId
	 * @param int       $durationMs
	 *
	 * @return int|\WP_Error
	 */
	public static function recordError( array $credential, \WP_Error $error, ?int $consumerTokenId = null, int $durationMs = 0 ): int|\WP_Error {
		$data = $error->get_error_data();

		return self::record(
			[
				'consumer_token_id' => $consumerTokenId,
				'credential_id'     => isset( $credential['id'] ) ? (int) $credential['id'] : null,
				'provider'          => (string) ( $credential['provider'] ?? '' ),
				'model'             => (string) ( $credential['default_model'] ?? '' ),
				'status'            => 'error',
				'error_category'    => sanitize_key( (string) ( $data['category'] ?? $error->get_error_code() ) ),
				'duration_ms'       => $durationMs,
			]
		);
	}

	/**
	 * Record a cache-hit entry in the usage ledger.
	 *
	 * @param GatewayResponse $response
	 * @param int|null        $consumerTokenId
	 *
	 * @return int|\WP_Error
	 */
	public static function recordCacheHit( GatewayResponse $response, ?int $consumerTokenId = null ): int|\WP_Error {
		return self::record(
			[
				'consumer_token_id' => $consumerTokenId,
				'provider'          => $response->provider,
				'model'             => $response->model,
				'status'            => 'cache_hit',
				'duration_ms'       => 0,
			]
		);
	}

	/**
	 * @param array $credential
	 *
	 * @return bool
	 */
	public static function providerBudgetExceeded( array $credential ): bool {
		$credentialId = isset( $credential['id'] ) ? (int) $credential['id'] : 0;
		if ( $credentialId <= 0 ) {
			return false;
		}

		$dailyTokenLimit   = isset( $credential['daily_token_limit'] ) ? (int) $credential['daily_token_limit'] : 0;
		$monthlyTokenLimit = isset( $credential['monthly_token_limit'] ) ? (int) $credential['monthly_token_limit'] : 0;
		$dailyCostLimit    = isset( $credential['daily_cost_limit'] ) ? (float) $credential['daily_cost_limit'] : 0;
		$monthlyCostLimit  = isset( $credential['monthly_cost_limit'] ) ? (float) $credential['monthly_cost_limit'] : 0;
		$dayStart          = gmdate( 'Y-m-d 00:00:00' );
		$monthStart        = gmdate( 'Y-m-01 00:00:00' );

		if ( $dailyTokenLimit > 0 && self::sumTokens( 'credential_id', $credentialId, $dayStart ) >= $dailyTokenLimit ) {
			return true;
		}

		if ( $monthlyTokenLimit > 0 && self::sumTokens( 'credential_id', $credentialId, $monthStart ) >= $monthlyTokenLimit ) {
			return true;
		}

		if ( $dailyCostLimit > 0 && self::sumCost( 'credential_id', $credentialId, $dayStart ) >= $dailyCostLimit ) {
			return true;
		}

		if ( $monthlyCostLimit > 0 && self::sumCost( 'credential_id', $credentialId, $monthStart ) >= $monthlyCostLimit ) {
			return true;
		}

		return false;
	}

	/**
	 * @param array $consumerToken
	 *
	 * @return bool
	 */
	public static function consumerBudgetExceeded( array $consumerToken ): bool {
		$consumerTokenId = isset( $consumerToken['id'] ) ? (int) $consumerToken['id'] : 0;
		if ( $consumerTokenId <= 0 ) {
			return false;
		}

		$dailyTokenLimit   = isset( $consumerToken['daily_token_limit'] ) ? (int) $consumerToken['daily_token_limit'] : 0;
		$monthlyTokenLimit = isset( $consumerToken['monthly_token_limit'] ) ? (int) $consumerToken['monthly_token_limit'] : 0;
		$dayStart          = gmdate( 'Y-m-d 00:00:00' );
		$monthStart        = gmdate( 'Y-m-01 00:00:00' );

		if ( $dailyTokenLimit > 0 && self::sumTokens( 'consumer_token_id', $consumerTokenId, $dayStart ) >= $dailyTokenLimit ) {
			return true;
		}

		if ( $monthlyTokenLimit > 0 && self::sumTokens( 'consumer_token_id', $consumerTokenId, $monthStart ) >= $monthlyTokenLimit ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $consumerTokenId
	 *
	 * @return array{daily_tokens: int, monthly_tokens: int}
	 */
	public static function consumerTokenUsage( int $consumerTokenId ): array {
		if ( $consumerTokenId <= 0 ) {
			return [
				'daily_tokens'   => 0,
				'monthly_tokens' => 0,
			];
		}

		return [
			'daily_tokens'   => self::sumTokens( 'consumer_token_id', $consumerTokenId, gmdate( 'Y-m-d 00:00:00' ) ),
			'monthly_tokens' => self::sumTokens( 'consumer_token_id', $consumerTokenId, gmdate( 'Y-m-01 00:00:00' ) ),
		];
	}

	/**
	 * @param int $days
	 *
	 * @return int|false
	 */
	public static function cleanupDetailedRows( int $days = 90 ): int|false {
		$table  = DB::backtickedTable( self::TABLE );
		$before = gmdate( 'Y-m-d H:i:s', time() - ( max( 1, $days ) * DAY_IN_SECONDS ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		return DB::db()->query( DB::db()->prepare( "DELETE FROM {$table} WHERE created_at < %s", $before ) );
	}

	/**
	 * @param array $data
	 *
	 * @return int|\WP_Error
	 */
	private static function record( array $data ): int|\WP_Error {
		return DB::insertOneRow(
			self::TABLE,
			[
				'consumer_token_id' => $data['consumer_token_id'] ?? null,
				'credential_id'     => $data['credential_id'] ?? null,
				'provider'          => sanitize_text_field( (string) ( $data['provider'] ?? '' ) ),
				'model'             => sanitize_text_field( (string) ( $data['model'] ?? '' ) ),
				'request_class'     => 'chat_completions',
				'status'            => sanitize_key( (string) ( $data['status'] ?? '' ) ),
				'error_category'    => sanitize_key( (string) ( $data['error_category'] ?? '' ) ),
				'prompt_tokens'     => max( 0, (int) ( $data['prompt_tokens'] ?? 0 ) ),
				'completion_tokens' => max( 0, (int) ( $data['completion_tokens'] ?? 0 ) ),
				'total_tokens'      => max( 0, (int) ( $data['total_tokens'] ?? 0 ) ),
				'estimated_cost'    => max( 0, (float) ( $data['estimated_cost'] ?? 0 ) ),
				'duration_ms'       => max( 0, (int) ( $data['duration_ms'] ?? 0 ) ),
			]
		);
	}

	/**
	 * @param string $column
	 * @param int    $id
	 * @param string $since
	 *
	 * @return int
	 */
	private static function sumTokens( string $column, int $id, string $since ): int {
		$column = DB::backtickedColumn( $column );
		$table  = DB::backtickedTable( self::TABLE );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		return (int) DB::db()->get_var( DB::db()->prepare( "SELECT COALESCE(SUM(total_tokens), 0) FROM {$table} WHERE {$column} = %d AND created_at >= %s", $id, $since ) );
	}

	/**
	 * @param string $column
	 * @param int    $id
	 * @param string $since
	 *
	 * @return float
	 */
	private static function sumCost( string $column, int $id, string $since ): float {
		$column = DB::backtickedColumn( $column );
		$table  = DB::backtickedTable( self::TABLE );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		return (float) DB::db()->get_var( DB::db()->prepare( "SELECT COALESCE(SUM(estimated_cost), 0) FROM {$table} WHERE {$column} = %d AND created_at >= %s", $id, $since ) );
	}
}

