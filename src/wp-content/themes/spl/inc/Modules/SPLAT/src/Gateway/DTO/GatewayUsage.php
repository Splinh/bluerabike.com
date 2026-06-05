<?php
/**
 * Gateway usage DTO.
 *
 * @package SPLAT\Gateway\DTO
 */

namespace SPLAT\Gateway\DTO;

defined( 'ABSPATH' ) || exit;

final class GatewayUsage {

	public function __construct(
		public readonly ?int $promptTokens = null,
		public readonly ?int $completionTokens = null,
		public readonly ?int $totalTokens = null,
	) {}

	/**
	 * @param array $data
	 *
	 * @return self
	 */
	public static function fromArray( array $data ): self {
		$prompt     = isset( $data['prompt_tokens'] ) ? max( 0, (int) $data['prompt_tokens'] ) : null;
		$completion = isset( $data['completion_tokens'] ) ? max( 0, (int) $data['completion_tokens'] ) : null;
		$total      = isset( $data['total_tokens'] ) ? max( 0, (int) $data['total_tokens'] ) : null;

		if ( $total === null && $prompt !== null && $completion !== null ) {
			$total = $prompt + $completion;
		}

		return new self(
			promptTokens:     $prompt,
			completionTokens: $completion,
			totalTokens:      $total,
		);
	}

	/**
	 * @return array
	 */
	public function toArray(): array {
		return [
			'prompt_tokens'     => $this->promptTokens ?? 0,
			'completion_tokens' => $this->completionTokens ?? 0,
			'total_tokens'      => $this->totalTokens ?? ( ( $this->promptTokens ?? 0 ) + ( $this->completionTokens ?? 0 ) ),
		];
	}
}

