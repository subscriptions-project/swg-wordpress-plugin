<?php

/**
 * Class SubscribeWithGoogle\WordPress\AdminPage
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/**
 * Adds admin page.
 */
final class AdminPage
{

	/** Adds WordPress actions. */
	public function __construct()
	{
		add_action('admin_menu', array(__CLASS__, 'add_link'));
		add_action('admin_init', array(__CLASS__, 'prepare'));
	}

	/** Adds link to admin menu. */
	public static function add_link()
	{
		$page_title = 'Subscribe with Google';
		$menu_title = 'Subscribe with Google';
		$capability = 'manage_options';
		$slug       = 'subscribe_with_google';
		$callback   = array(__CLASS__, 'render');
		$icon       = 'dashicons-megaphone';
		$position   = 100;

		add_menu_page(
			$page_title,
			$menu_title,
			$capability,
			$slug,
			$callback,
			$icon,
			$position
		);
	}

	/** Renders the admin page. */
	public static function render()
	{
?>
		<div class="wrap">
			<h2>Subscribe with Google</h2>
			<form method="post" action="options.php">
				<?php
				settings_fields('subscribe_with_google');
				do_settings_sections('subscribe_with_google');
				submit_button();
				?>
			</form>
		</div>
<?php
	}

	/** Prepares the admin page to be rendered. */
	public static function prepare()
	{
		add_settings_section(Plugin::key('configuration'), 'Configuration', false, 'subscribe_with_google');

		self::prepare_settings();
	}

	/** Prepares settings to be rendered. */
	public static function prepare_settings()
	{
		self::add_setting(
			array(
				'id'          => 'products',
				'label'       => 'Product Names',
				'type'        => 'textarea',
				'description' => 'Product names, one per line.',
			)
		);

		self::add_setting(
			array(
				'id'          => 'publication_id',
				'label'       => 'Publication ID',
				'type'        => 'text',
				'description' => 'Unique indentifier for your publication.',
			)
		);

		self::add_setting(
			array(
				'id'          => 'oauth_client_id',
				'label'       => 'OAuth Client ID',
				'type'        => 'text',
				'description' => 'Unique identifier for your Google OAuth Client.',
			)
		);

		self::add_setting(
			array(
				'id'          => 'oauth_client_secret',
				'label'       => 'OAuth Client Secret',
				'type'        => 'text',
				'description' => 'Secret key for your Google OAuth Client.',
			)
		);
	}

	/**
	 * Adds a setting to the admin page.
	 *
	 * @param array[string]string $setting describes the setting.
	 */
	private static function add_setting($setting)
	{
		$setting['options'] = false;
		$setting['section'] = Plugin::key('configuration');
		$setting['uid']     = Plugin::key($setting['id']);
		$setting['value']   = get_option($setting['uid']);

		$render_fn = 'render_' . $setting['type'] . '_setting';
		$page      = 'subscribe_with_google';

		add_settings_field(
			$setting['uid'],
			$setting['label'],
			array(__CLASS__, $render_fn),
			$page,
			$setting['section'],
			$setting
		);

		register_setting('subscribe_with_google', $setting['uid']);
	}


	/**
	 * Renders a textarea setting on the admin page.
	 *
	 * @param array[string]string $setting describes the setting.
	 */
	public static function render_textarea_setting($setting)
	{
		echo '<textarea';
		echo ' id="' . esc_attr($setting['uid']) . '"';
		echo ' name="' . esc_attr($setting['uid']) . '"';
		echo ' style="min-height: 96px;"'; // TODO: Add external stylesheet.
		echo '>';
		echo esc_attr($setting['value']);
		echo '</textarea>';
		echo '<p class="description">';
		echo esc_attr($setting['description']);
		echo '</p>';
	}

	/**
	 * Renders a text setting on the admin page.
	 *
	 * @param array[string]string $setting describes the setting.
	 */
	public static function render_text_setting($setting)
	{
		echo '<input';
		echo ' id="' . esc_attr($setting['uid']) . '"';
		echo ' name="' . esc_attr($setting['uid']) . '"';
		echo ' value="' . esc_attr($setting['value']) . '"';
		echo '/>';
		echo '<p class="description">';
		echo esc_attr($setting['description']);
		echo '</p>';
	}
}
