<?php

namespace WAPaaS\MWP\Cache\Types;

use WAPaaS\MWP\Config;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class HTTP implements Type {

	/**
	 * Flush the HTTP cache.
	 *
	 * @since 1.0.0
	 *
	 * @return true
	 *
	 * @SuppressWarnings(PHPMD)
	 */
	public static function flush() {

		$args = [
			'method'    => 'PURGE',
			'blocking'  => false,
			'sslverify' => false, // This is a safe internal request.
			'timeout'   => 1,
			'headers'   => [
				'X-Site-Token' => Config::get( 'site_token' ),
			],
		];

		$url = sprintf(
			'https://wp-cache-%s.wp-%s',
			Config::get( 'site_uid' ),
			Config::get( 'account_uid' )
		);

		wp_remote_request( $url, $args ); // Cannot use `wp_safe_remote_request()`.

		return true;

	}

	/**
	 * Purge specific URLs from the HTTP cache.
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

		$args = [
			'blocking'  => false,
			'sslverify' => false, // This is a safe internal request.
			'timeout'   => 1,
			'headers'   => [
				'X-Site-Token' => Config::get( 'site_token' ),
			],
		];

		foreach ( $urls as &$url ) {

			$url = sprintf( '(%s)$', wp_parse_url( trailingslashit( $url ), PHP_URL_PATH ) );

		}

		$url = sprintf(
			'https://wp-cache-%1$s.wp-%2$s/__mwp2_cache__/delete_regex?url=^https?://wp-web-%1$s\.wp-%2$s%3$s',
			Config::get( 'site_uid' ),
			Config::get( 'account_uid' ),
			implode( '|', $urls )
		);

		wp_remote_get( $url, $args ); // Cannot use `wp_safe_remote_get()`.

		return ! empty( $urls );

	}

}
