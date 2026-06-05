<?php
/**
 * Gateway response DTO.
 *
 * @package SPLAT\Gateway\DTO
 */

namespace SPLAT\Gateway\DTO;

defined( 'ABSPATH' ) || exit;

final class GatewayResponse {

	/**
	 * @param string          $content
	 * @param string          $provider
	 * @param string          $model
	 * @param GatewayChoice[] $choices
	 * @param GatewayUsage|null $usage
	 * @param int|null        $credentialId
	 * @param string|null     $finishReason
	 * @param string|null     $rawId
	 * @param int|null        $created
	 * @param array           $toolCalls
	 */
	public function __construct(
		public readonly string $content,
		public readonly string $provider,
		public readonly string $model,
		public readonly array $choices = [],
		public readonly ?GatewayUsage $usage = null,
		public readonly ?int $credentialId = null,
		public readonly ?string $finishReason = null,
		public readonly ?string $rawId = null,
		public readonly ?int $created = null,
		public readonly array $toolCalls = [],
	) {}

	/**
	 * @return array
	 */
	public function toOpenAiArray(): array {
		$choices = $this->choices ?: [
			new GatewayChoice(
				index:        0,
				message:      new GatewayMessage( role: 'assistant', content: $this->content, toolCalls: $this->toolCalls ),
				finishReason: $this->finishReason,
			),
		];

		return [
			'id'       => $this->rawId ?? 'chatcmpl-splat',
			'object'   => 'chat.completion',
			'created'  => $this->created ?? time(),
			'model'    => $this->model,
			'choices'  => array_map(
				static fn( GatewayChoice $choice ): array => $choice->toArray(),
				$choices
			),
			'usage'    => ( $this->usage ?? new GatewayUsage() )->toArray(),
			'provider' => $this->provider,
		];
	}
}

