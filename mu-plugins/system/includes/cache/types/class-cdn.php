<?php

namespace WAPaaS\MWP\Cache\Types;

use WAPaaS\MWP\Cache\CDN as CDN_Cache;
use WAPaaS\MWP\API;
use WAPaaS\MWP\Config;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class CDN implements Type {

	/**
	 * Flush the CDN cache.
	 *
	 * @since 1.0.0
	 *
	 * @return true
	 *
	 * @SuppressWarnings(PHPMD)
	 */
	public static function flush() {

		API::delete(
			sprintf( 'cache/%s', Config::get( 'site_uid' ) ),
			[],
			[
				'blocking' => false,
				'timeout'  => 1,
				'headers'  => [
					'X-Site-Token' => Config::get( 'site_token' ),
				],
			]
		);

		return true;

	}

	/**
	 * Purge specific URLs from the CDN cache.
	 *
	 * @since 1.0.0
	 *
	 * @param  array $urls
	 *
	 * @return bool
	 *
	 * @SuppressWarnings(PHPMD)
	 */
	public static function purge( array $urls ) {

		foreach ( $urls as &$url ) {

			$url = wp_parse_url( $url, PHP_URL_PATH );

		}

		$urls = array_filter( $urls, function ( $url ) {

			return in_array( pathinfo( $url, PATHINFO_EXTENSION ), CDN_Cache::FILE_FORMATS, true );

		} );

		if ( ! $urls ) {

			return false;

		}

		API::delete(
			sprintf( 'cache/%s', Config::get( 'site_uid' ) ),
			[
				'paths' => $urls,
			],
			[
				'blocking' => false,
				'timeout'  => 1,
				'headers'  => [
					'X-Site-Token' => Config::get( 'site_token' ),
				],
			]
		);

		return true;

	}

}
