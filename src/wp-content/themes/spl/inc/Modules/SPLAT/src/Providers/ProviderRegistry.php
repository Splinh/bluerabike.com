<?php
/**
 * Gateway provider capability registry.
 *
 * @package SPLAT\Providers
 */

namespace SPLAT\Providers;

defined( 'ABSPATH' ) || exit;

use SPLAT\Providers as LegacyProviders;

final class ProviderRegistry {

	/** @var array<string, ProviderDefinition>|null */
	private static ?array $definitions = null;

	/**
	 * @return array<string, ProviderDefinition>
	 */
	public static function all(): array {
		if ( self::$definitions !== null ) {
			return self::$definitions;
		}

		$definitions = [];
		foreach ( LegacyProviders::all() as $slug => $provider ) {
			$definitions[ $slug ] = self::fromLegacyProvider( $slug, $provider );
		}

		$definitions['custom_openai_compatible'] = new ProviderDefinition(
			provider:         'custom_openai_compatible',
			label:            'Custom OpenAI-Compatible',
			apiFormat:        'openai_compatible',
			authStrategy:     'bearer',
			defaultBaseUrl:   '',
			defaultModel:     '',
			models:           [],
			capabilities:     self::openAiCompatibleCapabilities(),
			defaultLimits:    self::defaultLimits(),
			modelAliases:     [],
			proxyRouter:      false,
		);

		/**
		 * Filter gateway provider capability definitions.
		 *
		 * @param array<string, ProviderDefinition> $definitions
		 */
		self::$definitions = apply_filters( 'splat_provider_definitions', $definitions );

		return self::$definitions;
	}

	/**
	 * @param string $provider
	 *
	 * @return ProviderDefinition|null
	 */
	public static function get( string $provider ): ?ProviderDefinition {
		return self::all()[ $provider ] ?? null;
	}

	/**
	 * @param string $provider
	 *
	 * @return array<string, bool>
	 */
	public static function capabilitiesFor( string $provider ): array {
		return self::get( $provider )?->capabilities ?? [];
	}

	/**
	 * @return void
	 */
	public static function resetCache(): void {
		self::$definitions = null;
	}

	/**
	 * @param string $slug
	 * @param array  $provider
	 *
	 * @return ProviderDefinition
	 */
	private static function fromLegacyProvider( string $slug, array $provider ): ProviderDefinition {
		$apiFormat   = self::normalizeApiFormat( (string) ( $provider['api_format'] ?? 'openai' ) );
		$models      = array_values( array_map( 'strval', $provider['models'] ?? [] ) );
		$proxyRouter = in_array( $slug, [ 'openrouter', '9router', 'omnirouter' ], true )
			|| ( ( $provider['category'] ?? '' ) === 'router' );

		return new ProviderDefinition(
			provider:         $slug,
			label:            (string) ( $provider['label'] ?? $slug ),
			apiFormat:        $apiFormat,
			authStrategy:     self::authStrategyFor( $apiFormat ),
			defaultBaseUrl:   (string) ( $provider['base_url'] ?? '' ),
			defaultModel:     $models[0] ?? '',
			models:           $models,
			capabilities:     self::capabilitiesForFormat( $apiFormat, $proxyRouter ),
			defaultLimits:    self::defaultLimits(),
			modelAliases:     self::modelAliasesFor( $slug ),
			proxyRouter:      $proxyRouter,
		);
	}

	/**
	 * @param string $format
	 *
	 * @return string
	 */
	private static function normalizeApiFormat( string $format ): string {
		return match ( $format ) {
			'openai', 'openai_compatible' => 'openai_compatible',
			'google', 'google_gemini'     => 'google_gemini',
			'anthropic', 'anthropic_messages' => 'anthropic_messages',
			default                       => sanitize_key( $format ),
		};
	}

	/**
	 * @param string $apiFormat
	 *
	 * @return string
	 */
	private static function authStrategyFor( string $apiFormat ): string {
		return match ( $apiFormat ) {
			'google_gemini'      => 'query_api_key',
			'anthropic_messages' => 'x_api_key',
			default              => 'bearer',
		};
	}

	/**
	 * @param string $apiFormat
	 * @param bool   $proxyRouter
	 *
	 * @return array<string, bool>
	 */
	private static function capabilitiesForFormat( string $apiFormat, bool $proxyRouter ): array {
		return match ( $apiFormat ) {
			'google_gemini'      => [
				'chat_completions' => true,
				'json_object'      => true,
				'json_schema'      => false,
				'tools'            => true,
				'streaming'        => false,
				'usage'            => true,
				'model_list'       => false,
				'proxy_router'     => false,
			],
			'anthropic_messages' => [
				'chat_completions' => true,
				'json_object'      => false,
				'json_schema'      => false,
				'tools'            => true,
				'streaming'        => false,
				'usage'            => true,
				'model_list'       => false,
				'proxy_router'     => false,
			],
			default              => self::openAiCompatibleCapabilities( $proxyRouter ),
		};
	}

	/**
	 * @param bool $proxyRouter
	 *
	 * @return array<string, bool>
	 */
	private static function openAiCompatibleCapabilities( bool $proxyRouter = false ): array {
		return [
			'chat_completions' => true,
			'json_object'      => true,
			'json_schema'      => true,
			'tools'            => true,
			'streaming'        => false,
			'usage'            => true,
			'model_list'       => true,
			'proxy_router'     => $proxyRouter,
		];
	}

	/**
	 * @return array<string, int>
	 */
	private static function defaultLimits(): array {
		return [
			'max_prompt_tokens'     => 0,
			'max_completion_tokens' => 0,
			'max_total_tokens'      => 0,
		];
	}

	/**
	 * @param string $provider
	 *
	 * @return array<string, string>
	 */
	private static function modelAliasesFor( string $provider ): array {
		return match ( $provider ) {
			'openrouter', '9router', 'omnirouter' => [ 'auto' => 'auto' ],
			default                              => [],
		};
	}
}

