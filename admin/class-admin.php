<?php
/**
 * Admin - menu registration and asset loading.
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class.
 */
class Admin {

	/**
	 * Hook slug of our page, used to scope asset loading.
	 *
	 * @var string
	 */
	private string $hook_suffix = '';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register the admin menu page.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		$this->hook_suffix = add_menu_page(
			__( 'Asisten Bantuan', 'fanaloka-selfhelp' ),
			__( 'Asisten Bantuan', 'fanaloka-selfhelp' ),
			'manage_options',
			'fanaloka-selfhelp',
			array( new AssistantPage(), 'render' ),
			'dashicons-format-chat',
			58
		);
	}

	/**
	 * Enqueue CSS/JS only on our own admin page.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( string $hook ): void {
		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'fanaloka-selfhelp-admin',
			FSH_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			FSH_VERSION
		);

		wp_enqueue_script(
			'fanaloka-selfhelp-admin',
			FSH_PLUGIN_URL . 'assets/js/admin.js',
			array(),
			FSH_VERSION,
			true
		);

		wp_localize_script(
			'fanaloka-selfhelp-admin',
			'fshData',
			array(
				'restUrl' => esc_url_raw( rest_url( 'fanaloka-selfhelp/v1' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'i18n'    => array(
					'placeholder' => __( 'Tanya sesuatu, mis. "cara ganti password"...', 'fanaloka-selfhelp' ),
					'send'        => __( 'Kirim', 'fanaloka-selfhelp' ),
					'thinking'    => __( 'Mencari jawaban...', 'fanaloka-selfhelp' ),
					'alternatives' => __( 'Mungkin maksud kamu:', 'fanaloka-selfhelp' ),
				),
			)
		);
	}
}
