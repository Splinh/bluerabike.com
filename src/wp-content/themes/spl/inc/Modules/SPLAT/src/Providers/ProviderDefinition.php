<?php
/**
 * Provider capability definition.
 *
 * @package SPLAT\Providers
 */

namespace SPLAT\Providers;

defined( 'ABSPATH' ) || exit;

final class ProviderDefinition {

	/**
	 * @param string $provider
	 * @param string $label
	 * @param string $apiFormat
	 * @param string $authStrategy
	 * @param string $defaultBaseUrl
	 * @param string $defaultModel
	 * @param array  $models
	 * @param array  $capabilities
	 * @param array  $defaultLimits
	 * @param array  $modelAliases
	 * @param bool   $proxyRouter
	 */
	public function __construct(
		public readonly string $provider,
		public readonly string $label,
		public readonly string $apiFormat,
		public readonly string $authStrategy,
		public readonly string $defaultBaseUrl,
		public readonly string $defaultModel,
		public readonly array $models,
		public readonly array $capabilities,
		public readonly array $defaultLimits = [],
		public readonly array $modelAliases = [],
		public readonly bool $proxyRouter = false,
	) {}

	/**
	 * @return array
	 */
	public function toArray(): array {
		return [
			'provider'         => $this->provider,
			'label'            => $this->label,
			'api_format'       => $this->apiFormat,
			'auth_strategy'    => $this->authStrategy,
			'default_base_url' => $this->defaultBaseUrl,
			'default_model'    => $this->defaultModel,
			'models'           => $this->models,
			'capabilities'     => $this->capabilities,
			'default_limits'   => $this->defaultLimits,
			'model_aliases'    => $this->modelAliases,
			'proxy_router'     => $this->proxyRouter,
		];
	}
}

