<?php

namespace Addons\ContactLink;

use Addons\Helper;

\defined('ABSPATH') || exit;

final class ContactLink
{
	// ------------------------------------------------------

	public function __construct()
	{
		/**
		 * @var array $shortcodes
		 */
		$shortcodes = [
			'contact_link' => [$this, 'contact_link'],
		];

		foreach ($shortcodes as $shortcode => $function) {
			add_shortcode($shortcode, $function);
		}

		add_action('hd_footer_after_action', [$this, 'add_this_contact_link'], 11);
		add_filter('hd_footer_class_filter', [$this, 'modify_footer_class']);
	}

	// ------------------------------------------------------

	/**
	 * @param $default_class
	 *
	 * @return mixed|string
	 */
	public function modify_footer_class($default_class): mixed
	{
		$contact_link_option = Helper::getOption('contact_link__options');
		$flag = false;

		foreach ($contact_link_option as $option) {
			if (!empty($option['value'])) {
				$flag = true;
				break;
			}
		}

		if ($flag) {
			return $default_class . ' has-contact-link';
		}

		return $default_class;
	}

	// ------------------------------------------------------

	/**
	 * @return void
	 */
	public function add_this_contact_link(): void
	{
		echo Helper::doShortcode('contact_link');
	}

	// ------------------------------------------------------

	/**
	 * @param array $atts
	 *
	 * @return string
	 */
	public function contact_link(array $atts = []): string
	{
		$atts = shortcode_atts(
			[
				'class' => 'contact-link',
			],
			$atts,
			'contact_link'
		);

		$class = $atts['class'] ? ' ' . Helper::escAttr($atts['class']) : ' contact-link';

		ob_start();

		$contact_options = Helper::getOption('contact_link__options');
		$contact_links = Helper::filterSettingOptions('contact_links', []);

		if ($contact_options) {
			foreach ($contact_options as $key => $contact_option) {
				$value = $contact_option['value'] ?? '';

				$data = [
					'name' => $contact_links[$key]['name'] ?? '',
					'icon' => $contact_links[$key]['icon'] ?? '',
					'placeholder' => $contact_links[$key]['placeholder'] ?? '',
					'target' => $contact_links[$key]['target'] ?? '',
					'class' => $contact_links[$key]['class'] ?? '',
				];

				if (empty($value)) {
					continue;
				}

				$target = $data['target'] ? ' target="' . $data['target'] . '"' : '';
				$title = $value ? Helper::escAttr($value) : Helper::escAttr($data['name']);
				$classes = $data['class'] ? $key . ' ' . $data['class'] : $key;
				$thumb = '';

				if (Helper::isUrl($data['icon']) || str_starts_with($data['icon'], 'data:')):
					$thumb = '<img width="48" height="48" src="' . $data['icon'] . '" alt="' . Helper::escAttr($data['name']) . '">';
				elseif (str_starts_with($data['icon'], '<svg')):
					$thumb = $data['icon'];
				elseif (is_string($data['icon'])):
					$thumb = '<i class="' . $data['icon'] . '"></i>';
				endif;

				?>
				<li>
					<a<?= $target ?> class="<?= $classes ?>" href="<?= $value ?>" aria-label="<?= $title ?>">
						<?= $thumb ?>
						<span><?= $data['name'] ?></span>
						</a>
				</li>
				<?php
			}
		}

		$content = ob_get_clean();

		// Add popup trigger button to the fixed icons list
		$popup_trigger = '<li class="promo-popup-item">
			<button class="popup-trigger-btn" id="promo-popup-trigger" aria-label="Mở form ưu đãi" title="Nhận ưu đãi">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
					<polyline points="9 22 9 12 15 12 15 22"></polyline>
				</svg>
				<span class="popup-trigger-text">Ưu đãi</span>
			</button>
		</li>';

		return $content ? '<ul class="add-this' . $class . '">' . $content . $popup_trigger . '</ul>' : '';
	}

	// ------------------------------------------------------
}
