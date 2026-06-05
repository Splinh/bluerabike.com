<?php
/**
 * Gateway choice DTO.
 *
 * @package SPLAT\Gateway\DTO
 */

namespace SPLAT\Gateway\DTO;

defined( 'ABSPATH' ) || exit;

final class GatewayChoice {

	public function __construct(
		public readonly int $index,
		public readonly GatewayMessage $message,
		public readonly ?string $finishReason = null,
	) {}

	/**
	 * @return array
	 */
	public function toArray(): array {
		return [
			'index'         => $this->index,
			'message'       => $this->message->toArray(),
			'finish_reason' => $this->finishReason,
		];
	}
}

