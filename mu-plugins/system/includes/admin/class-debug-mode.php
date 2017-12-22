<?php

namespace WAPaaS\MWP\Admin;

use WAPaaS\MWP\SSO;
use WAPaaS\MWP\System;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Debug_Mode {

	/**
	 * Cookie name.
	 *
	 * @var string
	 */
	const COOKIE = 'mwp-system-debug-mode';

	/**
	 * Default theme slug.
	 *
	 * Note: The `WP_DEFAULT_THEME` core constant is not available early
	 * enough in the load order to be referenced during debug mode.
	 *
	 * @var string
	 */
	const DEFAULT_THEME = 'twentyseventeen';

	/**
	 * Session data.
	 *
	 * @var array
	 */
	private $session = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {

			return;

		}

		if ( $this->is_start() ) {

			$this->start_session();

		}

		if ( $this->is_exit() ) {

			$this->stop_session(); // Reload.

		}

		if ( empty( $_COOKIE[ self::COOKIE ] ) ) {

			return;

		}

		if ( ! $this->is_valid_cookie() ) {

			$this->stop_session(); // Reload.

		}

		if ( $this->is_update() ) {

			$this->update_session(); // Reload.

		}

		add_action( 'muplugins_loaded',   [ $this, 'filter_plugins' ],  PHP_INT_MAX );
		add_action( 'muplugins_loaded',   [ $this, 'filter_theme' ],    PHP_INT_MAX );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], PHP_INT_MAX );
		add_action( 'wp_footer',          [ $this, 'display' ],         PHP_INT_MAX );

		add_filter( 'wp_headers', function ( $headers ) {

			return array_merge( $headers, wp_get_nocache_headers() );

		}, PHP_INT_MAX );

		add_filter( 'body_class', function ( $classes ) {

			$classes[] = 'mwp-system-debug-mode';

			return $classes;

		}, PHP_INT_MAX );

	}

	/**
	 * Whether to start (or restart) a session.
	 *
	 * @return bool
	 */
	private function is_start() {

		$action = filter_input( INPUT_GET, 'mwp-action', FILTER_SANITIZE_STRING );
		$token  = filter_input( INPUT_GET, 'mwp-token', FILTER_SANITIZE_STRING );

		return ( 'debug' === $action && SSO::is_valid_token( $token ) );

	}

	/**
	 * Whether to exit the session.
	 *
	 * @return bool
	 */
	private function is_exit() {

		return ( 'debug-exit' === filter_input( INPUT_GET, 'mwp-action', FILTER_SANITIZE_STRING ) );

	}

	/**
	 * Whether the session cookie is valid.
	 *
	 * @return bool
	 */
	private function is_valid_cookie() {

		$this->session = isset( $_COOKIE[ self::COOKIE ] ) ? json_decode( $_COOKIE[ self::COOKIE ], true ) : null;

		$nonce = isset( $this->session['_nonce'] ) ? $this->session['_nonce'] : null;

		return ( isset( $this->session['plugins'] ) && ! empty( $this->session['themes'] ) && false !== $this->wp_verify_nonce( $nonce, self::COOKIE ) );

	}

	/**
	 * Whether to update the session.
	 *
	 * @return bool
	 */
	private function is_update() {

		$nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );

		return ( $nonce && false !== $this->wp_verify_nonce( $nonce, 'mwp-system-debug-mode-update' ) );

	}

	/**
	 * Start a new session.
	 */
	private function start_session() {

		$themes  = array_fill_keys( array_keys( $this->wp_get_themes() ), false );
		$default = isset( $themes[ self::DEFAULT_THEME ] ) ? self::DEFAULT_THEME : get_stylesheet();

		$this->session = [
			'_nonce'  => $this->wp_create_nonce( self::COOKIE ),
			'plugins' => array_fill_keys( array_keys( $this->get_plugins() ), false ),
			'themes'  => array_merge( $themes, [ $default => true ] ),
		];

		$this->set_cookie( wp_json_encode( $this->session ) );

	}

	/**
	 * Stop a session.
	 */
	private function stop_session() {

		$this->set_cookie( null, 0 );

		$this->reload();

	}

	/**
	 * Update a session.
	 */
	private function update_session() {

		$active_plugins = (array) filter_input( INPUT_POST, 'mwp-system-debug-mode-plugins', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		foreach ( $this->session['plugins'] as $plugin => &$active ) {

			$active = in_array( $plugin, $active_plugins, true );

		}

		$active_theme = filter_input( INPUT_POST, 'mwp-system-debug-mode-theme', FILTER_SANITIZE_STRING );

		foreach ( $this->session['themes'] as $theme => &$active ) {

			$active = ( $theme === $active_theme );

		}

		$this->set_cookie( wp_json_encode( $this->session ) );

		$this->reload();

	}

	/**
	 * Reload the current view.
	 */
	private function reload() {

		if ( ! function_exists( 'wp_safe_redirect' ) ) {

			require_once ABSPATH . WPINC . '/pluggable.php';

		}

		wp_safe_redirect( remove_query_arg( [ 'mwp-action', 'mwp-token' ] ) );

		exit;

	}

	/**
	 * Set a session cookie.
	 *
	 * @param string $value
	 * @param int    $expire (optional)
	 */
	private function set_cookie( $value, $expire = DAY_IN_SECONDS ) {

		wp_cookie_constants();

		setcookie( self::COOKIE, $value, time() + $expire, SITECOOKIEPATH, COOKIE_DOMAIN, is_ssl() );

		$_COOKIE[ self::COOKIE ] = $value; // Set in current request.

	}

	/**
	 * Get plugins helper.
	 *
	 * @return array
	 */
	private function get_plugins() {

		if ( ! function_exists( 'get_plugins' ) ) {

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

		}

		return get_plugins();

	}

	/**
	 * Get themes helper.
	 *
	 * @return array
	 */
	private function wp_get_themes() {

		// Global not available early in the load order, so we will define it manually when needed.
		if ( empty( $GLOBALS['wp_theme_directories'] ) ) {

			$GLOBALS['wp_theme_directories'][] = WP_CONTENT_DIR . get_theme_roots(); // WPCS: override ok.

		}

		return wp_get_themes();

	}

	/**
	 * Create a nonce helper.
	 *
	 * @return string
	 */
	private function wp_create_nonce( ...$args ) {

		if ( ! function_exists( 'wp_create_nonce' ) ) {

			require_once ABSPATH . WPINC . '/pluggable.php';

		}

		wp_cookie_constants(); // Nonces require `SECURE_AUTH_COOKIE` to be defined.

		return wp_create_nonce( ...$args );

	}

	/**
	 * Verify a nonce helper.
	 *
	 * @return int|false
	 */
	private function wp_verify_nonce( ...$args ) {

		if ( ! function_exists( 'wp_verify_nonce' ) ) {

			require_once ABSPATH . WPINC . '/pluggable.php';

		}

		wp_cookie_constants(); // Nonces require `SECURE_AUTH_COOKIE` to be defined.

		return wp_verify_nonce( ...$args );

	}

	/**
	* Filter the active plugins.
	*
	* @action muplugins_loaded
	*/
	public function filter_plugins() {

		add_filter( 'option_active_plugins', function( $option_value ) {

			return array_keys( array_filter( $this->session['plugins'] ) );

		}, PHP_INT_MAX );

	}

	/**
	 * Filter the active theme.
	 *
	 * @action muplugins_loaded
	 */
	public function filter_theme() {

		$theme = wp_get_theme( array_search( true, $this->session['themes'], true ) );

		$template = function () use ( $theme ) {

			return $theme->template;

		};

		$stylesheet = function () use ( $theme ) {

			return $theme->stylesheet;

		};

		add_filter( 'template',        $template, PHP_INT_MAX );
		add_filter( 'option_template', $template, PHP_INT_MAX );

		$this->session['options']['stylesheet'] = get_stylesheet();

		add_filter( 'stylesheet',        $stylesheet, PHP_INT_MAX );
		add_filter( 'option_stylesheet', $stylesheet, PHP_INT_MAX );

	}

	/**
	 * Enqueue the session control panel scripts and styles.
	 *
	 * @action wp_enqueue_scripts
	 */
	public function enqueue_scripts() {

		$suffix = SCRIPT_DEBUG ? '' : '.min';
		$rtl    = ! is_rtl() ? '' : '-rtl';

		wp_enqueue_style( 'mwp-system-debug-mode', System::ASSETS_URL . "css/debug-mode{$rtl}{$suffix}.css", [], System::VERSION );

		wp_enqueue_script( 'jquery' );

	}

	/**
	 * Display the session control panel.
	 *
	 * @action wp_footer
	 */
	public function display() {

		$plugins = $this->get_plugins();
		$themes  = $this->wp_get_themes();

		?>
		<script type="text/javascript">
			jQuery( document ).on( "keyup", function( e ) {
				if ( 27 == e.keyCode ) {
					jQuery( "body" ).toggleClass( "mwp-system-debug-mode" );
				}
			} );
		</script>
		<div id="mwp-system-debug-mode" class="cleanslate">
			<div>
				<form method="POST">
					<h4><?php esc_html_e( 'Plugins' ); // core i18n. ?></h4>
					<ul>
					<?php foreach ( $this->session['plugins'] as $plugin => $active ) : ?>
						<li>
							<input type="checkbox" id="plugin-<?php echo esc_attr( crc32( $plugin ) ); ?>" name="mwp-system-debug-mode-plugins[]" value="<?php echo esc_attr( $plugin ); ?>" <?php checked( $active ); ?>>
							<label for="plugin-<?php echo esc_attr( crc32( $plugin ) ); ?>"><?php echo esc_html( $plugins[ $plugin ]['Name'] ); ?></label>
						</li>
					<?php endforeach; ?>
					</ul>
					<h4><?php esc_html_e( 'Themes' ); // core i18n. ?></h4>
					<ul>
					<?php foreach ( $this->session['themes'] as $theme => $active ) : ?>
						<li>
							<input type="radio" id="theme-<?php echo esc_attr( crc32( $theme ) ); ?>" name="mwp-system-debug-mode-theme" value="<?php echo esc_attr( $theme ); ?>" <?php checked( $active ); ?>>
							<?php if ( $theme === $this->session['options']['stylesheet'] ) : ?><strong><?php endif; ?>
								<label for="theme-<?php echo esc_attr( crc32( $theme ) ); ?>"><?php echo esc_html( $themes[ $theme ]->get( 'Name' ) ); ?></label>
							<?php if ( $theme === $this->session['options']['stylesheet'] ) : ?></strong><?php endif; ?>
						</li>
					<?php endforeach; ?>
					</ul>
					<?php wp_nonce_field( 'mwp-system-debug-mode-update' ); ?>
					<input type="submit" value="<?php esc_attr_e( 'Update', 'mwp-system-plugin' ); ?>">
					<p><a href="<?php echo esc_url( add_query_arg( 'mwp-action', 'debug-exit' ) ); ?>" id="mwp-system-debug-mode-exit"><?php esc_html_e( 'Exit', 'mwp-system-plugin' ); ?></a></p>
				</form>
			</div>
		</div>
		<?php

	}

}
