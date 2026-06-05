<?php
/**
 * Error classification for AI API responses.
 *
 * @package SPLAT\Driver
 */

namespace SPLAT\Driver;

defined( 'ABSPATH' ) || exit;

trait ErrorClassifier {

	/**
	 * Build a WP_Error from API response.
	 */
	protected function buildError( int $httpStatus, string $message, string $provider ): \WP_Error {
		return new \WP_Error(
			'ai_api_error',
			$message,
			[
				'status'   => $httpStatus,
				'provider' => $provider,
				'category' => $this->classifyStatus( $httpStatus ),
			]
		);
	}

	/**
	 * @param int $httpStatus
	 *
	 * @return string
	 */
	private function classifyStatus( int $httpStatus ): string {
		return match ( true ) {
			401 === $httpStatus || 403 === $httpStatus => 'auth',
			400 === $httpStatus || 422 === $httpStatus => 'invalid_request',
			408 === $httpStatus                        => 'timeout',
			429 === $httpStatus                        => 'rate_limit',
			402 === $httpStatus                        => 'quota',
			$httpStatus >= 500                         => 'provider_error',
			default                                    => 'provider_error',
		};
	}
}

