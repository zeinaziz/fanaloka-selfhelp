<?php
/**
 * Admin - menu registration and global floating widget loading.
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
	 * Hook slug of the dedicated info page.
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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_page_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_floating_widget_assets' ) );
		add_action( 'admin_footer', array( $this, 'render_floating_widget' ) );
	}

	/**
	 * Register the admin menu page (technical snapshot / info page).
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
	 * Enqueue CSS for the dedicated info page only.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_page_assets( string $hook ): void {
		if ( $hook !== $this->hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'fanaloka-selfhelp-admin',
			FSH_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			FSH_VERSION
		);
	}

	/**
	 * Enqueue the floating chat widget on every wp-admin screen — it is the
	 * one always-available entry point to ask a question, not just on our
	 * own page.
	 *
	 * @return void
	 */
	public function enqueue_floating_widget_assets(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_style(
			'fanaloka-selfhelp-widget',
			FSH_PLUGIN_URL . 'assets/css/floating-widget.css',
			array(),
			FSH_VERSION
		);

		wp_enqueue_script(
			'fanaloka-selfhelp-widget',
			FSH_PLUGIN_URL . 'assets/js/floating-widget.js',
			array(),
			FSH_VERSION,
			true
		);

		wp_localize_script(
			'fanaloka-selfhelp-widget',
			'fshData',
			array(
				'restUrl' => esc_url_raw( rest_url( 'fanaloka-selfhelp/v1' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'i18n'    => array(
					'placeholder'  => __( 'Tanya sesuatu, mis. "cara ganti password"...', 'fanaloka-selfhelp' ),
					'send'         => __( 'Kirim', 'fanaloka-selfhelp' ),
					'thinking'     => __( 'Mencari jawaban...', 'fanaloka-selfhelp' ),
					'alternatives' => __( 'Mungkin maksud kamu:', 'fanaloka-selfhelp' ),
					'title'        => __( 'Asisten Bantuan', 'fanaloka-selfhelp' ),
					'greeting'     => __( 'Halo! Tanya apa saja soal website ini. Nggak ada yang disimpan — begitu ditutup, percakapan ini hilang.', 'fanaloka-selfhelp' ),
				),
			)
		);
	}

	/**
	 * Print the floating widget markup once in the admin footer.
	 *
	 * @return void
	 */
	public function render_floating_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div id="fsh-widget" class="fsh-widget">
			<button type="button" id="fsh-widget-bubble" class="fsh-widget-bubble" aria-label="<?php esc_attr_e( 'Buka Asisten Bantuan', 'fanaloka-selfhelp' ); ?>">
				<span class="dashicons dashicons-format-chat"></span>
			</button>
			<div id="fsh-widget-panel" class="fsh-widget-panel" hidden>
				<div class="fsh-widget-header">
					<span><?php esc_html_e( 'Asisten Bantuan', 'fanaloka-selfhelp' ); ?></span>
					<button type="button" id="fsh-widget-close" aria-label="<?php esc_attr_e( 'Tutup', 'fanaloka-selfhelp' ); ?>">&times;</button>
				</div>
				<div id="fsh-widget-messages" class="fsh-widget-messages" aria-live="polite"></div>
				<form id="fsh-widget-form" class="fsh-widget-form">
					<input type="text" id="fsh-widget-input" autocomplete="off" />
					<button type="submit" aria-label="<?php esc_attr_e( 'Kirim', 'fanaloka-selfhelp' ); ?>">
						<span class="dashicons dashicons-arrow-right-alt2"></span>
					</button>
				</form>
			</div>
		</div>
		<?php
	}
}
