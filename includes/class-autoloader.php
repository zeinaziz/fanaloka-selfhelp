<?php
/**
 * Autoloader - explicit class map for plugin classes.
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoloader Class.
 */
class Autoloader {

	/**
	 * Fully-qualified class name => file path relative to plugin root.
	 *
	 * @var array<string,string>
	 */
	private const CLASS_MAP = array(
		'Fanaloka\\SelfHelp\\Detector'                  => 'includes/class-detector.php',
		'Fanaloka\\SelfHelp\\KnowledgeBase'              => 'includes/class-knowledge-base.php',
		'Fanaloka\\SelfHelp\\SiteIndexer'                => 'includes/class-site-indexer.php',
		'Fanaloka\\SelfHelp\\Stats'                      => 'includes/class-stats.php',
		'Fanaloka\\SelfHelp\\Matcher'                    => 'includes/class-matcher.php',
		'Fanaloka\\SelfHelp\\REST\\RESTController'       => 'includes/class-rest-controller.php',
		'Fanaloka\\SelfHelp\\Admin\\Admin'               => 'admin/class-admin.php',
		'Fanaloka\\SelfHelp\\Admin\\AssistantPage'       => 'admin/class-assistant-page.php',
		'Fanaloka\\SelfHelp\\Admin\\KBPage'              => 'admin/class-kb-page.php',
	);

	/**
	 * Register the autoloader.
	 *
	 * @return void
	 */
	public static function register(): void {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload a class from the class map.
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @return void
	 */
	public static function autoload( string $class_name ): void {
		if ( ! isset( self::CLASS_MAP[ $class_name ] ) ) {
			return;
		}

		$path = FSH_PLUGIN_DIR . self::CLASS_MAP[ $class_name ];

		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}
}
