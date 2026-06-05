<?php
/**
 * Provider credential selection.
 *
 * @package SPLAT\Gateway
 */

namespace SPLAT\Gateway;

defined( 'ABSPATH' ) || exit;

use SPLAT\Gateway\DTO\GatewayRequest;
use SPLAT\KeyRepository;
use SPLAT\Providers;

final class KeySelector {

	/**
	 * @param GatewayRequest $request
	 * @param string|null    $preferredProvider
	 * @param string|null    $tierPreference
	 *
	 * @return array<int, array>
	 */
	public function candidates( GatewayRequest $request, ?string $preferredProvider = null, ?string $tierPreference = null ): array {
		$candidates = KeyRepository::getActiveKeys( $preferredProvider, $tierPreference );

		$candidates = array_values(
			array_filter(
				$candidates,
				fn( array $row ): bool => $this->isEligible( $row, $request )
			)
		);

		return $this->sortCandidates( $candidates, $request, $preferredProvider, $tierPreference );
	}

	/**
	 * @param array          $row
	 * @param GatewayRequest $request
	 *
	 * @return bool
	 */
	private function isEligible( array $row, GatewayRequest $request ): bool {
		if ( ! CredentialLifecycle::isSelectable( $row ) ) {
			return false;
		}

		$optimizedRequest = RequestOptimizer::optimizeForCredential( $request, $row );
		if ( is_wp_error( CapabilityResolver::validate( $optimizedRequest, $row ) ) ) {
			return false;
		}

		$provider = (string) ( $row['provider'] ?? '' );
		$model    = $request->model ?: $this->defaultModelFor( $row );
		$policy   = $request->routingPolicy;

		$allowedProviders = $this->stringList( $policy['allowed_providers'] ?? [] );
		if ( $allowedProviders && ! in_array( $provider, $allowedProviders, true ) ) {
			return false;
		}

		$excluded = $this->stringList( $policy['excluded_providers'] ?? [] );
		if ( $excluded && in_array( $provider, $excluded, true ) ) {
			return false;
		}

		$allowedModels = $this->stringList( $policy['allowed_models'] ?? [] );
		if ( $allowedModels && ! in_array( $model, $allowedModels, true ) ) {
			return false;
		}

		$maxCostTier = isset( $policy['max_cost_tier'] ) ? sanitize_key( (string) $policy['max_cost_tier'] ) : '';
		if ( 'free' === $maxCostTier && ( $row['tier'] ?? '' ) !== 'free' ) {
			return false;
		}

		$requiredCapabilities = $this->stringList( $policy['required_capabilities'] ?? [] );
		if ( $requiredCapabilities ) {
			$capabilities = CapabilityResolver::resolve( $row );
			foreach ( $requiredCapabilities as $capability ) {
				if ( empty( $capabilities[ sanitize_key( $capability ) ] ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param array<int, array> $candidates
	 * @param GatewayRequest   $request
	 * @param string|null      $preferredProvider
	 * @param string|null      $tierPreference
	 *
	 * @return array<int, array>
	 */
	private function sortCandidates( array $candidates, GatewayRequest $request, ?string $preferredProvider, ?string $tierPreference ): array {
		$strategy = sanitize_key( (string) ( $request->routingPolicy['strategy'] ?? 'priority_first' ) );

		$preferredProviders = $this->stringList( $request->routingPolicy['preferred_providers'] ?? [] );

		usort(
			$candidates,
			static function ( array $a, array $b ) use ( $preferredProvider, $preferredProviders, $tierPreference ): int {
				return [
					self::providerRank( (string) ( $a['provider'] ?? '' ), $preferredProviders ),
					( ( $a['provider'] ?? '' ) === $preferredProvider ) ? 0 : 1,
					( ( $a['tier'] ?? '' ) === $tierPreference ) ? 0 : 1,
					(int) ( $a['priority'] ?? 10 ),
					(int) ( $a['fail_count'] ?? 0 ),
					(string) ( $a['last_used_at'] ?? '' ),
					(int) ( $a['id'] ?? 0 ),
				] <=> [
					self::providerRank( (string) ( $b['provider'] ?? '' ), $preferredProviders ),
					( ( $b['provider'] ?? '' ) === $preferredProvider ) ? 0 : 1,
					( ( $b['tier'] ?? '' ) === $tierPreference ) ? 0 : 1,
					(int) ( $b['priority'] ?? 10 ),
					(int) ( $b['fail_count'] ?? 0 ),
					(string) ( $b['last_used_at'] ?? '' ),
					(int) ( $b['id'] ?? 0 ),
				];
			}
		);

		if ( 'weighted_round_robin' === $strategy && count( $candidates ) > 1 ) {
			$offset = (int) get_transient( 'splat_key_selector_offset' );
			set_transient( 'splat_key_selector_offset', $offset + 1, HOUR_IN_SECONDS );
			$offset = $offset % count( $candidates );

			return array_merge( array_slice( $candidates, $offset ), array_slice( $candidates, 0, $offset ) );
		}

		return $candidates;
	}

	/**
	 * @param string   $provider
	 * @param string[] $preferredProviders
	 *
	 * @return int
	 */
	private static function providerRank( string $provider, array $preferredProviders ): int {
		if ( ! $preferredProviders ) {
			return 0;
		}

		$rank = array_search( $provider, $preferredProviders, true );

		return $rank === false ? count( $preferredProviders ) + 1 : (int) $rank;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string[]
	 */
	private function stringList( mixed $value ): array {
		if ( is_string( $value ) ) {
			$value = array_filter( array_map( 'trim', explode( ',', $value ) ) );
		}

		if ( ! is_array( $value ) ) {
			return [];
		}

		return array_values(
			array_filter(
				array_map( static fn( mixed $item ): string => sanitize_text_field( (string) $item ), $value )
			)
		);
	}

	/**
	 * @param array $row
	 *
	 * @return string
	 */
	private function defaultModelFor( array $row ): string {
		$model = (string) ( $row['default_model'] ?? '' );
		if ( '' !== $model ) {
			return $model;
		}

		$provider = Providers::get( (string) ( $row['provider'] ?? '' ) );

		return (string) ( $provider['models'][0] ?? '' );
	}
}

