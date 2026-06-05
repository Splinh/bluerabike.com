<?php
/**
 * Driver contract for AI API clients.
 *
 * @package SPLAT\Driver
 */

namespace SPLAT\Driver;

defined( 'ABSPATH' ) || exit;

use SPLAT\Gateway\DTO\GatewayRequest;
use SPLAT\Gateway\DTO\GatewayResponse;

interface DriverInterface {

	/**
	 * Send a chat completion request.
	 *
	 * @param GatewayRequest $request    Normalized gateway request.
	 * @param array          $credential Credential metadata used for response normalization.
	 *
	 * @return GatewayResponse|\WP_Error
	 */
	public function chat( GatewayRequest $request, array $credential = [] ): GatewayResponse|\WP_Error;
}

