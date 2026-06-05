<?php
/**
 * Gateway request DTO.
 *
 * @package SPLAT\Gateway\DTO
 */

namespace SPLAT\Gateway\DTO;

defined( 'ABSPATH' ) || exit;

final class GatewayRequest {

	/**
	 * @param GatewayMessage[] $messages
	 * @param string|null      $model
	 * @param float|null       $temperature
	 * @param int|null         $maxTokens
	 * @param array|null       $responseFormat
	 * @param array            $tools
	 * @param string|array|null $toolChoice
	 * @param array            $metadata
	 * @param int|null         $consumerId
	 * @param array            $budgetPolicy
	 * @param array            $routingPolicy
	 */
	public function __construct(
		public readonly array $messages,
		public readonly ?string $model = null,
		public readonly ?float $temperature = null,
		public readonly ?int $maxTokens = null,
		public readonly ?array $responseFormat = null,
		public readonly array $tools = [],
		public readonly string|array|null $toolChoice = null,
		public readonly array $metadata = [],
		public readonly ?int $consumerId = null,
		public readonly array $budgetPolicy = [],
		public readonly array $routingPolicy = [],
	) {}

	/**
	 * @param array $data
	 *
	 * @return self
	 */
	public static function fromArray( array $data ): self {
		$messages = array_map(
			static fn( array $message ): GatewayMessage => GatewayMessage::fromArray( $message ),
			array_values( array_filter( $data['messages'] ?? [], 'is_array' ) )
		);

		return new self(
			messages:       $messages,
			model:          isset( $data['model'] ) ? sanitize_text_field( (string) $data['model'] ) : null,
			temperature:    isset( $data['temperature'] ) ? (float) $data['temperature'] : null,
			maxTokens:      isset( $data['max_tokens'] ) ? max( 0, (int) $data['max_tokens'] ) : null,
			responseFormat: is_array( $data['response_format'] ?? null ) ? $data['response_format'] : null,
			tools:          is_array( $data['tools'] ?? null ) ? $data['tools'] : [],
			toolChoice:     $data['tool_choice'] ?? null,
			metadata:       is_array( $data['metadata'] ?? null ) ? $data['metadata'] : [],
			consumerId:     isset( $data['consumer_id'] ) ? max( 0, (int) $data['consumer_id'] ) : null,
			budgetPolicy:   is_array( $data['budget_policy'] ?? null ) ? $data['budget_policy'] : [],
			routingPolicy:  is_array( $data['routing_policy'] ?? null ) ? $data['routing_policy'] : [],
		);
	}

	/**
	 * @return array
	 */
	public function messagesToArray(): array {
		return array_map(
			static fn( GatewayMessage $message ): array => $message->toArray(),
			$this->messages
		);
	}
}

