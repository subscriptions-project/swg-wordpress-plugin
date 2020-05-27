<?php
/**
 * Class SubscribeWithGoogle\WordPress\Shortcodes
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/**
 * Adds shortcodes.
 */
final class Shortcodes {

	/** Registers shortcodes. */
	public function __construct() {
		add_shortcode( 'swg-contribute', array( $this, 'contribute' ) );
		add_shortcode( 'swg-subscribe', array( $this, 'subscribe' ) );
	}

	/**
	 * Shortcode for rendering a Contribute button.
	 *
	 * @param array[string]string $atts Attributes affecting shortcode.
	 */
	public static function contribute( $atts = array() ) {
		$html = '<button class="swg-contribute-button" data-play-offers="';
		if ( isset( $atts['play-offers'] ) ) {
			$html .= $atts['play-offers'];
		}
		$html .= '">Contribute with Google</button>';
		return $html;
	}

	/**
	 * Shortcode for rendering a Subscribe button.
	 *
	 * @param array[string]string $atts Attributes affecting shortcode.
	 */
	public static function subscribe( $atts = array() ) {
		$html = '<button class="swg-button swg-subscribe-button" data-play-offers="';
		if ( isset( $atts['play-offers'] ) ) {
			$html .= $atts['play-offers'];
		}
		$html .= '"></button>';
		return $html;
	}
}
