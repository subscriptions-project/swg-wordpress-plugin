<?php
/**
 * Class SubscribeWithGoogle\WordPress\MeterReader
 *
 * @package   SubscribeWithGoogle\WordPress
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */

namespace SubscribeWithGoogle\WordPress;

/**
 * Determines how many free pageloads a user has left
 */
final class MeterReader {

	public static function addReadRecordForUrlToUser($url, $user_id){
		$urlRecord = get_user_meta( $user_id, Plugin::key( 'free_urls_accessed' ), true );
		if ( ! $urlRecord ){
			$urlRecord = [];
		}

		if (! self::urlReadRecordExistsForUser($url, $user_id)){
			$urlRecord[] = $url;
		}

		update_user_meta($user_id, Plugin::key( 'free_urls_accessed' ), $urlRecord);

	}

	public static function getReadRecordCountForUser($user_id){
		$urlRecord = get_user_meta( $user_id, Plugin::key( 'free_urls_accessed' ), true );
		return $urlRecord ? count($urlRecord) : 0;
	}
	
	public static function urlReadRecordExistsForUser($url, $user_id){
		$urlRecord = get_user_meta( $user_id, Plugin::key( 'free_urls_accessed' ), true );
		return  in_array($url, $urlRecord);
	}

}