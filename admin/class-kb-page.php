<?php
/**
 * KBPage - admin CRUD screen for the knowledge base articles the chat
 * widget matches against (title, keywords, tags, steps).
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp\Admin;

use Fanaloka\SelfHelp\KnowledgeBase;
use Fanaloka\SelfHelp\SiteIndexer;
use Fanaloka\SelfHelp\Stats;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KBPage Class.
 */
class KBPage {

	private const NONCE_ACTION = 'fsh_kb_save';

	/**
	 * Handle form submission / delete requests. Must run on the page's
	 * `load-{hook}` action, before any HTML is sent, so redirects work.
	 *
	 * @return void
	 */
	public function handle_actions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['fsh_kb_submit'] ) ) {
			check_admin_referer( self::NONCE_ACTION, 'fsh_kb_nonce' );
			$this->save_from_post();
			return;
		}

		if ( isset( $_GET['action'], $_GET['id'] ) && 'delete' === $_GET['action'] ) {
			check_admin_referer( 'fsh_kb_delete_' . sanitize_text_field( wp_unslash( $_GET['id'] ) ) );
			KnowledgeBase::delete( sanitize_text_field( wp_unslash( $_GET['id'] ) ) );
			wp_safe_redirect( remove_query_arg( array( 'action', 'id', '_wpnonce' ) ) );
			exit;
		}

		if ( isset( $_GET['action'] ) && 'index' === $_GET['action'] ) {
			check_admin_referer( 'fsh_kb_index' );
			$result = SiteIndexer::run();
			wp_safe_redirect(
				add_query_arg(
					array(
						'fsh_indexed' => 1,
						'added'       => $result['added'],
						'updated'     => $result['updated'],
						'removed'     => $result['removed'],
					),
					remove_query_arg( array( 'action', '_wpnonce' ) )
				)
			);
			exit;
		}

		if ( isset( $_GET['action'] ) && 'reset_stats' === $_GET['action'] ) {
			check_admin_referer( 'fsh_kb_reset_stats' );
			Stats::reset();
			wp_safe_redirect( remove_query_arg( array( 'action', '_wpnonce' ) ) );
			exit;
		}
	}

	/**
	 * Validate + save the posted article, then redirect back to the list.
	 *
	 * @return void
	 */
	private function save_from_post(): void {
		$title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';

		if ( '' === $title ) {
			wp_safe_redirect( add_query_arg( array( 'view' => 'edit', 'fsh_error' => 'empty_title' ) ) );
			exit;
		}

		$keywords = $this->lines_to_array( $_POST['keywords'] ?? '' );
		$steps    = $this->lines_to_array( $_POST['steps'] ?? '' );
		$tags     = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['tags'] ?? '' ) ) ) ) );
		$tags     = array_map( 'strtolower', $tags );

		if ( empty( $steps ) ) {
			wp_safe_redirect( add_query_arg( array( 'view' => 'edit', 'fsh_error' => 'empty_steps' ) ) );
			exit;
		}

		$existing_id = isset( $_POST['article_id'] ) ? sanitize_text_field( wp_unslash( $_POST['article_id'] ) ) : '';
		$existing    = '' !== $existing_id ? KnowledgeBase::get( $existing_id ) : null;
		$id          = $existing ? $existing_id : $this->unique_id( $title );

		KnowledgeBase::upsert(
			array(
				'id'       => $id,
				'title'    => $title,
				'keywords' => $keywords,
				'tags'     => array_values( $tags ),
				'steps'    => $steps,
				'source'   => $existing['source'] ?? 'manual',
				// Any admin-saved edit to a builtin/auto article protects it
				// from SiteIndexer's next run — see SiteIndexer::run().
				'edited'   => (bool) $existing,
			)
		);

		wp_safe_redirect( remove_query_arg( array( 'view', 'id', 'fsh_error' ) ) );
		exit;
	}

	/**
	 * Turn a slug-safe title into a unique article id.
	 *
	 * @param string $title Article title.
	 * @return string
	 */
	private function unique_id( string $title ): string {
		$base = sanitize_title( $title );
		$id   = $base;
		$i    = 2;

		while ( null !== KnowledgeBase::get( $id ) ) {
			$id = $base . '-' . $i;
			++$i;
		}

		return $id;
	}

	/**
	 * Split a textarea value into a trimmed, non-empty array of lines.
	 *
	 * @param string $raw Raw textarea value.
	 * @return array<int,string>
	 */
	private function lines_to_array( string $raw ): array {
		$raw   = sanitize_textarea_field( wp_unslash( $raw ) );
		$lines = preg_split( '/\r\n|\r|\n/', $raw );

		return array_values( array_filter( array_map( 'trim', $lines ) ) );
	}

	/**
	 * Render the page: list view or add/edit form.
	 *
	 * @return void
	 */
	public function render(): void {
		$view = isset( $_GET['view'] ) ? sanitize_text_field( wp_unslash( $_GET['view'] ) ) : 'list';

		echo '<div class="wrap fsh-wrap">';
		echo '<h1>' . esc_html__( 'Kelola Panduan', 'fanaloka-selfhelp' ) . '</h1>';

		if ( 'edit' === $view ) {
			$this->render_form();
		} else {
			$this->render_list();
		}

		echo '</div>';
	}

	/**
	 * Render the article list table.
	 *
	 * @return void
	 */
	private function render_list(): void {
		$articles  = KnowledgeBase::articles();
		$add_url   = add_query_arg( array( 'view' => 'edit' ) );
		$index_url = wp_nonce_url( add_query_arg( array( 'action' => 'index' ) ), 'fsh_kb_index' );

		$source_labels = array(
			'builtin' => __( 'Bawaan', 'fanaloka-selfhelp' ),
			'auto'    => __( 'Hasil Index', 'fanaloka-selfhelp' ),
			'manual'  => __( 'Manual', 'fanaloka-selfhelp' ),
		);
		?>
		<p class="fsh-subtitle">
			<?php esc_html_e( 'Artikel di sini dipakai Asisten Bantuan untuk menjawab pertanyaan. Ubah kapan saja tanpa perlu edit kode.', 'fanaloka-selfhelp' ); ?>
		</p>

		<?php if ( isset( $_GET['fsh_indexed'] ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: 1: added, 2: updated, 3: removed */
						esc_html__( 'Index selesai — %1$d panduan ditambahkan, %2$d diperbarui, %3$d dihapus (karena plugin-nya tidak aktif di website ini).', 'fanaloka-selfhelp' ),
						(int) ( $_GET['added'] ?? 0 ),
						(int) ( $_GET['updated'] ?? 0 ),
						(int) ( $_GET['removed'] ?? 0 )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<p>
			<a href="<?php echo esc_url( $add_url ); ?>" class="button button-primary"><?php esc_html_e( '+ Tambah Panduan', 'fanaloka-selfhelp' ); ?></a>
			<a href="<?php echo esc_url( $index_url ); ?>" class="button fsh-index-link" data-confirm="<?php esc_attr_e( 'Index ulang berdasarkan plugin & konten yang terpasang di website ini? Panduan bawaan untuk plugin yang tidak aktif akan dihapus (kecuali yang sudah kamu edit).', 'fanaloka-selfhelp' ); ?>">
				<?php esc_html_e( '⟳ Index Website Ini', 'fanaloka-selfhelp' ); ?>
			</a>
		</p>
		<p class="description" style="max-width:640px;">
			<?php esc_html_e( 'Index memindai plugin aktif dan tipe konten khusus di website ini, lalu menyesuaikan daftar panduan: menghapus panduan plugin yang tidak terpasang dan menambahkan panduan untuk konten yang memang ada. Panduan yang sudah kamu edit sendiri tidak akan diutak-atik.', 'fanaloka-selfhelp' ); ?>
		</p>

		<?php $this->render_stats_summary(); ?>

		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Judul', 'fanaloka-selfhelp' ); ?></th>
					<th><?php esc_html_e( 'Kata Kunci', 'fanaloka-selfhelp' ); ?></th>
					<th><?php esc_html_e( 'Tag', 'fanaloka-selfhelp' ); ?></th>
					<th style="width:110px;"><?php esc_html_e( 'Sumber', 'fanaloka-selfhelp' ); ?></th>
					<th style="width:80px;"><?php esc_html_e( 'Ditanya', 'fanaloka-selfhelp' ); ?></th>
					<th style="width:140px;"><?php esc_html_e( 'Aksi', 'fanaloka-selfhelp' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $articles ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'Belum ada panduan.', 'fanaloka-selfhelp' ); ?></td></tr>
				<?php endif; ?>
				<?php foreach ( $articles as $article ) : ?>
					<?php
					$edit_url   = add_query_arg( array( 'view' => 'edit', 'id' => $article['id'] ) );
					$delete_url = wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $article['id'] ) ), 'fsh_kb_delete_' . $article['id'] );
					$source     = $source_labels[ $article['source'] ] ?? $article['source'];
					?>
					<tr>
						<td><strong><?php echo esc_html( $article['title'] ); ?></strong></td>
						<td><?php echo esc_html( implode( ', ', array_slice( $article['keywords'], 0, 4 ) ) ); ?><?php echo count( $article['keywords'] ) > 4 ? esc_html__( ', ...', 'fanaloka-selfhelp' ) : ''; ?></td>
						<td><?php echo $article['tags'] ? esc_html( implode( ', ', $article['tags'] ) ) : '—'; ?></td>
						<td>
							<?php echo esc_html( $source ); ?>
							<?php if ( ! empty( $article['edited'] ) && 'manual' !== $article['source'] ) : ?>
								<span class="fsh-badge fsh-badge-warn" title="<?php esc_attr_e( 'Sudah kamu edit, jadi dilindungi dari Index Website', 'fanaloka-selfhelp' ); ?>">✎</span>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( (string) Stats::matched_count( $article['id'] ) ); ?>×</td>
						<td>
							<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'fanaloka-selfhelp' ); ?></a>
							|
							<a href="<?php echo esc_url( $delete_url ); ?>" class="fsh-delete-link" data-confirm="<?php esc_attr_e( 'Hapus panduan ini?', 'fanaloka-selfhelp' ); ?>"><?php esc_html_e( 'Hapus', 'fanaloka-selfhelp' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<script>
		document.querySelectorAll( '.fsh-delete-link, .fsh-index-link, .fsh-reset-stats-link' ).forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				if ( ! window.confirm( link.getAttribute( 'data-confirm' ) ) ) {
					e.preventDefault();
				}
			} );
		} );
		</script>
		<?php
	}

	/**
	 * Render the anonymous usage summary: how many questions went
	 * unanswered, plus a link to clear the counters.
	 *
	 * @return void
	 */
	private function render_stats_summary(): void {
		$unanswered = Stats::unanswered_count();
		$reset_url  = wp_nonce_url( add_query_arg( array( 'action' => 'reset_stats' ) ), 'fsh_kb_reset_stats' );
		?>
		<div class="fsh-stats-summary">
			<p>
				<?php
				printf(
					/* translators: %d: number of unanswered questions */
					esc_html__( 'Pertanyaan yang belum terjawab sejauh ini: %d. Angka ini cuma hitungan, isi pertanyaannya sendiri tidak pernah disimpan.', 'fanaloka-selfhelp' ),
					(int) $unanswered
				);
				?>
				<a href="<?php echo esc_url( $reset_url ); ?>" class="fsh-reset-stats-link" data-confirm="<?php esc_attr_e( 'Reset semua statistik (jumlah ditanya + belum terjawab)?', 'fanaloka-selfhelp' ); ?>">
					<?php esc_html_e( 'Reset statistik', 'fanaloka-selfhelp' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the add/edit form.
	 *
	 * @return void
	 */
	private function render_form(): void {
		$id      = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
		$article = '' !== $id ? KnowledgeBase::get( $id ) : null;
		$error   = isset( $_GET['fsh_error'] ) ? sanitize_text_field( wp_unslash( $_GET['fsh_error'] ) ) : '';

		$title    = $article['title'] ?? '';
		$keywords = $article ? implode( "\n", $article['keywords'] ) : '';
		$tags     = $article ? implode( ', ', $article['tags'] ) : '';
		$steps    = $article ? implode( "\n", $article['steps'] ) : '';

		$back_url = remove_query_arg( array( 'view', 'id', 'fsh_error' ) );
		?>
		<p><a href="<?php echo esc_url( $back_url ); ?>">&larr; <?php esc_html_e( 'Kembali ke daftar', 'fanaloka-selfhelp' ); ?></a></p>

		<?php if ( 'empty_title' === $error ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Judul tidak boleh kosong.', 'fanaloka-selfhelp' ); ?></p></div>
		<?php elseif ( 'empty_steps' === $error ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Isi minimal satu langkah.', 'fanaloka-selfhelp' ); ?></p></div>
		<?php endif; ?>

		<form method="post" class="fsh-kb-form">
			<?php wp_nonce_field( self::NONCE_ACTION, 'fsh_kb_nonce' ); ?>
			<input type="hidden" name="article_id" value="<?php echo esc_attr( $id ); ?>" />

			<table class="form-table">
				<tr>
					<th><label for="fsh-title"><?php esc_html_e( 'Judul', 'fanaloka-selfhelp' ); ?></label></th>
					<td><input type="text" id="fsh-title" name="title" class="regular-text" value="<?php echo esc_attr( $title ); ?>" required /></td>
				</tr>
				<tr>
					<th><label for="fsh-keywords"><?php esc_html_e( 'Kata Kunci', 'fanaloka-selfhelp' ); ?></label></th>
					<td>
						<textarea id="fsh-keywords" name="keywords" rows="4" class="large-text" placeholder="<?php esc_attr_e( "satu per baris, mis.\nganti password\nlupa password", 'fanaloka-selfhelp' ); ?>"><?php echo esc_textarea( $keywords ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Satu frasa per baris — dicocokkan ke pertanyaan yang diketik admin/klien.', 'fanaloka-selfhelp' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="fsh-tags"><?php esc_html_e( 'Tag Plugin (opsional)', 'fanaloka-selfhelp' ); ?></label></th>
					<td>
						<input type="text" id="fsh-tags" name="tags" class="regular-text" value="<?php echo esc_attr( $tags ); ?>" placeholder="mis. updraftplus, litespeed" />
						<p class="description"><?php esc_html_e( 'Pisahkan dengan koma. Kalau plugin ini terdeteksi aktif di website, panduan ini diprioritaskan. Kosongkan kalau berlaku untuk semua website.', 'fanaloka-selfhelp' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="fsh-steps"><?php esc_html_e( 'Langkah-langkah', 'fanaloka-selfhelp' ); ?></label></th>
					<td>
						<textarea id="fsh-steps" name="steps" rows="6" class="large-text" placeholder="<?php esc_attr_e( "satu langkah per baris", 'fanaloka-selfhelp' ); ?>"><?php echo esc_textarea( $steps ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Satu langkah per baris, akan ditampilkan sebagai daftar bernomor.', 'fanaloka-selfhelp' ); ?></p>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" name="fsh_kb_submit" class="button button-primary"><?php esc_html_e( 'Simpan', 'fanaloka-selfhelp' ); ?></button>
			</p>
		</form>
		<?php
	}
}
