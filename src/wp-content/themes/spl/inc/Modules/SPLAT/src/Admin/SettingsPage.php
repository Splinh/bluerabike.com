<?php
/**
 * Admin Settings Page for HD AI Toolkit.
 *
 * @package SPLAT\Admin
 */

namespace SPLAT\Admin;

defined( 'ABSPATH' ) || exit;

use SPLAT\Asset;
use SPLAT\Providers;

final class SettingsPage {

	private const PAGE_SLUG = 'splat-settings';

	public function init(): void {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
	}

	public function add_menu(): void {
		$hook = add_menu_page(
			__( 'AI Toolkit', 'splat' ),
			__( 'AI Toolkit', 'splat' ),
			'manage_options',
			self::PAGE_SLUG,
			$this->render( ... ),
			'dashicons-admin-generic',
			81
		);

		add_action(
			'admin_enqueue_scripts',
			function ( string $currentHook ) use ( $hook ): void {
				if ( $currentHook !== $hook ) {
					return;
				}

				// Vite-built JS (auto-enqueues associated CSS).
				// 'wp-api' dependency ensures WP REST API client loads before our script.
				Asset::enqueueJS( 'admin.js', [ 'wp-api', 'jquery' ] );

				$handle = Asset::handle( 'admin.js' );
				if ( $handle ) {
					Asset::localize(
						$handle,
						'splatData',
						[
							'restUrl'   => rest_url( 'splat/v1/' ),
							'nonce'     => wp_create_nonce( 'wp_rest' ),
							'providers' => Providers::all(),
						]
					);
				}
			}
		);
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		?>
		<div class="wrap" id="splat-app">
			<div class="splat-header-banner">
				<div class="splat-header-info">
					<div class="splat-logo-wrapper">
						<span class="dashicons dashicons-admin-generic splat-logo-icon"></span>
					</div>
					<div class="splat-header-titles">
						<h1><?php esc_html_e( 'SPLAT AI Key Pool', 'splat' ); ?></h1>
						<p><?php esc_html_e( 'Enterprise-grade smart API gateway, load-balancing, and key rotation.', 'splat' ); ?></p>
					</div>
				</div>
				<div class="splat-header-stats">
					<div class="splat-stat-pill">
						<span class="splat-stat-dot active"></span>
						<span class="splat-stat-label"><strong><?php esc_html_e( 'Gateway Status: Active', 'splat' ); ?></strong></span>
					</div>
				</div>
			</div>

			<nav class="nav-tab-wrapper splat-tabs">
				<a href="#" class="nav-tab nav-tab-active" data-tab="splat-tab-keys"><?php esc_html_e( 'Credentials', 'splat' ); ?></a>
				<a href="#" class="nav-tab" data-tab="splat-tab-consumer-tokens"><?php esc_html_e( 'Consumer Tokens', 'splat' ); ?></a>
				<a href="#" class="nav-tab" data-tab="splat-tab-settings"><?php esc_html_e( 'Routing & Budgets', 'splat' ); ?></a>
				<a href="#" class="nav-tab" data-tab="splat-tab-usage"><?php esc_html_e( 'Usage', 'splat' ); ?></a>
				<a href="#" class="nav-tab" data-tab="splat-tab-test"><?php esc_html_e( 'Test', 'splat' ); ?></a>
			</nav>

			<!-- Keys Tab -->
			<div class="splat-tab-content" id="splat-tab-keys">
				<div class="splat-toolbar">
					<button type="button" class="button button-primary" id="splat-add-key"><?php esc_html_e( 'Add API Key', 'splat' ); ?></button>
					<button type="button" class="button" id="splat-reset-cooldowns"><?php esc_html_e( 'Reset Cooldowns', 'splat' ); ?></button>
				</div>
				<table class="wp-list-table widefat fixed striped" id="splat-keys-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Provider', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Label', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Key', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Model', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Tier', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Priority', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Status', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Fails', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Last Used', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'splat' ); ?></th>
						</tr>
					</thead>
					<tbody id="splat-keys-body">
						<tr><td colspan="10"><?php esc_html_e( 'Loading...', 'splat' ); ?></td></tr>
					</tbody>
				</table>
			</div>

			<div class="splat-tab-content" id="splat-tab-consumer-tokens" style="display:none;">
				<div class="splat-toolbar">
					<button type="button" class="button button-primary" id="splat-open-consumer-modal"><?php esc_html_e( 'Add Consumer Token', 'splat' ); ?></button>
				</div>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Name', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Prefix', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Status', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Daily Tokens', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Monthly Tokens', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Expires', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Last Used', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'splat' ); ?></th>
						</tr>
					</thead>
					<tbody id="splat-consumer-tokens-body">
						<tr><td colspan="8"><?php esc_html_e( 'Loading...', 'splat' ); ?></td></tr>
					</tbody>
				</table>
			</div>

			<!-- Settings Tab -->
			<div class="splat-tab-content" id="splat-tab-settings" style="display:none;">
				<table class="form-table" id="splat-settings-form">
					<tr>
						<th><label for="splat-preferred-provider"><?php esc_html_e( 'Preferred Provider', 'splat' ); ?></label></th>
						<td>
							<select id="splat-preferred-provider">
								<option value=""><?php esc_html_e( '— Auto (by priority) —', 'splat' ); ?></option>
								<?php foreach ( Providers::labels() as $slug => $label ) : ?>
									<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="splat-max-retries"><?php esc_html_e( 'Max Retries', 'splat' ); ?></label></th>
						<td><input type="number" id="splat-max-retries" min="0" max="50" value="0" class="small-text"> <span class="description"><?php esc_html_e( '0 = try all keys in pool (recommended)', 'splat' ); ?></span></td>
					</tr>
					<tr>
						<th><label for="splat-cooldown-429"><?php esc_html_e( 'Cooldown (Rate Limit)', 'splat' ); ?></label></th>
						<td><input type="number" id="splat-cooldown-429" min="10" value="60" class="small-text"> <span class="description">seconds</span></td>
					</tr>
					<tr>
						<th><label for="splat-cooldown-5xx"><?php esc_html_e( 'Cooldown (Server Error)', 'splat' ); ?></label></th>
						<td><input type="number" id="splat-cooldown-5xx" min="30" value="300" class="small-text"> <span class="description">seconds</span></td>
					</tr>
					<tr>
						<th><label for="splat-timeout"><?php esc_html_e( 'Request Timeout', 'splat' ); ?></label></th>
						<td><input type="number" id="splat-timeout" min="5" max="120" value="30" class="small-text"> <span class="description">seconds</span></td>
					</tr>
					<tr>
						<th><label for="splat-cache-ttl"><?php esc_html_e( 'Response Cache TTL', 'splat' ); ?></label></th>
						<td><input type="number" id="splat-cache-ttl" min="0" max="604800" value="86400" class="small-text"> <span class="description"><?php esc_html_e( 'seconds (0 = disabled, 86400 = 24h)', 'splat' ); ?></span></td>
					</tr>
					<tr>
						<th colspan="2"><hr><h3 style="margin:0"><?php esc_html_e( 'Smart Tier Routing', 'splat' ); ?></h3></th>
					</tr>
					<tr>
						<th><label for="splat-prefer-free"><?php esc_html_e( 'Prefer Free Keys', 'splat' ); ?></label></th>
						<td>
							<label><input type="checkbox" id="splat-prefer-free" value="1" checked> <?php esc_html_e( 'Prioritize free-tier keys for low-complexity tasks', 'splat' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><label for="splat-paid-strategy"><?php esc_html_e( 'Paid Key Strategy', 'splat' ); ?></label></th>
						<td>
							<select id="splat-paid-strategy">
								<option value="high_complexity_first"><?php esc_html_e( 'Use paid for high-complexity tasks', 'splat' ); ?></option>
								<option value="fallback_only"><?php esc_html_e( 'Use paid only as fallback', 'splat' ); ?></option>
								<option value="always"><?php esc_html_e( 'No preference (use all equally)', 'splat' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Controls when paid keys (GPT, Claude) are selected over free keys (Gemini).', 'splat' ); ?></p>
						</td>
					</tr>
				</table>

				<table class="form-table">
					<tr>
						<th colspan="2"><hr><h3 style="margin:0"><?php esc_html_e( 'Data Management', 'splat' ); ?></h3></th>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Uninstall Behavior', 'splat' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="splat-clean-uninstall" value="1">
								<?php esc_html_e( 'Delete all plugin data when uninstalling', 'splat' ); ?>
							</label>
							<p class="description"><?php esc_html_e( 'If unchecked, API keys and settings are preserved for future reinstall.', 'splat' ); ?></p>
						</td>
					</tr>
				</table>

				<table class="form-table">
					<tr>
						<th colspan="2"><hr><h3 style="margin:0"><?php esc_html_e( 'Plugin Auto-Update', 'splat' ); ?></h3></th>
					</tr>
					<tr>
						<th><label for="splat-update-token"><?php esc_html_e( 'Access Token', 'splat' ); ?></label></th>
						<td>
							<input
								type="password"
								id="splat-update-token"
								class="regular-text"
								placeholder="<?php esc_attr_e( 'Enter your access token', 'splat' ); ?>"
								autocomplete="new-password"
								spellcheck="false"
							>
							<span id="splat-token-status" style="margin-left:8px;"></span>
							<p class="description"><?php esc_html_e( 'Access token for automatic plugin updates. Leave blank to clear. Token is encrypted before storage.', 'splat' ); ?></p>
						</td>
					</tr>
				</table>

				<p><button type="button" class="button button-primary" id="splat-save-settings"><?php esc_html_e( 'Save Settings', 'splat' ); ?></button> <span id="splat-settings-status"></span></p>
			</div>

			<!-- Usage Tab -->
			<div class="splat-tab-content" id="splat-tab-usage" style="display:none;">
				<div class="splat-toolbar">
					<select id="splat-usage-status">
						<option value=""><?php esc_html_e( 'All statuses', 'splat' ); ?></option>
						<option value="success"><?php esc_html_e( 'Success', 'splat' ); ?></option>
						<option value="error"><?php esc_html_e( 'Error', 'splat' ); ?></option>
					</select>
					<input type="text" id="splat-usage-provider" placeholder="<?php esc_attr_e( 'Provider', 'splat' ); ?>">
					<input type="text" id="splat-usage-model" placeholder="<?php esc_attr_e( 'Model', 'splat' ); ?>">
					<input type="date" id="splat-usage-date-from">
					<input type="date" id="splat-usage-date-to">
					<button type="button" class="button" id="splat-refresh-usage"><?php esc_html_e( 'Refresh', 'splat' ); ?></button>
				</div>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Status', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Provider', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Model', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Requests', 'splat' ); ?></th>
							<th><?php esc_html_e( 'Tokens', 'splat' ); ?></th>
						</tr>
					</thead>
					<tbody id="splat-usage-body">
						<tr><td colspan="5"><?php esc_html_e( 'Loading...', 'splat' ); ?></td></tr>
					</tbody>
				</table>
			</div>

			<!-- Test Tab -->
			<div class="splat-tab-content" id="splat-tab-test" style="display:none;">
				<p><?php esc_html_e( 'Send a test message through the key pool to verify rotation.', 'splat' ); ?></p>
				<textarea id="splat-test-message" rows="3" class="large-text" placeholder="Say hello in 10 words."></textarea>
				<p>
					<button type="button" class="button button-primary" id="splat-send-test"><?php esc_html_e( 'Send Test', 'splat' ); ?></button>
					<span id="splat-test-status"></span>
				</p>
				<div id="splat-test-result" style="display:none;" class="notice notice-info">
					<pre id="splat-test-output"></pre>
				</div>
			</div>

			<!-- Add/Edit API Key Modal -->
			<div id="splat-modal" style="display:none;">
				<div class="splat-modal-overlay"></div>
				<div class="splat-modal-content">
					<div class="splat-modal-header">
						<h2 id="splat-modal-title"><?php esc_html_e( 'Add API Key', 'splat' ); ?></h2>
					</div>
					<div class="splat-modal-body">
						<input type="hidden" id="splat-edit-id" value="">
						<table class="form-table">
							<tr>
								<th><label for="splat-key-provider"><?php esc_html_e( 'Provider', 'splat' ); ?></label></th>
								<td>
									<select id="splat-key-provider">
										<?php foreach ( \SPLAT\Providers\ProviderRegistry::all() as $slug => $def ) : ?>
											<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $def->label ); ?></option>
										<?php endforeach; ?>
									</select>
								</td>
							</tr>
							<tr>
								<th><label for="splat-key-label"><?php esc_html_e( 'Label', 'splat' ); ?></label></th>
								<td>
									<input type="text" id="splat-key-label" class="regular-text" placeholder="e.g. Primary OpenAI Key">
									<p class="description"><?php esc_html_e( 'A friendly name for this key.', 'splat' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="splat-key-apikey"><?php esc_html_e( 'API Key', 'splat' ); ?></label></th>
								<td>
									<input type="password" id="splat-key-apikey" class="regular-text" autocomplete="new-password" spellcheck="false">
									<p class="description"><?php esc_html_e( 'Keys are encrypted in the database. Leave blank when editing to keep current key.', 'splat' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="splat-key-model"><?php esc_html_e( 'Default Model', 'splat' ); ?></label></th>
								<td>
									<select id="splat-key-model">
										<optgroup label="Supported Models" id="splat-key-model-options"></optgroup>
									</select>
								</td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-api-format"><?php esc_html_e( 'API Format', 'splat' ); ?></label></th>
								<td>
									<select id="splat-key-api-format">
										<option value="openai_compatible"><?php esc_html_e( 'OpenAI Compatible', 'splat' ); ?></option>
										<option value="google_gemini"><?php esc_html_e( 'Google Gemini', 'splat' ); ?></option>
										<option value="anthropic_messages"><?php esc_html_e( 'Anthropic Messages', 'splat' ); ?></option>
									</select>
								</td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-auth-strategy"><?php esc_html_e( 'Auth Strategy', 'splat' ); ?></label></th>
								<td>
									<select id="splat-key-auth-strategy">
										<option value="bearer"><?php esc_html_e( 'Bearer', 'splat' ); ?></option>
										<option value="query_api_key"><?php esc_html_e( 'Query API Key', 'splat' ); ?></option>
										<option value="x_api_key"><?php esc_html_e( 'X API Key', 'splat' ); ?></option>
									</select>
								</td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-baseurl"><?php esc_html_e( 'Base URL', 'splat' ); ?></label></th>
								<td><input type="url" id="splat-key-baseurl" class="regular-text" placeholder="Auto from provider"></td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-temperature"><?php esc_html_e( 'Temperature', 'splat' ); ?></label></th>
								<td><input type="number" id="splat-key-temperature" min="0" max="2" step="0.1" class="small-text"></td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-max-tokens"><?php esc_html_e( 'Max Tokens (Output)', 'splat' ); ?></label></th>
								<td><input type="number" id="splat-key-max-tokens" min="0" class="small-text"> <span class="description"><?php esc_html_e( 'Caps AI response length', 'splat' ); ?></span></td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-max-prompt-tokens"><?php esc_html_e( 'Max Prompt Tokens (Input)', 'splat' ); ?></label></th>
								<td><input type="number" id="splat-key-max-prompt-tokens" min="0" class="small-text"> <span class="description"><?php esc_html_e( 'Rejects oversized prompts (0 = unlimited)', 'splat' ); ?></span></td>
							</tr>
							<tr>
								<th><label for="splat-key-priority"><?php esc_html_e( 'Priority', 'splat' ); ?></label></th>
								<td><input type="number" id="splat-key-priority" min="1" max="100" value="10" class="small-text"> <span class="description"><?php esc_html_e( 'Lower = tried first', 'splat' ); ?></span></td>
							</tr>
							<tr>
								<th><label for="splat-key-tier"><?php esc_html_e( 'Tier', 'splat' ); ?></label></th>
								<td>
									<select id="splat-key-tier">
										<option value="free"><?php esc_html_e( 'Free', 'splat' ); ?></option>
										<option value="paid"><?php esc_html_e( 'Paid', 'splat' ); ?></option>
									</select>
									<span class="description"><?php esc_html_e( 'Free keys are preferred for simple tasks', 'splat' ); ?></span>
								</td>
							</tr>
							<tr>
								<th></th>
								<td>
									<label>
										<input type="checkbox" id="splat-toggle-advanced-fields">
										<strong><?php esc_html_e( 'Show Advanced Settings', 'splat' ); ?></strong>
									</label>
								</td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-expires-at"><?php esc_html_e( 'Expires At', 'splat' ); ?></label></th>
								<td><input type="datetime-local" id="splat-key-expires-at"></td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-rotate-after-days"><?php esc_html_e( 'Rotate Warning', 'splat' ); ?></label></th>
								<td><input type="number" id="splat-key-rotate-after-days" min="0" class="small-text"> <span class="description"><?php esc_html_e( 'Days after creation. Warning only.', 'splat' ); ?></span></td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-daily-token-limit"><?php esc_html_e( 'Daily Token Limit', 'splat' ); ?></label></th>
								<td><input type="number" id="splat-key-daily-token-limit" min="0" class="regular-text"></td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-monthly-token-limit"><?php esc_html_e( 'Monthly Token Limit', 'splat' ); ?></label></th>
								<td><input type="number" id="splat-key-monthly-token-limit" min="0" class="regular-text"></td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-capabilities"><?php esc_html_e( 'Capability Overrides', 'splat' ); ?></label></th>
								<td><textarea id="splat-key-capabilities" class="large-text code" rows="3" placeholder='{"json_schema":true,"tools":false}'></textarea></td>
							</tr>
							<tr class="splat-advanced-field">
								<th><label for="splat-key-custom-headers"><?php esc_html_e( 'Custom Headers', 'splat' ); ?></label></th>
								<td>
									<textarea id="splat-key-custom-headers" class="large-text code" rows="3" placeholder='{"OpenAI-Organization":"org_xxx"}'></textarea>
									<p class="description"><?php esc_html_e( 'Optional JSON object of extra HTTP headers. Auth headers cannot be overridden.', 'splat' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
					<div class="splat-modal-footer">
						<button type="button" class="button button-primary" id="splat-modal-save"><?php esc_html_e( 'Save Key', 'splat' ); ?></button>
						<button type="button" class="button" id="splat-modal-cancel"><?php esc_html_e( 'Cancel', 'splat' ); ?></button>
						<span id="splat-modal-status"></span>
					</div>
				</div>
			</div>

			<!-- Add Consumer Token Modal -->
			<div id="splat-consumer-form-modal" style="display:none;">
				<div class="splat-modal-overlay"></div>
				<div class="splat-modal-content">
					<div class="splat-modal-header">
						<h2 id="splat-consumer-modal-title"><?php esc_html_e( 'Add Consumer Token', 'splat' ); ?></h2>
					</div>
					<div class="splat-modal-body">
						<table class="form-table">
							<tr>
								<th><label for="splat-consumer-token-name"><?php esc_html_e( 'Name', 'splat' ); ?></label></th>
								<td><input type="text" id="splat-consumer-token-name" class="regular-text" placeholder="PLL AutoTranslate"></td>
							</tr>
							<tr>
								<th><label for="splat-consumer-token-routes"><?php esc_html_e( 'Allowed Routes', 'splat' ); ?></label></th>
								<td>
									<select id="splat-consumer-token-routes" class="splat-multi-select" multiple>
										<option value="/splat/v1/chat/completions"><?php esc_html_e( 'Chat Completions', 'splat' ); ?></option>
										<option value="/splat/v1/models"><?php esc_html_e( 'Models', 'splat' ); ?></option>
										<option value="/splat/v1/openapi.json"><?php esc_html_e( 'OpenAPI Spec', 'splat' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Leave blank to allow all routes.', 'splat' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="splat-consumer-token-capabilities"><?php esc_html_e( 'Allowed Capabilities', 'splat' ); ?></label></th>
								<td>
									<select id="splat-consumer-token-capabilities" class="splat-multi-select" multiple>
										<option value="chat_completions"><?php esc_html_e( 'Chat Completions', 'splat' ); ?></option>
										<option value="json_object"><?php esc_html_e( 'JSON Object', 'splat' ); ?></option>
										<option value="json_schema"><?php esc_html_e( 'JSON Schema', 'splat' ); ?></option>
										<option value="tools"><?php esc_html_e( 'Tools', 'splat' ); ?></option>
										<option value="model_list"><?php esc_html_e( 'Model List', 'splat' ); ?></option>
									</select>
									<p class="description"><?php esc_html_e( 'Leave blank to allow all capabilities.', 'splat' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="splat-consumer-token-providers"><?php esc_html_e( 'Allowed Providers', 'splat' ); ?></label></th>
								<td>
									<select id="splat-consumer-token-providers" class="splat-multi-select" multiple>
										<?php foreach ( \SPLAT\Providers\ProviderRegistry::all() as $slug => $def ) : ?>
											<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $def->label ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Leave blank to allow all providers (Smart Routing).', 'splat' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="splat-consumer-token-models"><?php esc_html_e( 'Allowed Models', 'splat' ); ?></label></th>
								<td>
									<select id="splat-consumer-token-models" class="splat-multi-select" multiple data-taggable="true"></select>
									<p class="description"><?php esc_html_e( 'Leave blank to allow all models. Options follow Allowed Providers. Type a model ID to add a router/custom model.', 'splat' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><label for="splat-consumer-token-expires-at"><?php esc_html_e( 'Expires At', 'splat' ); ?></label></th>
								<td><input type="datetime-local" id="splat-consumer-token-expires-at"></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Internal Only', 'splat' ); ?></th>
								<td>
									<label>
										<input type="checkbox" id="splat-consumer-token-internal-only" value="1">
										<?php esc_html_e( 'Restrict to same-site internal calls only', 'splat' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'When enabled, external HTTP requests using this token will be rejected.', 'splat' ); ?></p>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Budgets', 'splat' ); ?></th>
								<td>
									<div class="splat-budget-grid">
										<label for="splat-consumer-token-daily-token-limit">
											<span><?php esc_html_e( 'Daily Tokens', 'splat' ); ?></span>
											<input type="number" id="splat-consumer-token-daily-token-limit" min="0" class="regular-text" placeholder="0 = unlimited">
										</label>
										<label for="splat-consumer-token-monthly-token-limit">
											<span><?php esc_html_e( 'Monthly Tokens', 'splat' ); ?></span>
											<input type="number" id="splat-consumer-token-monthly-token-limit" min="0" class="regular-text" placeholder="0 = unlimited">
										</label>
									</div>
									<p class="description"><?php esc_html_e( 'Token budgets are enforced from recorded usage. Empty or 0 means unlimited.', 'splat' ); ?></p>
								</td>
							</tr>
						</table>
					</div>
					<div class="splat-modal-footer">
						<button type="button" class="button button-primary" id="splat-create-consumer-token"><?php esc_html_e( 'Create Consumer Token', 'splat' ); ?></button>
						<button type="button" class="button" id="splat-consumer-modal-cancel"><?php esc_html_e( 'Cancel', 'splat' ); ?></button>
						<span id="splat-consumer-status"></span>
					</div>
				</div>
			</div>

			<!-- Show New Consumer Token Modal -->
			<div id="splat-token-modal" style="display:none;">
				<div class="splat-modal-overlay"></div>
				<div class="splat-modal-content" style="max-width: 600px; text-align: center;">
					<div class="splat-modal-body" style="padding: 30px;">
						<h2 style="color:#46b450; margin-top:0; font-size: 1.5em; display:flex; align-items:center; justify-content:center; gap:8px;">
							<span class="dashicons dashicons-yes-alt" style="font-size: 32px; width: 32px; height: 32px;"></span> <?php esc_html_e( 'Consumer Token Created!', 'splat' ); ?>
						</h2>
						<p style="font-size: 14px; margin: 16px 0; color: #50575e;"><?php esc_html_e( 'Please copy your API key immediately. For security reasons, it will not be shown again. Only the prefix is stored in the database.', 'splat' ); ?></p>
						<div style="margin: 20px 0;">
							<input type="text" id="splat-new-full-token" readonly style="width: 100%; font-family: monospace; font-size: 18px; padding: 12px; text-align: center; background: #f0f0f1; border: 1px solid #8c8f94; border-radius: 4px;">
						</div>
						<div style="margin-top: 24px;">
							<button type="button" class="button button-primary button-hero" id="splat-token-modal-copy"><?php esc_html_e( 'Copy Token & Close', 'splat' ); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

