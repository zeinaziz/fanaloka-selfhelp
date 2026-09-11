<?php
/**
 * Detector - reads the current site's own technical state (no data leaves the site).
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detector Class.
 */
class Detector {

	/**
	 * Plugin/theme signature => KnowledgeBase tag. Matched against active plugin
	 * basenames and the active theme's template slug (case-insensitive substring match).
	 *
	 * @var array<string,string>
	 */
	private const TAG_SIGNATURES = array(
		'bricks'          => 'bricks',
		'elementor'       => 'elementor',
		'wp-defender'     => 'defender',
		'forminator'      => 'forminator',
		'jet-engine'      => 'jetengine',
		'smartcrawl'      => 'smartcrawl',
		'wp-smushit'      => 'smush',
		'updraftplus'     => 'updraftplus',
		'litespeed-cache' => 'litespeed',
		'polylang'        => 'polylang',
		'wpmudev'         => 'wpmudev',
		'cloudflare'      => 'cloudflare',
		'woocommerce'     => 'woocommerce',
		'contact-form-7'  => 'cf7',
		'wordfence'       => 'wordfence',
		'yoast'           => 'yoast',
		'akismet'         => 'akismet',
	);

	/**
	 * Gather a snapshot of the site's technical state.
	 *
	 * @return array<string,mixed>
	 */
	public function snapshot(): array {
		global $wp_version, $wpdb;

		$active_plugins = $this->get_active_plugins();
		$theme          = wp_get_theme();

		return array(
			'wp_version'    => $wp_version,
			'php_version'   => PHP_VERSION,
			'mysql_version' => $wpdb->db_version(),
			'is_ssl'        => is_ssl(),
			'is_multisite'  => is_multisite(),
			'theme'         => array(
				'name'     => $theme->get( 'Name' ),
				'template' => $theme->get_template(),
				'version'  => $theme->get( 'Version' ),
			),
			'plugins'       => $active_plugins,
			'tags'          => $this->detect_tags( $active_plugins, $theme->get_template() ),
			'login_url'     => wp_login_url(),
			'site_url'      => site_url(),
			'php_ok'        => version_compare( PHP_VERSION, '8.0', '>=' ),
		);
	}

	/**
	 * Get active plugins with their display names.
	 *
	 * @return array<int,array{basename:string,name:string}>
	 */
	private function get_active_plugins(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_basenames = (array) get_option( 'active_plugins', array() );
		$result         = array();

		foreach ( $active_basenames as $basename ) {
			$result[] = array(
				'basename' => $basename,
				'name'     => $all_plugins[ $basename ]['Name'] ?? $basename,
			);
		}

		return $result;
	}

	/**
	 * Resolve known-tool tags from active plugins + theme, used to boost
	 * KnowledgeBase article relevance for tools this site actually runs.
	 *
	 * @param array<int,array{basename:string,name:string}> $active_plugins Active plugins.
	 * @param string                                         $theme_template Theme template slug.
	 * @return array<int,string>
	 */
	private function detect_tags( array $active_plugins, string $theme_template ): array {
		$haystack = strtolower( $theme_template );

		foreach ( $active_plugins as $plugin ) {
			$haystack .= ' ' . strtolower( $plugin['basename'] );
		}

		$tags = array();

		foreach ( self::TAG_SIGNATURES as $needle => $tag ) {
			if ( false !== strpos( $haystack, $needle ) ) {
				$tags[] = $tag;
			}
		}

		return array_unique( $tags );
	}
}
