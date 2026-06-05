<?php
/**
 * Gateway message DTO.
 *
 * @package SPLAT\Gateway\DTO
 */

namespace SPLAT\Gateway\DTO;

defined( 'ABSPATH' ) || exit;

final class GatewayMessage {

	/**
	 * @param string            $role
	 * @param string|array|null $content
	 * @param string|null       $name
	 * @param string|null       $toolCallId
	 * @param array             $toolCalls
	 */
	public function __construct(
		public readonly string $role,
		public readonly string|array|null $content = null,
		public readonly ?string $name = null,
		public readonly ?string $toolCallId = null,
		public readonly array $toolCalls = [],
	) {}

	/**
	 * @param array $data
	 *
	 * @return self
	 */
	public static function fromArray( array $data ): self {
		return new self(
			role:       sanitize_key( (string) ( $data['role'] ?? 'user' ) ),
			content:    $data['content'] ?? null,
			name:       isset( $data['name'] ) ? sanitize_key( (string) $data['name'] ) : null,
			toolCallId: isset( $data['tool_call_id'] ) ? sanitize_text_field( (string) $data['tool_call_id'] ) : null,
			toolCalls:  is_array( $data['tool_calls'] ?? null ) ? $data['tool_calls'] : [],
		);
	}

	/**
	 * @return array
	 */
	public function toArray(): array {
		$data = [
			'role'    => $this->role,
			'content' => $this->content,
		];

		if ( $this->name !== null ) {
			$data['name'] = $this->name;
		}

		if ( $this->toolCallId !== null ) {
			$data['tool_call_id'] = $this->toolCallId;
		}

		if ( $this->toolCalls ) {
			$data['tool_calls'] = $this->toolCalls;
		}

		return $data;
	}
}

