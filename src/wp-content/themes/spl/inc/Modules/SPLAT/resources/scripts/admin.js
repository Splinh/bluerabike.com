/**
 * HD AI Toolkit — Admin JS
 * Vite entry point. SCSS imported here → Vite auto-extracts CSS.
 */
import select2 from 'select2';
import 'select2/dist/css/select2.min.css';
import '../styles/admin.scss';
import { $, $$, on } from './utils/dom.js';

(function () {
	'use strict';

	const { restUrl, nonce, providers } = window.splatData || {};
	if (!restUrl) return;
	const jq = window.jQuery;
	if (jq) select2(jq);

	// ── DOM Helpers ─────────────────────────────────
	const api = (endpoint, method = 'GET', body = null) =>
		fetch(restUrl + endpoint, {
			method,
			headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce },
			body: body ? JSON.stringify(body) : undefined,
		}).then((r) => r.json());

	// ── Tabs ────────────────────────────────────────
	on('.splat-tabs .nav-tab', 'click', (e) => {
		e.preventDefault();
		const tab = e.currentTarget;
		$$('.splat-tabs .nav-tab').forEach((t) => t.classList.remove('nav-tab-active'));
		$$('.splat-tab-content').forEach((c) => (c.style.display = 'none'));
		tab.classList.add('nav-tab-active');
		$('#' + tab.dataset.tab).style.display = '';
	});

	// ── Keys List ───────────────────────────────────
	const tbody = $('#splat-keys-body');

	function loadKeys() {
		api('ai-keys').then((keys) => {
			tbody.innerHTML = '';
			if (!keys.length) {
				tbody.innerHTML = '<tr><td colspan="10">No API keys configured. Click "Add API Key" to start.</td></tr>';
				return;
			}
			keys.forEach((k) => {
				const tr = document.createElement('tr');
				const providerLabel = providers[k.provider]?.label || k.provider;

				let statusHtml;
				if (!Number(k.is_active)) {
					statusHtml = '<span class="splat-badge splat-badge--disabled">Disabled</span>';
				} else if (k.expires_at && new Date(k.expires_at.replace(' ', 'T')) <= new Date()) {
					statusHtml = '<span class="splat-badge splat-badge--disabled">Expired</span>';
				} else if (k.cooldown_until) {
					statusHtml = '<span class="splat-badge splat-badge--cooldown">Cooldown</span>';
				} else if (k.expires_at) {
					statusHtml = '<span class="splat-badge splat-badge--cooldown">Expiring</span>';
				} else {
					statusHtml = '<span class="splat-badge splat-badge--active">Active</span>';
				}

				const tierBadge = k.tier === 'paid' ? '<span class="splat-badge splat-badge--paid">Paid</span>' : '<span class="splat-badge splat-badge--free">Free</span>';

				tr.innerHTML = `
					<td>${esc(providerLabel)}</td>
					<td>${esc(k.label)}</td>
					<td><code>${esc(k.api_key_hash)}</code></td>
					<td>${esc(k.default_model)}</td>
					<td>${tierBadge}</td>
					<td>${esc(k.priority)}</td>
					<td>${statusHtml}</td>
					<td>${esc(k.fail_count)}${k.last_error ? ` <small title="${esc(k.last_error)}">Error</small>` : ''}</td>
					<td>${k.last_used_at || '—'}</td>
					<td>
						<button class="button button-small splat-test-btn" data-id="${k.id}">Test</button>
						<button class="button button-small splat-edit-btn" data-id="${k.id}">Edit</button>
						<button class="button button-small splat-del-btn" data-id="${k.id}">Delete</button>
					</td>
				`;
				tbody.appendChild(tr);
			});
		});
	}

	function esc(s) {
		const d = document.createElement('div');
		d.textContent = String(s || '');
		return d.innerHTML;
	}

	function showSplatPopup(title, contentHtml, type = 'info') {
		const existing = document.getElementById('splat-info-popup');
		if (existing) existing.remove();

		let color = '#1d2327';
		let icon = '<span class="dashicons dashicons-info" style="font-size:24px; width:24px; height:24px; vertical-align:middle; margin-right:8px; color:#2271b1;"></span>';

		if (type === 'error') {
			color = '#d63638';
			icon = '<span class="dashicons dashicons-warning" style="font-size:24px; width:24px; height:24px; vertical-align:middle; margin-right:8px; color:#d63638;"></span>';
		} else if (type === 'success') {
			color = '#46b450';
			icon = '<span class="dashicons dashicons-yes-alt" style="font-size:24px; width:24px; height:24px; vertical-align:middle; margin-right:8px; color:#46b450;"></span>';
		}

		const html = `
			<div id="splat-info-popup" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:99999; display:flex; align-items:center; justify-content:center;">
				<div style="background:#fff; padding:24px; border-radius:4px; max-width:500px; width:100%; box-shadow:0 3px 15px rgba(0,0,0,0.2);">
					<h3 style="margin-top:0; border-bottom:1px solid #f0f0f1; padding-bottom:12px; margin-bottom:16px; display:flex; align-items:center; color:${color}; font-size:18px;">
						${icon}${title}
					</h3>
					<div style="font-size:14px; line-height:1.5; text-align:left;">${contentHtml}</div>
					<div style="text-align:right; margin-top:24px;">
						<button type="button" class="button button-primary" onclick="document.getElementById('splat-info-popup').remove()">Close</button>
					</div>
				</div>
			</div>
		`;
		document.body.insertAdjacentHTML('beforeend', html);
	}

	// Event Delegation for table buttons
	on(tbody, 'click', (e) => {
		const btn = e.target.closest('button');
		if (!btn) return;

		const id = btn.dataset.id;
		if (btn.classList.contains('splat-test-btn')) {
			const originalHtml = btn.innerHTML;
			btn.textContent = '...';
			api('ai-keys/' + id + '/test', 'POST')
				.then((r) => {
					if (!r.success) {
						btn.textContent = 'Failed';
						showSplatPopup(
							'Test Failed',
							`<p style="color:#d63638; font-family:monospace; background:#fbf0f0; border:1px solid #f5c6cb; padding:10px; border-radius:4px; word-break:break-all;">${esc(r.message || r.error || r.code || 'Unknown error')}</p>`,
							'error',
						);
					} else {
						btn.textContent = 'OK';
						btn.style.backgroundColor = '#46b450';
						btn.style.color = '#fff';
						btn.style.borderColor = '#46b450';
						showSplatPopup(
							'Test Successful',
							`
							<table style="width:100%; text-align:left; border-collapse:collapse;">
								<tr><th style="padding:8px 0; border-bottom:1px solid #f0f0f1; width:100px;">Latency:</th><td style="padding:8px 0; border-bottom:1px solid #f0f0f1;"><strong>${r.latency_ms} ms</strong></td></tr>
								<tr><th style="padding:8px 0;">Model:</th><td style="padding:8px 0;"><code>${esc(r.model || 'auto')}</code></td></tr>
							</table>
						`,
							'success',
						);
					}
					setTimeout(() => {
						// Reload keys to update the Fails/Status column
						loadKeys();
					}, 2000);
				})
				.catch((err) => {
					btn.textContent = 'Error';
					showSplatPopup(
						'Network Error',
						`<p style="color:#d63638; font-family:monospace; background:#fbf0f0; border:1px solid #f5c6cb; padding:10px; border-radius:4px;">${esc(err.message)}</p>`,
						'error',
					);
					setTimeout(() => loadKeys(), 2000);
				});
		} else if (btn.classList.contains('splat-edit-btn')) {
			openEditModal(Number(id));
		} else if (btn.classList.contains('splat-del-btn')) {
			if (confirm('Delete this key?')) {
				api('ai-keys/' + id, 'DELETE').then(() => loadKeys());
			}
		}
	});

	loadKeys();

	const consumerTokensBody = $('#splat-consumer-tokens-body');
	const usageBody = $('#splat-usage-body');
	const consumerModelSelect = $('#splat-consumer-token-models');
	const consumerFormModal = $('#splat-consumer-form-modal');
	const consumerStatus = $('#splat-consumer-status');

	initEnhancedSelects();
	updateConsumerModelOptions();

	function loadConsumerTokens() {
		if (!consumerTokensBody) return;
		api('consumer-tokens').then((tokens) => {
			consumerTokensBody.innerHTML = '';
			if (!tokens.length) {
				consumerTokensBody.innerHTML = '<tr><td colspan="8">No consumer tokens configured.</td></tr>';
				return;
			}
			tokens.forEach((token) => {
				const tr = document.createElement('tr');
				const isRevoked = !!token.revoked_at;
				const isExpired = !isRevoked && token.expires_at && new Date(token.expires_at.replace(' ', 'T')) <= new Date();
				const status = isRevoked ? 'Revoked' : isExpired ? 'Expired' : 'Active';
				const revokeBtn = isRevoked
					? '<span class="splat-badge splat-badge--disabled">Revoked</span>'
					: `<button class="button button-small splat-revoke-consumer-token" data-id="${token.id}">Revoke</button>`;

				const infoData = encodeURIComponent(
					JSON.stringify({
						routes: JSON.parse(token.allowed_routes_json || '[]'),
						capabilities: JSON.parse(token.allowed_capabilities_json || '[]'),
						providers: JSON.parse(token.allowed_providers_json || '[]'),
						models: JSON.parse(token.allowed_models_json || '[]'),
						internal_only: Number(token.internal_only) === 1,
					}),
				);

				const infoBtn = `<button class="button button-small splat-info-consumer-token" data-info="${infoData}" title="View token permissions" style="margin-left: 4px;">Info</button>`;
				const deleteBtn = `<button class="button button-small splat-delete-consumer-token" data-id="${token.id}" title="Permanently delete this token" style="margin-left: 4px; color: #d63638; border-color: #d63638;">Delete</button>`;

				const prefixHtml = `
					<div style="display: flex; align-items: center; gap: 4px;">
						<code style="text-overflow: ellipsis; white-space: nowrap; overflow: hidden; max-width: 150px; display: inline-block; vertical-align: middle;" title="${esc(token.token_prefix)}">${esc(token.token_prefix)}</code>
						<span class="dashicons dashicons-admin-page splat-copy-prefix" style="cursor: pointer; font-size: 16px; width: 16px; height: 16px; color: #2271b1;" data-prefix="${esc(token.token_prefix)}" title="Copy Prefix"></span>
					</div>
				`;

				const internalBadge = Number(token.internal_only) === 1 ? ' <span class="splat-badge splat-badge--cooldown" title="Internal only">Internal</span>' : '';

				tr.innerHTML = `
					<td>${esc(token.name)}${internalBadge}</td>
					<td>${prefixHtml}</td>
					<td>${esc(status)}</td>
					<td>${formatTokenUsage(token.usage?.daily_tokens, token.daily_token_limit)}</td>
					<td>${formatTokenUsage(token.usage?.monthly_tokens, token.monthly_token_limit)}</td>
					<td>${esc(token.expires_at || '-')}</td>
					<td>${esc(token.last_used_at || '-')}</td>
					<td style="white-space: nowrap;">${revokeBtn}${infoBtn}${deleteBtn}</td>
				`;
				consumerTokensBody.appendChild(tr);
			});
		});
	}

	function loadUsage() {
		if (!usageBody) return;
		const params = new URLSearchParams();
		[
			['status', '#splat-usage-status'],
			['provider', '#splat-usage-provider'],
			['model', '#splat-usage-model'],
			['date_from', '#splat-usage-date-from'],
			['date_to', '#splat-usage-date-to'],
		].forEach(([key, selector]) => {
			const value = $(selector)?.value?.trim();
			if (value) params.set(key, value);
		});
		api('usage' + (params.toString() ? '?' + params.toString() : '')).then((payload) => {
			const rows = payload.summary || [];
			usageBody.innerHTML = '';
			if (!rows.length) {
				usageBody.innerHTML = '<tr><td colspan="5">No usage recorded.</td></tr>';
				return;
			}
			rows.forEach((row) => {
				const tr = document.createElement('tr');
				tr.innerHTML = `
					<td>${esc(row.status)}</td>
					<td>${esc(row.provider)}</td>
					<td>${esc(row.model)}</td>
					<td>${Number(row.requests || 0).toLocaleString()}</td>
					<td>${formatTokens(row.total_tokens)}</td>
				`;
				usageBody.appendChild(tr);
			});
		});
	}

	loadConsumerTokens();
	loadUsage();

	on('#splat-open-consumer-modal', 'click', () => {
		// Reset form
		$('#splat-consumer-token-name').value = '';
		if (jq?.fn?.select2) {
			jq('#splat-consumer-token-routes').val(null).trigger('change');
			jq('#splat-consumer-token-capabilities').val(null).trigger('change');
			jq('#splat-consumer-token-providers').val(null).trigger('change');
			jq('#splat-consumer-token-models').val(null).trigger('change');
		} else {
			$('#splat-consumer-token-routes').value = '';
			$('#splat-consumer-token-capabilities').value = '';
			$('#splat-consumer-token-providers').value = '';
			$('#splat-consumer-token-models').value = '';
		}
		$('#splat-consumer-token-expires-at').value = '';
		$('#splat-consumer-token-internal-only').checked = false;
		$('#splat-consumer-token-daily-token-limit').value = '';
		$('#splat-consumer-token-monthly-token-limit').value = '';
		consumerStatus.innerHTML = '';
		consumerFormModal.style.display = '';
	});

	on('#splat-consumer-modal-cancel', 'click', () => {
		consumerFormModal.style.display = 'none';
	});

	on('#splat-create-consumer-token', 'click', () => {
		const name = $('#splat-consumer-token-name').value.trim();
		if (!name) {
			consumerStatus.innerHTML = '<span style="color:#d63638;">Name is required.</span>';
			return;
		}
		const payload = {
			name,
			allowed_routes: selectedValues($('#splat-consumer-token-routes')),
			allowed_capabilities: selectedValues($('#splat-consumer-token-capabilities')),
			allowed_providers: selectedValues($('#splat-consumer-token-providers')),
			allowed_models: selectedValues(consumerModelSelect),
			expires_at: $('#splat-consumer-token-expires-at').value || null,
			internal_only: $('#splat-consumer-token-internal-only').checked ? 1 : 0,
			daily_token_limit: $('#splat-consumer-token-daily-token-limit').value || null,
			monthly_token_limit: $('#splat-consumer-token-monthly-token-limit').value || null,
		};
		const btn = $('#splat-create-consumer-token');
		btn.disabled = true;
		consumerStatus.textContent = 'Creating...';

		api('consumer-tokens', 'POST', payload).then((result) => {
			btn.disabled = false;
			if (result.error) {
				consumerStatus.innerHTML = `<span style="color:#d63638;">${esc(result.error)}</span>`;
				return;
			}
			consumerFormModal.style.display = 'none';
			$('#splat-new-full-token').value = result.token;
			$('#splat-token-modal').style.display = '';
			loadConsumerTokens();
		});
	});

	on('#splat-consumer-token-providers', 'change', updateConsumerModelOptions);

	on(consumerTokensBody, 'click', (e) => {
		const copyBtn = e.target.closest('.splat-copy-prefix');
		if (copyBtn) {
			navigator.clipboard.writeText(copyBtn.dataset.prefix).then(() => {
				const originalColor = copyBtn.style.color;
				copyBtn.style.color = '#46b450';
				setTimeout(() => (copyBtn.style.color = originalColor), 1000);
				showSplatPopup(
					'Prefix Copied / Đã Copy Prefix',
					`<p><strong>Copied prefix / Đã copy:</strong> <code>${esc(copyBtn.dataset.prefix)}</code></p>
					<p style="color: #b58105; background: #fff8e5; border-left: 4px solid #ffb900; padding: 12px; border-radius: 4px; line-height: 1.5; margin-top: 12px;">
						<strong>[VIỆT NAM] CẢNH BÁO:</strong> Đây là <strong>Prefix (40 ký tự)</strong> dùng để đối chiếu, không phải là Token đầy đủ. 
						Vì lý do bảo mật, Full Token chỉ hiển thị 1 lần duy nhất lúc tạo. Copy Prefix này <strong>sẽ KHÔNG hoạt động</strong> khi dán vào cài đặt Polylang. 
						Nếu đã làm mất Full Token, vui lòng xóa dòng này và tạo một Consumer Token mới.<br><br>
						<strong>[ENGLISH] WARNING:</strong> This is a 40-character <strong>Prefix</strong> for reference, not the full token. 
						Full tokens are only shown once during creation. Copying this prefix <strong>will NOT work</strong> for authentication. 
						If you lost the full token, please delete this one and create a new Consumer Token.
					</p>`,
					'info'
				);
			});
			return;
		}

		const infoBtn = e.target.closest('.splat-info-consumer-token');
		if (infoBtn) {
			try {
				const info = JSON.parse(decodeURIComponent(infoBtn.dataset.info || '%7B%7D'));
				const renderList = (title, items) => {
					const vals = items?.length
						? items
								.map(
									(i) =>
										`<span style="background:#f0f0f1; border:1px solid #dcdcde; padding:2px 6px; border-radius:3px; font-family:monospace; font-size:12px; margin:2px 4px 2px 0; display:inline-block;">${esc(i)}</span>`,
								)
								.join('')
						: '<em style="color:#646970;">All (Unrestricted)</em>';
					return `<div style="margin-bottom: 15px; text-align:left;">
						<strong style="display:block; margin-bottom:5px;">${title}:</strong>
						<div>${vals}</div>
					</div>`;
				};

				const internalFlag = info.internal_only
					? '<div style="margin-bottom: 15px; text-align:left;"><strong style="display:block; margin-bottom:5px;">Internal Only:</strong><span class="splat-badge splat-badge--cooldown">Yes — external HTTP rejected</span></div>'
					: '';

				const contentHtml = `
					${internalFlag}
					${renderList('Allowed Routes', info.routes)}
					${renderList('Allowed Capabilities', info.capabilities)}
					${renderList('Allowed Providers', info.providers)}
					${renderList('Allowed Models', info.models)}
				`;

				showSplatPopup('Token Permissions', contentHtml, 'info');
			} catch (err) {
				showSplatPopup('Error', 'Error reading token info.', 'error');
			}
			return;
		}

		const delBtn = e.target.closest('.splat-delete-consumer-token');
		if (delBtn) {
			if (!confirm('Permanently delete this consumer token? This cannot be undone.')) return;
			delBtn.disabled = true;
			delBtn.textContent = 'Deleting...';
			api('consumer-tokens/' + delBtn.dataset.id + '?force=1', 'DELETE').then((r) => {
				if (r && r.error) {
					alert('Delete failed: ' + r.error);
					delBtn.disabled = false;
					delBtn.textContent = 'Delete';
				} else {
					loadConsumerTokens();
				}
			});
			return;
		}

		const btn = e.target.closest('.splat-revoke-consumer-token');
		if (!btn || !confirm('Revoke this consumer token?')) return;
		btn.disabled = true;
		btn.textContent = 'Revoking...';
		api('consumer-tokens/' + btn.dataset.id, 'DELETE').then((r) => {
			if (r && r.error) {
				alert('Revoke failed: ' + r.error);
				btn.disabled = false;
				btn.textContent = 'Revoke';
			} else {
				loadConsumerTokens();
			}
		});
	});

	on('#splat-refresh-usage', 'click', loadUsage);

	function selectedValues(select) {
		if (jq?.fn?.select2 && select && jq(select).data('select2')) {
			const value = jq(select).val();
			return (Array.isArray(value) ? value : value ? [value] : []).map((item) => String(item).trim()).filter(Boolean);
		}

		return Array.from(select?.selectedOptions || [])
			.map((option) => option.value.trim())
			.filter(Boolean);
	}

	function formatTokens(num) {
		num = Number(num || 0);
		if (num >= 1000000) {
			return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
		}
		if (num >= 1000) {
			return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
		}
		return num.toLocaleString();
	}

	function formatTokenUsage(used, limit) {
		const usedValue = formatTokens(used);
		const limitValue = Number(limit || 0);

		return limitValue > 0 ? `${usedValue} / ${formatTokens(limitValue)}` : usedValue;
	}

	function updateConsumerModelOptions() {
		if (!consumerModelSelect) return;
		const selectedProviders = selectedValues($('#splat-consumer-token-providers'));
		const currentValues = selectedValues(consumerModelSelect);
		const values = new Set();
		selectedProviders.forEach((providerSlug) => {
			(providers?.[providerSlug]?.models || []).forEach((model) => values.add(model));
		});
		currentValues.forEach((model) => values.add(model));

		const nextSelected = currentValues.filter((model) => values.has(model));
		consumerModelSelect.innerHTML = '';
		Array.from(values)
			.sort()
			.forEach((model) => addOption(consumerModelSelect, model, nextSelected.includes(model)));

		syncSelect2State(consumerModelSelect, selectedProviders.length === 0, nextSelected);
	}

	function addOption(select, value, selected = false) {
		const existing = Array.from(select.options).find((option) => option.value === value);
		if (existing) {
			existing.selected = existing.selected || selected;
			return existing;
		}

		const option = document.createElement('option');
		option.value = value;
		option.textContent = value;
		option.selected = selected;
		select.appendChild(option);

		return option;
	}

	function initEnhancedSelects() {
		if (!jq?.fn?.select2) return;

		$$('.splat-multi-select').forEach((select) => {
			const $select = jq(select);
			$select.select2({
				width: '100%',
				tags: select.dataset.taggable === 'true',
				tokenSeparators: select.dataset.taggable === 'true' ? [','] : [],
				placeholder: select.id === 'splat-consumer-token-models' ? 'Select providers first' : 'Select one or more',
			});

			if (select.id === 'splat-consumer-token-providers') {
				$select.on('change', updateConsumerModelOptions);
			}
		});
	}

	function syncSelect2State(select, disabled, value = null) {
		select.disabled = disabled;
		if (!jq?.fn?.select2 || !select) return;

		const $select = jq(select);
		if (value !== null) {
			$select.val(value);
		}
		$select.prop('disabled', disabled).trigger('change');
	}

	// ── Modal ───────────────────────────────────────
	const modal = $('#splat-modal');
	const providerSelect = $('#splat-key-provider');
	const modelSelect = $('#splat-key-model');
	const modelOptions = $('#splat-key-model-options');
	const baseurlInput = $('#splat-key-baseurl');

	function openAddModal() {
		$('#splat-edit-id').value = '';
		$('#splat-modal-title').textContent = 'Add API Key';
		$('#splat-key-label').value = '';
		$('#splat-key-apikey').value = '';
		$('#splat-key-apikey').placeholder = '';
		$('#splat-key-priority').value = '10';
		$('#splat-key-tier').value = 'free';
		$('#splat-key-custom-headers').value = '';
		$('#splat-key-capabilities').value = '';
		$('#splat-key-temperature').value = '';
		$('#splat-key-max-tokens').value = '';
		$('#splat-key-max-prompt-tokens').value = '';
		$('#splat-key-expires-at').value = '';
		$('#splat-key-rotate-after-days').value = '';
		$('#splat-key-daily-token-limit').value = '';
		$('#splat-key-monthly-token-limit').value = '';
		providerSelect.value = 'openai';
		updateModelOptions(true);
		modal.style.display = '';
	}

	function openEditModal(id) {
		api('ai-keys').then((keys) => {
			const k = keys.find((x) => Number(x.id) === id);
			if (!k) return;
			$('#splat-edit-id').value = k.id;
			$('#splat-modal-title').textContent = 'Edit API Key';
			$('#splat-key-label').value = k.label;
			$('#splat-key-apikey').value = '';
			$('#splat-key-apikey').placeholder = '•••••••••••• (Leave blank to keep unchanged)';
			$('#splat-key-priority').value = k.priority;
			$('#splat-key-tier').value = k.tier || 'free';
			providerSelect.value = k.provider;
			updateModelOptions();
			$('#splat-key-api-format').value = normalizeApiFormat(k.api_format || providers[k.provider]?.api_format || 'openai_compatible');
			$('#splat-key-auth-strategy').value = k.auth_strategy || authStrategyFor($('#splat-key-api-format').value);
			modelSelect.value = k.default_model || '';
			baseurlInput.value = k.base_url;
			$('#splat-key-temperature').value = k.default_temperature || '';
			$('#splat-key-max-tokens').value = k.default_max_tokens || '';
			$('#splat-key-max-prompt-tokens').value = k.max_prompt_tokens || '';
			$('#splat-key-expires-at').value = k.expires_at ? k.expires_at.replace(' ', 'T').slice(0, 16) : '';
			$('#splat-key-rotate-after-days').value = k.rotate_after_days || '';
			$('#splat-key-daily-token-limit').value = k.daily_token_limit || '';
			$('#splat-key-monthly-token-limit').value = k.monthly_token_limit || '';
			$('#splat-key-capabilities').value = k.capabilities_json || '';
			$('#splat-key-custom-headers').value = k.custom_headers || '';
			modal.style.display = '';
		});
	}

	function closeModal() {
		modal.style.display = 'none';
		$('#splat-modal-status').textContent = '';
	}

	on('#splat-add-key', 'click', openAddModal);
	on('#splat-modal-cancel', 'click', closeModal);
	on('.splat-modal-overlay', 'click', (e) => {
		closeModal();
		$('#splat-token-modal').style.display = 'none';
	});
	on(providerSelect, 'change', () => updateModelOptions(true));

	on('#splat-token-modal-copy', 'click', () => {
		const input = $('#splat-new-full-token');
		input.select();
		navigator.clipboard.writeText(input.value).then(() => {
			$('#splat-token-modal').style.display = 'none';
		});
	});

	const advancedToggle = $('#splat-toggle-advanced-fields');

	on(advancedToggle, 'change', () => {
		$$('.splat-advanced-field').forEach((el) => (el.style.display = advancedToggle.checked ? '' : 'none'));
	});

	function updateModelOptions(resetValue = false) {
		const isCustom = providerSelect.value === 'custom_openai_compatible';
		if (isCustom) advancedToggle.checked = true;
		$$('.splat-advanced-field').forEach((el) => (el.style.display = advancedToggle.checked ? '' : 'none'));

		const p = providers[providerSelect.value];
		modelOptions.innerHTML = '';
		if (p) {
			baseurlInput.placeholder = p.base_url;
			$('#splat-key-api-format').value = normalizeApiFormat(p.api_format || 'openai_compatible');
			$('#splat-key-auth-strategy').value = authStrategyFor($('#splat-key-api-format').value);
			(p.models || []).forEach((m) => {
				const opt = document.createElement('option');
				opt.value = m;
				modelOptions.appendChild(opt);
			});
			if (resetValue) modelSelect.value = p.models?.[0] || '';
		}
	}
	updateModelOptions(true);

	function normalizeApiFormat(format) {
		return (
			{
				openai: 'openai_compatible',
				google: 'google_gemini',
				anthropic: 'anthropic_messages',
			}[format] ||
			format ||
			'openai_compatible'
		);
	}

	function authStrategyFor(format) {
		if (format === 'google_gemini') return 'query_api_key';
		if (format === 'anthropic_messages') return 'x_api_key';
		return 'bearer';
	}

	on('#splat-modal-save', 'click', () => {
		const editId = $('#splat-edit-id').value;
		const p = providers[providerSelect.value];
		const data = {
			provider: providerSelect.value,
			api_format: $('#splat-key-api-format').value || normalizeApiFormat(p?.api_format),
			auth_strategy: $('#splat-key-auth-strategy').value || authStrategyFor($('#splat-key-api-format').value),
			label: $('#splat-key-label').value,
			api_key: $('#splat-key-apikey').value,
			default_model: modelSelect.value,
			base_url: baseurlInput.value || '',
			default_temperature: $('#splat-key-temperature').value || null,
			default_max_tokens: $('#splat-key-max-tokens').value || null,
			max_prompt_tokens: $('#splat-key-max-prompt-tokens').value || null,
			priority: Number($('#splat-key-priority').value) || 10,
			tier: $('#splat-key-tier').value || 'free',
			expires_at: $('#splat-key-expires-at').value || null,
			rotate_after_days: $('#splat-key-rotate-after-days').value || null,
			daily_token_limit: $('#splat-key-daily-token-limit').value || null,
			monthly_token_limit: $('#splat-key-monthly-token-limit').value || null,
			capabilities_json: $('#splat-key-capabilities').value.trim() || null,
			custom_headers: $('#splat-key-custom-headers').value.trim() || '',
		};

		const status = $('#splat-modal-status');
		status.textContent = 'Saving...';

		const endpoint = editId ? 'ai-keys/' + editId : 'ai-keys';
		const method = editId ? 'PUT' : 'POST';

		api(endpoint, method, data).then((r) => {
			if (r.error) {
				status.textContent = 'Error: ' + r.error;
			} else {
				closeModal();
				loadKeys();
			}
		});
	});

	// ── Reset Cooldowns ─────────────────────────────
	on('#splat-reset-cooldowns', 'click', () => {
		api('ai-keys/reset-cooldowns', 'POST').then(() => loadKeys());
	});

	// ── Settings ────────────────────────────────────
	api('settings').then((s) => {
		$('#splat-preferred-provider').value = s.preferred_provider || '';
		$('#splat-max-retries').value = s.max_retries;
		$('#splat-cooldown-429').value = s.cooldown_429;
		$('#splat-cooldown-5xx').value = s.cooldown_5xx;
		$('#splat-timeout').value = s.request_timeout;
		$('#splat-cache-ttl').value = s.cache_ttl ?? 86400;
		$('#splat-prefer-free').checked = !!s.prefer_free_keys;
		$('#splat-paid-strategy').value = s.paid_key_strategy || 'high_complexity_first';
	});

	// Load token status.
	const tokenInput = $('#splat-update-token');
	const tokenStatus = $('#splat-token-status');
	api('token').then((r) => {
		if (r.has_token) {
			tokenInput.value = '***configured';
			tokenStatus.innerHTML = '<span style="color:#46b450;">&#10003; Configured</span>';
		}
	});

	on('#splat-save-settings', 'click', () => {
		const status = $('#splat-settings-status');
		status.textContent = 'Saving...';

		// Save token first (separate endpoint), then save settings.
		const rawToken = tokenInput.value.trim();
		const tokenPromise = api('token', 'POST', { token: rawToken }).then((r) => {
			if (r.has_token) {
				tokenInput.value = '***configured';
				tokenStatus.innerHTML = '<span style="color:#46b450;">&#10003; Configured</span>';
			} else {
				tokenInput.value = '';
				tokenStatus.innerHTML = '';
			}
		});

		const settingsPromise = api('settings', 'POST', {
			preferred_provider: $('#splat-preferred-provider').value,
			max_retries: Number($('#splat-max-retries').value),
			cooldown_429: Number($('#splat-cooldown-429').value),
			cooldown_5xx: Number($('#splat-cooldown-5xx').value),
			request_timeout: Number($('#splat-timeout').value),
			cache_ttl: Number($('#splat-cache-ttl').value),
			prefer_free_keys: $('#splat-prefer-free').checked,
			paid_key_strategy: $('#splat-paid-strategy').value,
		});

		Promise.all([tokenPromise, settingsPromise]).then(([, r]) => {
			status.textContent = r.message ? 'Saved.' : 'Error';
			setTimeout(() => (status.textContent = ''), 3000);
		});
	});

	// ── Test Chat ───────────────────────────────────
	on('#splat-send-test', 'click', () => {
		const btn = $('#splat-send-test');
		const msg = $('#splat-test-message').value || 'Say hello in 10 words.';
		const status = $('#splat-test-status');
		const result = $('#splat-test-result');
		const output = $('#splat-test-output');

		btn.disabled = true;
		status.innerHTML =
			'<span class="spinner is-active" style="float:none; margin: 0 8px; display:inline-block; vertical-align: middle;"></span> <strong style="vertical-align:middle; color:#2271b1;">AI is thinking...</strong>';
		result.style.display = 'none';

		api('test-chat', 'POST', { message: msg })
			.then((r) => {
				btn.disabled = false;
				status.innerHTML = '';
				result.style.display = '';

				if (r.error) {
					result.className = 'notice notice-error';
					output.innerHTML = `<strong style="color:#d63638;">Request Failed:</strong><br><code style="background:transparent; color:inherit;">${esc(r.error)}</code>`;
				} else {
					result.className = 'notice notice-success';
					output.innerHTML = `<div style="margin-bottom:12px; padding-bottom:8px; border-bottom:1px solid #c3e6cb;">
<span style="display:inline-block; margin-right:15px;"><strong style="color:#155724;">Provider:</strong> <code>${esc(r.provider)}</code></span>
<span style="display:inline-block;"><strong style="color:#155724;">Model:</strong> <code>${esc(r.model)}</code></span>
</div>
<div style="font-size:14px; line-height:1.6; white-space:pre-wrap; color:#155724; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;">${esc(r.content)}</div>`;
				}
			})
			.catch((err) => {
				btn.disabled = false;
				status.innerHTML = '';
				result.style.display = '';
				result.className = 'notice notice-error';
				output.innerHTML = `<strong style="color:#d63638;">Network / Server Error:</strong><br><code style="background:transparent; color:inherit;">${esc(err.message)}</code>`;
			});
	});
})();

