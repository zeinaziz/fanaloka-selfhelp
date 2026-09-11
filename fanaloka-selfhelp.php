<?php
/**
 * Plugin Name:       Fanaloka Self-Help Assistant
 * Plugin URI:        https://github.com/zeinaziz/fanaloka-selfhelp
 * Description:       Asisten swalayan di wp-admin: deteksi kondisi teknis website dan jawab pertanyaan umum lewat pencocokan kata kunci ke basis panduan — tanpa API AI berbayar.
 * Version:            1.2.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Fanaloka
 * Author URI:        https://fanaloka.co
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fanaloka-selfhelp
 * Domain Path:       /languages
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FSH_VERSION', '1.2.0' );
define( 'FSH_PLUGIN_FILE', __FILE__ );
define( 'FSH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FSH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FSH_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once FSH_PLUGIN_DIR . 'includes/class-autoloader.php';
Autoloader::register();

register_activation_hook( __FILE__, __NAMESPACE__ . '\\fsh_activate' );

/**
 * Seed the editable knowledge base with the built-in starter articles on
 * first activation, so "Kelola Panduan" opens with something to edit
 * instead of an empty list.
 *
 * @return void
 */
function fsh_activate(): void {
	KnowledgeBase::maybe_seed_defaults();
}

/**
 * Boot the plugin.
 *
 * @return void
 */
function fsh_init(): void {
	load_plugin_textdomain( 'fanaloka-selfhelp', false, dirname( FSH_PLUGIN_BASENAME ) . '/languages' );

	if ( is_admin() ) {
		( new \Fanaloka\SelfHelp\Admin\Admin() )->init();
	}

	( new REST\RESTController() )->init();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\fsh_init' );

/**
 * Covers sites where the plugin was already active before this option
 * existed (activation hook only fires on a fresh activate). Deferred to
 * 'init' — calling KnowledgeBase this early on 'plugins_loaded' triggers
 * WP 6.7's "translation loaded too early" notice (defaults() uses __()).
 *
 * @return void
 */
function fsh_maybe_seed_kb(): void {
	KnowledgeBase::maybe_seed_defaults();
}
add_action( 'init', __NAMESPACE__ . '\\fsh_maybe_seed_kb' );
