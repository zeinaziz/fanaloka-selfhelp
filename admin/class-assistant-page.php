<?php
/**
 * AssistantPage - technical snapshot page. The chat itself lives in the
 * floating widget (available on every wp-admin screen, see Admin class),
 * so this page only shows what the plugin has detected about the site.
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp\Admin;

use Fanaloka\SelfHelp\Detector;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AssistantPage Class.
 */
class AssistantPage {

	/**
	 * Render the page.
	 *
	 * @return void
	 */
	public function render(): void {
		$snapshot = ( new Detector() )->snapshot();
		?>
		<div class="wrap fsh-wrap">
			<h1><?php esc_html_e( 'Asisten Bantuan', 'fanaloka-selfhelp' ); ?></h1>
			<p class="fsh-subtitle">
				<?php esc_html_e( 'Klik gelembung chat di pojok kanan bawah halaman mana pun di wp-admin untuk bertanya. Jawabannya dicocokkan dari panduan yang tersedia — tidak ada percakapan yang disimpan, semua hilang begitu jendela chat ditutup.', 'fanaloka-selfhelp' ); ?>
			</p>

			<div class="fsh-info-panel fsh-info-panel-standalone">
				<h2><?php esc_html_e( 'Info Website', 'fanaloka-selfhelp' ); ?></h2>
				<ul class="fsh-info-list">
					<li><strong><?php esc_html_e( 'Tema', 'fanaloka-selfhelp' ); ?>:</strong> <?php echo esc_html( $snapshot['theme']['name'] ); ?></li>
					<li><strong><?php esc_html_e( 'WordPress', 'fanaloka-selfhelp' ); ?>:</strong> <?php echo esc_html( $snapshot['wp_version'] ); ?></li>
					<li>
						<strong>PHP:</strong> <?php echo esc_html( $snapshot['php_version'] ); ?>
						<?php if ( ! $snapshot['php_ok'] ) : ?>
							<span class="fsh-badge fsh-badge-warn"><?php esc_html_e( 'sebaiknya diupgrade', 'fanaloka-selfhelp' ); ?></span>
						<?php endif; ?>
					</li>
					<li>
						<strong>SSL:</strong>
						<?php echo $snapshot['is_ssl'] ? esc_html__( 'Aktif', 'fanaloka-selfhelp' ) : '<span class="fsh-badge fsh-badge-warn">' . esc_html__( 'Tidak aktif', 'fanaloka-selfhelp' ) . '</span>'; ?>
					</li>
					<li><strong><?php esc_html_e( 'Jumlah plugin aktif', 'fanaloka-selfhelp' ); ?>:</strong> <?php echo esc_html( (string) count( $snapshot['plugins'] ) ); ?></li>
				</ul>
				<p class="fsh-info-note">
					<?php esc_html_e( 'Info ini dibaca langsung dari website — tidak dikirim ke mana pun.', 'fanaloka-selfhelp' ); ?>
				</p>
			</div>
		</div>
		<?php
	}
}
