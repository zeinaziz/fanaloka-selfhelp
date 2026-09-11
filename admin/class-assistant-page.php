<?php
/**
 * AssistantPage - renders the self-help chat UI + live technical snapshot.
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
				<?php esc_html_e( 'Tanya apa saja soal website ini. Jawabannya dicocokkan dari panduan yang tersedia — tidak ada percakapan yang disimpan, semua hilang begitu halaman ini ditutup.', 'fanaloka-selfhelp' ); ?>
			</p>

			<div class="fsh-layout">
				<div class="fsh-chat-panel">
					<div id="fsh-messages" class="fsh-messages" aria-live="polite"></div>
					<form id="fsh-form" class="fsh-form">
						<input type="text" id="fsh-input" autocomplete="off" placeholder="<?php esc_attr_e( 'Tanya sesuatu, mis. "cara ganti password"...', 'fanaloka-selfhelp' ); ?>" />
						<button type="submit" class="button button-primary"><?php esc_html_e( 'Kirim', 'fanaloka-selfhelp' ); ?></button>
					</form>
				</div>

				<div class="fsh-info-panel">
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
		</div>
		<?php
	}
}
