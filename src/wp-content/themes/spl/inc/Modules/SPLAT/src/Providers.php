<?php
/**
 * Provider registry — curated list of AI providers with metadata.
 *
 * Filterable via 'splat_providers' hook for third-party extensibility.
 * Data sourced from: https://github.com/mnfst/awesome-free-llm-apis
 *
 * @package SPLAT\Config
 */

namespace SPLAT;

defined( 'ABSPATH' ) || exit;

final class Providers {

	/** @var array<string, array>|null Cached provider list. */
	private static ?array $cache = null;

	/**
	 * Get all supported providers.
	 *
	 * @return array<string, array{label: string, api_format: string, base_url: string, models: string[], tier_default: string, category: string, rate_info: string}>
	 */
	public static function all(): array {
		if ( null !== self::$cache ) {
			return self::$cache;
		}

		$providers = [

			// ── Official Provider APIs ───────────────
			'openai'      => [
				'label'        => 'OpenAI',
				'api_format'   => 'openai',
				'base_url'     => 'https://api.openai.com/v1',
				'models'       => [ 'gpt-5.5', 'gpt-5.5-instant', 'gpt-5.5-pro', 'gpt-rosalind' ],
				'tier_default' => 'paid',
				'category'     => 'official',
				'rate_info'    => 'Pay-per-use',
			],
			'gemini'      => [
				'label'        => 'Google Gemini',
				'api_format'   => 'google',
				'base_url'     => 'https://generativelanguage.googleapis.com/v1beta',
				'models'       => [ 'gemini-3.1-pro-preview', 'gemini-3-flash-preview', 'gemini-3.1-flash-lite', 'gemini-2.5-pro', 'gemini-2.5-flash' ],
				'tier_default' => 'free',
				'category'     => 'official',
				'rate_info'    => 'Free tier (not available in EU/UK/CH)',
			],
			'anthropic'   => [
				'label'        => 'Anthropic',
				'api_format'   => 'anthropic',
				'base_url'     => 'https://api.anthropic.com',
				'models'       => [ 'claude-opus-4-7', 'claude-sonnet-4-6', 'claude-haiku-3-5' ],
				'tier_default' => 'paid',
				'category'     => 'official',
				'rate_info'    => 'Pay-per-use',
			],
			'deepseek'    => [
				'label'        => 'DeepSeek',
				'api_format'   => 'openai',
				'base_url'     => 'https://api.deepseek.com/v1',
				'models'       => [ 'deepseek-v4-pro', 'deepseek-v4-flash', 'deepseek-chat' ],
				'tier_default' => 'paid',
				'category'     => 'official',
				'rate_info'    => 'Pay-per-use (very cheap)',
			],
			'mistral'     => [
				'label'        => 'Mistral AI',
				'api_format'   => 'openai',
				'base_url'     => 'https://api.mistral.ai/v1',
				'models'       => [ 'mistral-large-latest', 'mistral-medium-3.5-latest', 'mistral-small-4.0-latest', 'devstral-2-latest', 'codestral-latest' ],
				'tier_default' => 'free',
				'category'     => 'official',
				'rate_info'    => '~1B tokens/month free (Experiment plan)',
			],
			'cohere'      => [
				'label'        => 'Cohere',
				'api_format'   => 'openai',
				'base_url'     => 'https://api.cohere.com/v2',
				'models'       => [ 'command-a-03-2025', 'command-a-reasoning-08-2025', 'command-a-vision-07-2025', 'command-r-plus-08-2024', 'command-r7b-12-2024' ],
				'tier_default' => 'free',
				'category'     => 'official',
				'rate_info'    => '1,000 API calls/month free (non-commercial)',
			],

			// ── Inference Providers ──────────────────
			'groq'        => [
				'label'        => 'Groq',
				'api_format'   => 'openai',
				'base_url'     => 'https://api.groq.com/openai/v1',
				'models'       => [ 'llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'openai/gpt-oss-120b', 'meta-llama/llama-4-scout-17b-16e-instruct', 'qwen/qwen3-32b' ],
				'tier_default' => 'free',
				'category'     => 'inference',
				'rate_info'    => 'Free, 14,400 RPD most models',
			],
			'cerebras'    => [
				'label'        => 'Cerebras',
				'api_format'   => 'openai',
				'base_url'     => 'https://api.cerebras.ai/v1',
				'models'       => [ 'gpt-oss-120b', 'llama-3.3-70b', 'qwen-3-32b' ],
				'tier_default' => 'free',
				'category'     => 'inference',
				'rate_info'    => 'Free, 1M tokens/day, ~2,600 tok/s',
			],
			'github'      => [
				'label'        => 'GitHub Models',
				'api_format'   => 'openai',
				'base_url'     => 'https://models.inference.ai.azure.com',
				'models'       => [ 'gpt-5-mini', 'Phi-4-reasoning', 'Meta-Llama-3.3-70B-Instruct', 'Mistral-Large-2' ],
				'tier_default' => 'free',
				'category'     => 'inference',
				'rate_info'    => 'Free for all GitHub users, 8K in/4K out per request',
			],
			'nvidia'      => [
				'label'        => 'NVIDIA NIM',
				'api_format'   => 'openai',
				'base_url'     => 'https://integrate.api.nvidia.com/v1',
				'models'       => [ 'meta/llama-3.3-70b-instruct', 'google/gemma-4-31b-it', 'deepseek-ai/deepseek-chat-3.2' ],
				'tier_default' => 'free',
				'category'     => 'inference',
				'rate_info'    => 'Free (NVIDIA Developer Program), no daily cap',
			],
			'siliconflow' => [
				'label'        => 'SiliconFlow',
				'api_format'   => 'openai',
				'base_url'     => 'https://api.siliconflow.cn/v1',
				'models'       => [ 'deepseek-ai/DeepSeek-V3', 'Qwen/Qwen3-32B', 'THUDM/glm-4-9b-chat' ],
				'tier_default' => 'free',
				'category'     => 'inference',
				'rate_info'    => 'Free signup credits + permanently free models',
			],
			'huggingface' => [
				'label'        => 'Hugging Face',
				'api_format'   => 'openai',
				'base_url'     => 'https://api-inference.huggingface.co/v1',
				'models'       => [ 'meta-llama/Llama-3.3-70B-Instruct', 'Qwen/Qwen3-32B', 'google/gemma-2-9b-it' ],
				'tier_default' => 'free',
				'category'     => 'inference',
				'rate_info'    => 'Free Serverless Inference, thousands of models',
			],
			'openrouter'  => [
				'label'        => 'OpenRouter',
				'api_format'   => 'openai',
				'base_url'     => 'https://openrouter.ai/api/v1',
				'models'       => [ 'auto', 'openrouter/auto' ],
				'tier_default' => 'free',
				'category'     => 'inference',
				'rate_info'    => '35+ free models (:free suffix), 200 RPD',
			],

			// ── Local AI Routers ─────────────────────
			'9router'     => [
				'label'        => '9Router',
				'api_format'   => 'openai',
				'base_url'     => 'http://localhost:20128/v1',
				'models'       => [ 'auto' ],
				'tier_default' => 'free',
				'category'     => 'router',
				'rate_info'    => 'Aggregates quota from Antigravity, Claude Code, Codex, Cursor',
			],
			'omnirouter'  => [
				'label'        => 'OmniRoute',
				'api_format'   => 'openai',
				'base_url'     => 'http://localhost:20128/v1',
				'models'       => [ 'auto' ],
				'tier_default' => 'free',
				'category'     => 'router',
				'rate_info'    => 'Multi-provider gateway with auto-fallback and load balancing',
			],
		];

		/** @var array<string, array> $providers Filtered provider registry. */
		self::$cache = apply_filters( 'splat_providers', $providers );

		return self::$cache;
	}

	/**
	 * Get a single provider config.
	 */
	public static function get( string $provider ): ?array {
		return self::all()[ $provider ] ?? null;
	}

	/**
	 * Get provider labels for select dropdowns.
	 *
	 * @return array<string, string>
	 */
	public static function labels(): array {
		return array_map( static fn( array $p ) => $p['label'], self::all() );
	}

	/**
	 * Get providers grouped by category.
	 *
	 * @return array<string, array<string, array>>
	 */
	public static function grouped(): array {
		$groups = [
			'official'  => [],
			'inference' => [],
			'router'    => [],
		];

		foreach ( self::all() as $slug => $config ) {
			$category                     = $config['category'] ?? 'inference';
			$groups[ $category ][ $slug ] = $config;
		}

		return $groups;
	}

	/**
	 * Reset static cache (useful after filter changes).
	 */
	public static function resetCache(): void {
		self::$cache = null;
	}
}

