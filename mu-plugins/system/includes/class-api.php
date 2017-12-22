<?php

namespace WAPaaS\MWP;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class API {

	/**
	 * Make an API call.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $method
	 * @param  string $endpoint
	 * @param  array  $args     (optional)
	 *
	 * @return array|WP_Error
	 */
	private static function call( $method, $endpoint, array $args = [] ) {

		if ( ! $endpoint ) {

			return new WP_Error( 'mwp_system_api_endpoint_missing' );

		}

		$api_url = Config::get( 'api_url' );

		if ( ! $api_url ) {

			return new WP_Error( 'mwp_system_api_url_missing' );

		}

		$api_url = trailingslashit( $api_url ) . $endpoint;

		$defaults = [
			'method'  => strtoupper( $method ),
			'headers' => [
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			],
		];

		$args = wp_parse_args( $args, $defaults );

		add_filter( 'https_ssl_verify', '__return_false' );

		$response = wp_remote_request( esc_url_raw( $api_url ), $args );

		add_filter( 'https_ssl_verify', '__return_true' );

		$code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $code ) {

			$error = new WP_Error( 'mwp_system_api_bad_status' );
			$error->add_data( $code );

			return $error;

		}

		return (array) json_decode( wp_remote_retrieve_body( $response ), true );

	}

	/**
	 * Make a GET request.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $endpoint
	 * @param  array  $query_args (optional)
	 * @param  array  $http_args  (optional)
	 *
	 * @return array|WP_Error
	 */
	public static function get( $endpoint, array $query_args = [], array $http_args = [] ) {

		$endpoint = add_query_arg( $query_args, $endpoint );

		return self::call( 'GET', $endpoint, $http_args );

	}

	/**
	 * Make an API request.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $method
	 * @param  array  $args   (optional)
	 *
	 * @return array|WP_Error
	 */
	public static function __callStatic( $method, array $args = [] ) {

		$defaults = [
			'endpoint'  => 'POST',
			'body_args' => [],
			'http_args' => [],
		];

		$args = wp_parse_args( $args, $defaults );

		list( $endpoint, $body_args, $http_args ) = $args;

		$body_args = isset( $http_args['body'] ) ? array_merge_recursive( $http_args['body'], $body_args ) : $body_args;

		$http_args['body'] = ( $body_args ) ? wp_json_encode( $body_args ) : null;

		return self::call( $method, $endpoint, $http_args );

	}

}
