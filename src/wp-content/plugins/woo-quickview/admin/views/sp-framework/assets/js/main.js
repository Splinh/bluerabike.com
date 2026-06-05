
; (function ($, window, document, undefined) {
	'use strict';

	//
	// Constants
	//
	var SP_WQV_Framework = SP_WQV_Framework || {};

	SP_WQV_Framework.funcs = {};

	SP_WQV_Framework.vars = {
		onloaded: false,
		$body: $('body'),
		$window: $(window),
		$document: $(document),
		$form_warning: null,
		is_confirm: false,
		form_modified: false,
		code_themes: [],
		is_rtl: $('body').hasClass('rtl'),
	};

	//
	// Helper Functions
	//
	SP_WQV_Framework.helper = {

		//
		// Generate UID
		//
		uid: function (prefix) {
			return (prefix || '') + Math.random().toString(36).substr(2, 9);
		},

		// Quote regular expression characters
		//
		preg_quote: function (str) {
			return (str + '').replace(/(\[|\])/g, "\\$1");
		},

		//
		// Reneme input names
		//
		name_nested_replace: function ($selector, field_id) {

			var checks = [];
			var regex = new RegExp(SP_WQV_Framework.helper.preg_quote(field_id + '[\\d+]'), 'g');

			$selector.find(':radio').each(function () {
				if (this.checked || this.orginal_checked) {
					this.orginal_checked = true;
				}
			});

			$selector.each(function (index) {
				$(this).find(':input').each(function () {
					this.name = this.name.replace(regex, field_id + '[' + index + ']');
					if (this.orginal_checked) {
						this.checked = true;
					}
				});
			});

		},

		//
		// Debounce
		//
		debounce: function (callback, threshold, immediate) {
			var timeout;
			return function () {
				var context = this, args = arguments;
				var later = function () {
					timeout = null;
					if (!immediate) {
						callback.apply(context, args);
					}
				};
				var callNow = (immediate && !timeout);
				clearTimeout(timeout);
				timeout = setTimeout(later, threshold);
				if (callNow) {
					callback.apply(context, args);
				}
			};
		},

	};

	//
	// Custom clone for textarea and select clone() bug
	//
	$.fn.sp_wqv_clone = function () {

		var base = $.fn.clone.apply(this, arguments),
			clone = this.find('select').add(this.filter('select')),
			cloned = base.find('select').add(base.filter('select'));

		for (var i = 0; i < clone.length; ++i) {
			for (var j = 0; j < clone[i].options.length; ++j) {

				if (clone[i].options[j].selected === true) {
					cloned[i].options[j].selected = true;
				}

			}
		}

		this.find(':radio').each(function () {
			this.orginal_checked = this.checked;
		});

		return base;

	};

	//
	// Expand All Options
	//
	$.fn.sp_wqv_expand_all = function () {
		return this.each(function () {
			$(this).on('click', function (e) {

				e.preventDefault();
				$('.sp_wqv-wrapper').toggleClass('sp_wqv-show-all');
				$('.sp_wqv-section').sp_wqv_reload_script();
				$(this).find('.fa').toggleClass('fa-indent').toggleClass('fa-outdent');

			});
		});
	};

	//
	// Options Navigation
	//
	$.fn.sp_wqv_nav_options = function () {
		return this.each(function () {

			var $nav = $(this),
				$window = $(window),
				$wpwrap = $('#wpwrap'),
				$links = $nav.find('a'),
				$last;

			$window.on('hashchange sp_wqv.hashchange', function () {

				var hash = window.location.hash.replace('#tab=', '');
				var slug = hash ? hash : $links.first().attr('href').replace('#tab=', '');
				var $link = $('[data-tab-id="' + slug + '"]');

				if ($link.length) {

					$link.closest('.sp_wqv-tab-item').addClass('sp_wqv-tab-expanded').siblings().removeClass('sp_wqv-tab-expanded');

					if ($link.next().is('ul')) {

						$link = $link.next().find('li').first().find('a');
						slug = $link.data('tab-id');

					}

					$links.removeClass('sp_wqv-active');
					$link.addClass('sp_wqv-active');

					if ($last) {
						$last.addClass('hidden');
					}

					var $section = $('[data-section-id="' + slug + '"]');

					$section.removeClass('hidden');
					$section.sp_wqv_reload_script();

					$('.sp_wqv-section-id').val($section.index() + 1);

					$last = $section;

					if ($wpwrap.hasClass('wp-responsive-open')) {
						$('html, body').animate({ scrollTop: ($section.offset().top - 50) }, 200);
						$wpwrap.removeClass('wp-responsive-open');
					}

				}

			}).trigger('sp_wqv.hashchange');

		});
	};






	//
	// Sticky Header
	//
	$.fn.sp_wqv_sticky = function () {
		return this.each(function () {

			var $this = $(this),
				$window = $(window),
				$inner = $this.find('.sp_wqv-header-inner'),
				padding = parseInt($inner.css('padding-left')) + parseInt($inner.css('padding-right')),
				offset = 32,
				scrollTop = 0,
				lastTop = 0,
				ticking = false,
				stickyUpdate = function () {

					var offsetTop = $this.offset().top,
						stickyTop = Math.max(offset, offsetTop - scrollTop),
						winWidth = $window.innerWidth();

					if (stickyTop <= offset && winWidth > 782) {
						$inner.css({ width: $this.outerWidth() - padding });
						$this.css({ height: $this.outerHeight() }).addClass('sp_wqv-sticky');
					} else {
						$inner.removeAttr('style');
						$this.removeAttr('style').removeClass('sp_wqv-sticky');
					}

				},
				requestTick = function () {

					if (!ticking) {
						requestAnimationFrame(function () {
							stickyUpdate();
							ticking = false;
						});
					}

					ticking = true;

				},
				onSticky = function () {

					scrollTop = $window.scrollTop();
					requestTick();

				};

			$window.on('scroll resize', onSticky);

			onSticky();

		});
	};

	//
	// Dependency System
	//
	$.fn.sp_wqv_dependency = function () {
		return this.each(function () {

			var $this = $(this),
				$fields = $this.children('[data-controller]');

			if ($fields.length) {

				var normal_ruleset = $.sp_wqv_deps.createRuleset(),
					global_ruleset = $.sp_wqv_deps.createRuleset(),
					normal_depends = [],
					global_depends = [];

				$fields.each(function () {

					var $field = $(this),
						controllers = $field.data('controller').split('|'),
						conditions = $field.data('condition').split('|'),
						values = $field.data('value').toString().split('|'),
						is_global = $field.data('depend-global') ? true : false,
						ruleset = (is_global) ? global_ruleset : normal_ruleset;

					$.each(controllers, function (index, depend_id) {
						var value = values[index] || '',
							condition = conditions[index] || conditions[0];

						ruleset = ruleset.createRule('[data-depend-id="' + depend_id + '"]', condition, value);

						ruleset.include($field);

						if (is_global) {
							global_depends.push(depend_id);
						} else {
							normal_depends.push(depend_id);
						}

					});

				});

				if (normal_depends.length) {
					$.sp_wqv_deps.enable($this, normal_ruleset, normal_depends);
				}

				if (global_depends.length) {
					$.sp_wqv_deps.enable(SP_WQV_Framework.vars.$body, global_ruleset, global_depends);
				}

			}

		});
	};

	//
	// Field: sortable
	//
	$.fn.sp_wqv_field_sortable = function () {
		return this.each(function () {
			var $sortable = $(this).find(".sp_wqv-sortable");

			$sortable.sortable({
				axis: "y",
				helper: "original",
				cursor: "move",
				disabled: true,
				placeholder: "widget-placeholder",
				update: function (event, ui) {
					$sortable.sp_wqv_customizer_refresh();
				},
			});

			$sortable.find(".sp_wqv-sortable-content").sp_wqv_reload_script();
		});
	};
	//
	// Field: code_editor
	//
	$.fn.sp_wqv_field_code_editor = function () {
		return this.each(function () {
			if (typeof wp === 'undefined' || typeof wp.codeEditor === 'undefined') {
				return;
			}

			var $this = $(this),
				$textarea = $this.find('textarea'),
				settings = $textarea.data('editor') || {};

			// Merge with WP defaults
			var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
			editorSettings.codemirror = _.extend(
				{},
				editorSettings.codemirror,
				settings
			);

			// Initialize editor
			var editor = wp.codeEditor.initialize($textarea[0], editorSettings);
			// Sync changes back to textarea
			editor.codemirror.on('change', function () {
				$textarea.val(editor.codemirror.getValue()).trigger('change');
			});
		});
	};

	//
	// Field: slider
	//
	$.fn.sp_wqv_field_slider = function () {
		return this.each(function () {

			var $this = $(this),
				$input = $this.find('input'),
				$slider = $this.find('.sp_wqv-slider-ui'),
				data = $input.data(),
				value = $input.val() || 0;

			if ($slider.hasClass('ui-slider')) {
				$slider.empty();
			}

			$slider.slider({
				range: 'min',
				value: value,
				min: data.min || 0,
				max: data.max || 100,
				step: data.step || 1,
				slide: function (e, o) {
					$input.val(o.value).trigger('change');
				}
			});

			$input.on('keyup', function () {
				$slider.slider('value', $input.val());
			});

		});
	};

	//
	// Field: spinner
	//
	$.fn.sp_wqv_field_spinner = function () {
		return this.each(function () {

			var $this = $(this),
				$input = $this.find('input'),
				$inited = $this.find('.ui-button'),
				data = $input.data();

			if ($inited.length) {
				$inited.remove();
			}

			$input.spinner({
				min: data.min || 0,
				max: data.max || 100,
				step: data.step || 1,
				create: function (event, ui) {
					if (data.unit) {
						$input.after('<span class="ui-button sp_wqv--unit">' + data.unit + '</span>');
					}
				},
				spin: function (event, ui) {
					$input.val(ui.value).trigger('change');
				}
			});

		});
	};

	//
	// Field: switcher
	//
	$.fn.sp_wqv_field_switcher = function () {
		return this.each(function () {

			var $switcher = $(this).find('.sp_wqv--switcher');

			$switcher.on('click', function () {

				var value = 0;
				var $input = $switcher.find('input');

				if ($switcher.hasClass('sp_wqv--active')) {
					$switcher.removeClass('sp_wqv--active');
				} else {
					value = 1;
					$switcher.addClass('sp_wqv--active');
				}

				$input.val(value).trigger('change');

			});

		});
	};

	//
	// Confirm
	//
	$.fn.sp_wqv_confirm = function () {
		return this.each(function () {
			$(this).on('click', function (e) {

				var confirm_text = $(this).data('confirm') || window.sp_wqv_vars.i18n.confirm;
				var confirm_answer = confirm(confirm_text);

				if (confirm_answer) {
					SP_WQV_Framework.vars.is_confirm = true;
					SP_WQV_Framework.vars.form_modified = false;
				} else {
					e.preventDefault();
					return false;
				}

			});
		});
	};

	$.fn.serializeObject = function () {

		var obj = {};

		$.each(this.serializeArray(), function (i, o) {
			var n = o.name,
				v = o.value;

			obj[n] = obj[n] === undefined ? v
				: $.isArray(obj[n]) ? obj[n].concat(v)
					: [obj[n], v];
		});

		return obj;

	};

	//
	// Options Save
	//
	$.fn.sp_wqv_save = function () {
		return this.each(function () {

			var $this = $(this),
				$buttons = $('.sp_wqv-save'),
				$panel = $('.sp_wqv-options'),
				flooding = false,
				timeout;

			$this.on('click', function (e) {

				if (!flooding) {

					var $text = $this.data('save'),
						$value = $this.val();

					$buttons.attr('value', $text);

					if ($this.hasClass('sp_wqv-save-ajax')) {
						e.preventDefault();

						$panel.addClass('sp_wqv-saving');
						$buttons.prop('disabled', true);

						window.wp.ajax.post('sp_wqv_' + $panel.data('unique') + '_ajax_save', {
							data: $('#sp_wqv-form').serializeJSONSP_WQV_Framework(),
							nonce: $('#sp_wqv_options_nonce' + $panel.data('unique')).val(),
						})
							.done(function (response) {

								// clear errors
								$('.sp_wqv-error').remove();

								if (Object.keys(response.errors).length) {

									var error_icon = '<i class="sp_wqv-label-error sp_wqv-error">!</i>';

									$.each(response.errors, function (key, error_message) {

										var $field = $('[data-depend-id="' + key + '"]'),
											$link = $('a[href="#tab=' + $field.closest('.sp_wqv-section').data('section-id') + '"]'),
											$tab = $link.closest('.sp_wqv-tab-item');

										$field.closest('.sp_wqv-fieldset').append('<p class="sp_wqv-error sp_wqv-error-text">' + error_message + '</p>');

										if (!$link.find('.sp_wqv-error').length) {
											$link.append(error_icon);
										}

										if (!$tab.find('.sp_wqv-arrow .sp_wqv-error').length) {
											$tab.find('.sp_wqv-arrow').append(error_icon);
										}

									});

								}

								$panel.removeClass('sp_wqv-saving');
								$buttons.prop('disabled', false).attr('value', $value);
								flooding = false;

								SP_WQV_Framework.vars.form_modified = false;
								SP_WQV_Framework.vars.$form_warning.hide();

								clearTimeout(timeout);

								var $result_success = $('.sp_wqv-form-success');
								$result_success.empty().append(response.notice).fadeIn('fast', function () {
									timeout = setTimeout(function () {
										$result_success.fadeOut('fast');
									}, 1000);
								});

							})
							.fail(function (response) {
								alert(response.error);
							});

					} else {

						SP_WQV_Framework.vars.form_modified = false;

					}

				}

				flooding = true;

			});

		});
	};

	//
	// Option Framework
	//
	$.fn.sp_wqv_options = function () {
		return this.each(function () {

			var $this = $(this),
				$content = $this.find('.sp_wqv-content'),
				$form_success = $this.find('.sp_wqv-form-success'),
				$form_warning = $this.find('.sp_wqv-form-warning'),
				$save_button = $this.find('.sp_wqv-header .sp_wqv-save');

			SP_WQV_Framework.vars.$form_warning = $form_warning;

			// Shows a message white leaving theme options without saving
			if ($form_warning.length) {

				window.onbeforeunload = function () {
					return (SP_WQV_Framework.vars.form_modified) ? true : undefined;
				};

				$content.on('change keypress', ':input', function () {
					if (!SP_WQV_Framework.vars.form_modified) {
						$form_success.hide();
						$form_warning.fadeIn('fast');
						SP_WQV_Framework.vars.form_modified = true;
					}
				});

			}

			if ($form_success.hasClass('sp_wqv-form-show')) {
				setTimeout(function () {
					$form_success.fadeOut('fast');
				}, 1000);
			}

			$(document).on('keydown', function (event) {
				if ((event.ctrlKey || event.metaKey) && event.which === 83) {
					$save_button.trigger('click');
					event.preventDefault();
					return false;
				}
			});

		});
	};


	//
	// WP Color Picker
	//
	if (typeof Color === 'function') {

		Color.prototype.toString = function () {

			if (this._alpha < 1) {
				return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
			}

			var hex = parseInt(this._color, 10).toString(16);

			if (this.error) { return ''; }

			if (hex.length < 6) {
				for (var i = 6 - hex.length - 1; i >= 0; i--) {
					hex = '0' + hex;
				}
			}

			return '#' + hex;

		};

	}

	SP_WQV_Framework.funcs.parse_color = function (color) {

		var value = color.replace(/\s+/g, ''),
			trans = (value.indexOf('rgba') !== -1) ? parseFloat(value.replace(/^.*,(.+)\)/, '$1') * 100) : 100,
			rgba = (trans < 100) ? true : false;

		return { value: value, transparent: trans, rgba: rgba };

	};

	$.fn.sp_wqv_color = function () {
		return this.each(function () {

			var $input = $(this),
				picker_color = SP_WQV_Framework.funcs.parse_color($input.val()),
				palette_color = window.sp_wqv_vars.color_palette.length ? window.sp_wqv_vars.color_palette : true,
				$container;

			// Destroy and Reinit
			if ($input.hasClass('wp-color-picker')) {
				$input.closest('.wp-picker-container').after($input).remove();
			}

			$input.wpColorPicker({
				palettes: palette_color,
				change: function (event, ui) {

					var ui_color_value = ui.color.toString();

					$container.removeClass('sp_wqv--transparent-active');
					$container.find('.sp_wqv--transparent-offset').css('background-color', ui_color_value);
					$input.val(ui_color_value).trigger('change');

				},
				create: function () {

					$container = $input.closest('.wp-picker-container');

					var a8cIris = $input.data('a8cIris'),
						$transparent_wrap = $('<div class="sp_wqv--transparent-wrap">' +
							'<div class="sp_wqv--transparent-slider"></div>' +
							'<div class="sp_wqv--transparent-offset"></div>' +
							'<div class="sp_wqv--transparent-text"></div>' +
							'<div class="sp_wqv--transparent-button">transparent <i class="fas fa-toggle-off"></i></div>' +
							'</div>').appendTo($container.find('.wp-picker-holder')),
						$transparent_slider = $transparent_wrap.find('.sp_wqv--transparent-slider'),
						$transparent_text = $transparent_wrap.find('.sp_wqv--transparent-text'),
						$transparent_offset = $transparent_wrap.find('.sp_wqv--transparent-offset'),
						$transparent_button = $transparent_wrap.find('.sp_wqv--transparent-button');

					if ($input.val() === 'transparent') {
						$container.addClass('sp_wqv--transparent-active');
					}

					$transparent_button.on('click', function () {
						if ($input.val() !== 'transparent') {
							$input.val('transparent').trigger('change').removeClass('iris-error');
							$container.addClass('sp_wqv--transparent-active');
						} else {
							$input.val(a8cIris._color.toString()).trigger('change');
							$container.removeClass('sp_wqv--transparent-active');
						}
					});

					$transparent_slider.slider({
						value: picker_color.transparent,
						step: 1,
						min: 0,
						max: 100,
						slide: function (event, ui) {

							var slide_value = parseFloat(ui.value / 100);
							a8cIris._color._alpha = slide_value;
							$input.wpColorPicker('color', a8cIris._color.toString());
							$transparent_text.text((slide_value === 1 || slide_value === 0 ? '' : slide_value));

						},
						create: function () {

							var slide_value = parseFloat(picker_color.transparent / 100),
								text_value = slide_value < 1 ? slide_value : '';

							$transparent_text.text(text_value);
							$transparent_offset.css('background-color', picker_color.value);

							$container.on('click', '.wp-picker-clear', function () {

								a8cIris._color._alpha = 1;
								$transparent_text.text('');
								$transparent_slider.slider('option', 'value', 100);
								$container.removeClass('sp_wqv--transparent-active');
								$input.trigger('change');

							});

							$container.on('click', '.wp-picker-default', function () {

								var default_color = SP_WQV_Framework.funcs.parse_color($input.data('default-color')),
									default_value = parseFloat(default_color.transparent / 100),
									default_text = default_value < 1 ? default_value : '';

								a8cIris._color._alpha = default_value;
								$transparent_text.text(default_text);
								$transparent_slider.slider('option', 'value', default_color.transparent);

								if (default_color.value === 'transparent') {
									$input.removeClass('iris-error');
									$container.addClass('sp_wqv--transparent-active');
								}

							});

						}
					});
				}
			});

		});
	};

	//
	// Field: icon
	//
	$.fn.sp_wqv_field_icon = function () {

		return this.each(function () {

			var $this = $(this);
			$this.on('click', '.sp_wqvp-icon-add', function (e) {

				e.preventDefault();

				var $button = $(this);
				var $modal = $('#sp_wqvp-modal-icon');
				$modal.show();

				SP_WQV_Framework.vars.$icon_target = $this;

				if (!SP_WQV_Framework.vars.icon_modal_loaded) {
					$modal.find('.sp_wqvp-modal-loading').show();

					window.wp.ajax.post('spqvp-get-icons', {
						nonce: $button.data('nonce')
					}).done(function (response) {
						$modal.find('.sp_wqvp-modal-loading').hide();
						SP_WQV_Framework.vars.icon_modal_loaded = true;

						var $load = $modal.find('.sp_wqvp-modal-load').html(response.content);
						$load.on('click', 'a', function (e) {
							e.preventDefault();
							var icon = $(this).data('sp_wqvp-icon');

							SP_WQV_Framework.vars.$icon_target.find('i').removeAttr('class').addClass(icon);
							SP_WQV_Framework.vars.$icon_target.find('input').val(icon).trigger('change');
							SP_WQV_Framework.vars.$icon_target.find('.sp_wqvp-icon-preview').removeClass('hidden');
							SP_WQV_Framework.vars.$icon_target.find('.sp_wqvp-warning-primary.fa.fa-trash').removeClass('hidden');

							$modal.hide();
						});

						$modal.on('change keyup', '.sp_wqvp-icon-search', function () {
							var value = $(this).val(),
								$icons = $load.find('a');

							$icons.each(function () {
								var $elem = $(this);

								if ($elem.data('sp_wqvp-icon').search(new RegExp(value, 'i')) < 0) {
									$elem.hide();
								} else {
									$elem.show();
								}

							});
						});

						$modal.on('click', '.sp_wqvp-modal-close, .sp_wqvp-modal-overlay', function () {
							$modal.hide();

						});
					});
				}
			});

			$this.on('click', '.sp_wqvp-warning-primary.fa.fa-trash', function (e) {
				e.preventDefault();

				$this.find('.sp_wqvp-icon-preview').addClass('hidden');
				$this.find('input').val('').trigger('change');
				$(this).addClass('hidden');

			});

		});
	};

	//
	// ChosenJS
	//
	$.fn.sp_wqv_chosen = function () {
		return this.each(function () {

			var $this = $(this),
				$inited = $this.parent().find('.chosen-container'),
				is_sortable = $this.hasClass('sp_wqv-chosen-sortable') || false,
				is_ajax = $this.hasClass('sp_wqv-chosen-ajax') || false,
				is_multiple = $this.attr('multiple') || false,
				set_width = is_multiple ? '100%' : 'auto',
				set_options = $.extend({
					allow_single_deselect: true,
					disable_search_threshold: 10,
					width: set_width,
					no_results_text: window.sp_wqv_vars.i18n.no_results_text,
				}, $this.data('chosen-settings'));

			if ($inited.length) {
				$inited.remove();
			}

			// Chosen ajax
			if (is_ajax) {

				var set_ajax_options = $.extend({
					data: {
						type: 'post',
						nonce: '',
					},
					allow_single_deselect: true,
					disable_search_threshold: -1,
					width: '100%',
					min_length: 3,
					type_delay: 500,
					typing_text: window.sp_wqv_vars.i18n.typing_text,
					searching_text: window.sp_wqv_vars.i18n.searching_text,
					no_results_text: window.sp_wqv_vars.i18n.no_results_text,
				}, $this.data('chosen-settings'));

				$this.SP_WQV_FrameworkAjaxChosen(set_ajax_options);

			} else {

				$this.chosen(set_options);

			}

			// Chosen keep options order
			if (is_multiple) {

				var $hidden_select = $this.parent().find('.sp_wqv-hide-select');
				var $hidden_value = $hidden_select.val() || [];

				$this.on('change', function (obj, result) {

					if (result && result.selected) {
						$hidden_select.append('<option value="' + result.selected + '" selected="selected">' + result.selected + '</option>');
					} else if (result && result.deselected) {
						$hidden_select.find('option[value="' + result.deselected + '"]').remove();
					}

					// Force customize refresh
					if (window.wp.customize !== undefined && $hidden_select.children().length === 0 && $hidden_select.data('customize-setting-link')) {
						window.wp.customize.control($hidden_select.data('customize-setting-link')).setting.set('');
					}

					$hidden_select.trigger('change');

				});

				// Chosen order abstract
				$this.SP_WQV_FrameworkChosenOrder($hidden_value, true);

			}

			// Chosen sortable
			if (is_sortable) {

				var $chosen_container = $this.parent().find('.chosen-container');
				var $chosen_choices = $chosen_container.find('.chosen-choices');

				$chosen_choices.bind('mousedown', function (event) {
					if ($(event.target).is('span')) {
						event.stopPropagation();
					}
				});

				$chosen_choices.sortable({
					items: 'li:not(.search-field)',
					helper: 'orginal',
					cursor: 'move',
					placeholder: 'search-choice-placeholder',
					start: function (e, ui) {
						ui.placeholder.width(ui.item.innerWidth());
						ui.placeholder.height(ui.item.innerHeight());
					},
					update: function (e, ui) {

						var select_options = '';
						var chosen_object = $this.data('chosen');
						var $prev_select = $this.parent().find('.sp_wqv-hide-select');

						$chosen_choices.find('.search-choice-close').each(function () {
							var option_array_index = $(this).data('option-array-index');
							$.each(chosen_object.results_data, function (index, data) {
								if (data.array_index === option_array_index) {
									select_options += '<option value="' + data.value + '" selected>' + data.value + '</option>';
								}
							});
						});

						$prev_select.children().remove();
						$prev_select.append(select_options);
						$prev_select.trigger('change');

					}
				});

			}

		});
	};

	//
	// Helper Checkbox Checker
	//
	$.fn.sp_wqv_checkbox = function () {
		return this.each(function () {

			var $this = $(this),
				$input = $this.find('.sp_wqv--input'),
				$checkbox = $this.find('.sp_wqv--checkbox');

			$checkbox.on('click', function () {
				$input.val(Number($checkbox.prop('checked'))).trigger('change');
			});

		});
	};

	//
	// Siblings
	//
	$.fn.sp_wqv_siblings = function () {
		return this.each(function () {

			var $this = $(this),
				$siblings = $this.find('.sp_wqvp--sibling'),
				multiple = $this.data('multiple') || false;

			$siblings.on('click', function () {

				var $sibling = $(this);

				if (multiple) {

					if ($sibling.hasClass('sp_wqvp--active')) {
						$sibling.removeClass('sp_wqvp--active');
						$sibling.find('input').prop('checked', false).trigger('change');
					} else {
						$sibling.addClass('sp_wqvp--active');
						$sibling.find('input').prop('checked', true).trigger('change');
					}

				} else {

					$this.find('input').prop('checked', false);
					$sibling.find('input').prop('checked', true).trigger('change');
					$sibling.addClass('sp_wqvp--active').siblings().removeClass('sp_wqvp--active');

				}

			});

		});
	};

	//
	// Help Tooltip
	//
	$.fn.sp_wqv_help = function () {
		return this.each(function () {

			var $this = $(this),
				$tooltip,
				offset_left;

			$this.on({
				mouseenter: function () {

					$tooltip = $('<div class="sp_wqv-tooltip"></div>').html($this.find('.sp_wqv-help-text').html()).appendTo('body');
					offset_left = (SP_WQV_Framework.vars.is_rtl) ? ($this.offset().left - $tooltip.outerWidth()) : ($this.offset().left + 24);

					$tooltip.css({
						top: $this.offset().top - (($tooltip.outerHeight() / 2) - 14),
						left: offset_left,
					});

				},
				mouseleave: function () {

					if ($tooltip !== undefined) {
						$tooltip.remove();
					}

				}

			});

		});
	};






	//
	// Window on resize
	//
	SP_WQV_Framework.vars.$window.on('resize sp_wqv.resize', SP_WQV_Framework.helper.debounce(function (event) {

		var window_width = navigator.userAgent.indexOf('AppleWebKit/') > -1 ? SP_WQV_Framework.vars.$window.width() : window.innerWidth;

		if (window_width <= 782 && !SP_WQV_Framework.vars.onloaded) {
			$('.sp_wqv-section').sp_wqv_reload_script();
			SP_WQV_Framework.vars.onloaded = true;
		}

	}, 200)).trigger('sp_wqv.resize');



	//
	// Nav Menu Options Framework
	//
	$.fn.sp_wqv_nav_menu = function () {
		return this.each(function () {

			var $navmenu = $(this);

			$navmenu.on('click', 'a.item-edit', function () {
				$(this).closest('li.menu-item').find('.sp_wqv-fields').sp_wqv_reload_script();
			});

			$navmenu.on('sortstop', function (event, ui) {
				ui.item.find('.sp_wqv-fields').sp_wqv_reload_script_retry();
			});

		});
	};

	//
	// Retry Plugins
	//
	$.fn.sp_wqv_reload_script_retry = function () {
		return this.each(function () {

			var $this = $(this);

			if ($this.data('inited')) {
				$this.children('.sp_wqv-field-wp_editor').sp_wqv_field_wp_editor();
			}

		});
	};

	//
	// Reload Plugins
	//
	$.fn.sp_wqv_reload_script = function (options) {

		var settings = $.extend({
			dependency: true,
		}, options);

		return this.each(function () {

			var $this = $(this);

			// Avoid for conflicts
			if (!$this.data('inited')) {

				// Field plugins
				$this.children('.sp_wqv-field-code_editor').sp_wqv_field_code_editor();
				$this.children('.sp_wqv-field-slider').sp_wqv_field_slider();


				$this.children('.sp_wqv-field-spinner').sp_wqv_field_spinner();
				$this.children('.sp_wqv-field-switcher').sp_wqv_field_switcher();
				$this.children(".sp_wqv-field-sortable").sp_wqv_field_sortable();


				// Field colors
				$this.children('.sp_wqv-field-border').find('.sp_wqv-color').sp_wqv_color();
				$this.children('.sp_wqv-field-color').find('.sp_wqv-color').sp_wqv_color();
				$this.children('.sp_wqv-field-color_group').find('.sp_wqv-color').sp_wqv_color();
				$this.children('.sp_wqv-field-typography').find('.sp_wqvp-color').sp_wqv_color();
				$this.children('.sp_wqv-field-icon').sp_wqv_field_icon();

				// Field chosenjs
				$this.children('.sp_wqv-field-select').find('.sp_wqv-chosen').sp_wqv_chosen();

				// Field Checkbox
				$this.children('.sp_wqv-field-checkbox').find('.sp_wqv-checkbox').sp_wqv_checkbox();
				// Field Siblings
				$this.children('.sp_wqv-field-button_set').find('.sp_wqvp-siblings').sp_wqv_siblings();
				$this.children('.sp_wqv-field-image_select').find('.sp_wqvp-siblings').sp_wqv_siblings();

				// Help Tooptip
				$this.children('.sp_wqv-field').find('.sp_wqv-help').sp_wqv_help();

				if (settings.dependency) {
					$this.sp_wqv_dependency();
				}

				$this.data('inited', true);

				$(document).trigger('sp_wqv-reload-script', $this);

			}

		});
	};

	//
	// Document ready and run scripts
	//
	$(document).ready(function () {

		$('.sp_wqv-save').sp_wqv_save();
		$('.sp_wqv-options').sp_wqv_options();
		$('.sp_wqv-sticky-header').sp_wqv_sticky();
		$('.sp_wqv-nav-options').sp_wqv_nav_options();
		$('.sp_wqv-confirm').sp_wqv_confirm();
		$('.sp_wqv-expand-all').sp_wqv_expand_all();
		$('.sp_wqv-onload').sp_wqv_reload_script();
		$('#menu-to-edit').sp_wqv_nav_menu();

	});

	$(document).on('keyup change', '#sp_wqv-form', function (e) {
		e.preventDefault();
		var $button = $(this).find('.sp_wqv-save');
		$button.css({ "background-color": "#00C263", "pointer-events": "initial" }).val('Save Settings');
	});
	$(".sp_wqv-save").on('click', function (e) {
		e.preventDefault();
		$(this).css({ "background-color": "#C5C5C6", "pointer-events": "none" }).val('Changes Saved');
	})
	$("select option:contains((Pro))").attr('disabled', true);
})(jQuery, window, document);
