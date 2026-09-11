<?php
/**
 * KnowledgeBase - static library of self-help articles.
 *
 * Each article has: id, title, keywords (matched against the question),
 * tags (empty = applies to every site; otherwise only boosted when the
 * Detector finds a matching plugin/theme), and steps (plain-language guide).
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KnowledgeBase Class.
 */
class KnowledgeBase {

	/**
	 * Get all articles.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function articles(): array {
		return array(
			array(
				'id'       => 'wp-edit-page',
				'title'    => __( 'Cara mengedit isi halaman', 'fanaloka-selfhelp' ),
				'keywords' => array( 'edit halaman', 'ubah halaman', 'ganti teks', 'edit konten', 'ubah konten', 'edit page' ),
				'tags'     => array(),
				'steps'    => array(
					__( 'Masuk ke wp-admin, buka menu Pages / Halaman.', 'fanaloka-selfhelp' ),
					__( 'Cari halaman yang mau diubah, klik Edit.', 'fanaloka-selfhelp' ),
					__( 'Kalau website pakai page builder (Elementor/Bricks/dll), klik tombol edit khusus builder tersebut, bukan editor bawaan WordPress.', 'fanaloka-selfhelp' ),
					__( 'Setelah selesai, klik Update/Publish untuk menyimpan perubahan.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'wp-change-password',
				'title'    => __( 'Cara ganti password admin', 'fanaloka-selfhelp' ),
				'keywords' => array( 'ganti password', 'lupa password', 'reset password', 'ubah kata sandi' ),
				'tags'     => array(),
				'steps'    => array(
					__( 'Masuk ke wp-admin > Users / Pengguna > Profile.', 'fanaloka-selfhelp' ),
					__( 'Scroll ke bagian Account Management, klik "Set New Password".', 'fanaloka-selfhelp' ),
					__( 'Masukkan password baru yang kuat, lalu klik Update Profile.', 'fanaloka-selfhelp' ),
					__( 'Kalau lupa password dan tidak bisa login sama sekali, gunakan link "Lost your password?" di halaman login.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'wp-update-plugin',
				'title'    => __( 'Cara update plugin/tema dengan aman', 'fanaloka-selfhelp' ),
				'keywords' => array( 'update plugin', 'update tema', 'update theme', 'plugin usang', 'versi baru plugin' ),
				'tags'     => array(),
				'steps'    => array(
					__( 'Backup website dulu sebelum update (lihat panduan "cara backup website").', 'fanaloka-selfhelp' ),
					__( 'Buka menu Plugins / Themes, lihat plugin/tema yang ada tanda "update available".', 'fanaloka-selfhelp' ),
					__( 'Update satu per satu (jangan sekaligus banyak), cek website masih normal setelah tiap update.', 'fanaloka-selfhelp' ),
					__( 'Kalau setelah update website error, restore dari backup dan hubungi tim maintenance.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'site-slow',
				'title'    => __( 'Website terasa lambat', 'fanaloka-selfhelp' ),
				'keywords' => array( 'lambat', 'lemot', 'lelet', 'loading lama', 'berat', 'slow' ),
				'tags'     => array(),
				'steps'    => array(
					__( 'Cek apakah ada plugin cache aktif (LiteSpeed Cache, WP Rocket, dll) dan cache-nya sudah pernah di-clear.', 'fanaloka-selfhelp' ),
					__( 'Kompres gambar berukuran besar sebelum upload (idealnya di bawah 300KB per gambar).', 'fanaloka-selfhelp' ),
					__( 'Cek apakah ada plugin yang tidak terpakai tapi masih aktif — nonaktifkan yang tidak perlu.', 'fanaloka-selfhelp' ),
					__( 'Kalau sudah dicoba dan masih lambat, kemungkinan perlu upgrade hosting atau diagnosa lebih lanjut oleh tim teknis.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'wp-backup',
				'title'    => __( 'Cara backup website', 'fanaloka-selfhelp' ),
				'keywords' => array( 'backup', 'cadangan', 'restore', 'pulihkan' ),
				'tags'     => array( 'updraftplus' ),
				'steps'    => array(
					__( 'Kalau UpdraftPlus terpasang: buka Settings > UpdraftPlus Backups, klik "Backup Now".', 'fanaloka-selfhelp' ),
					__( 'Simpan hasil backup ke cloud storage (Google Drive/Dropbox/dll), jangan cuma di server yang sama.', 'fanaloka-selfhelp' ),
					__( 'Untuk restore, buka tab Existing Backups, pilih backup yang mau dipulihkan, klik Restore.', 'fanaloka-selfhelp' ),
					__( 'Kalau tidak ada plugin backup, hubungi tim maintenance — hosting biasanya juga punya backup otomatis di panel hosting.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'ssl-https',
				'title'    => __( 'Website tidak aman / tidak ada gembok HTTPS', 'fanaloka-selfhelp' ),
				'keywords' => array( 'ssl', 'https', 'not secure', 'tidak aman', 'gembok' ),
				'tags'     => array(),
				'steps'    => array(
					__( 'Cek dulu apakah SSL aktif di panel hosting (biasanya gratis via Let\'s Encrypt).', 'fanaloka-selfhelp' ),
					__( 'Pastikan setting WordPress Address dan Site Address di Settings > General pakai https://.', 'fanaloka-selfhelp' ),
					__( 'Kalau sudah https tapi masih ada peringatan "not secure", biasanya ada gambar/script lama yang masih load lewat http:// (mixed content) — perlu di-scan dan diperbaiki satu per satu.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'form-not-sending',
				'title'    => __( 'Form kontak tidak mengirim email', 'fanaloka-selfhelp' ),
				'keywords' => array( 'form tidak jalan', 'form kontak', 'email tidak masuk', 'submit gagal', 'contact form' ),
				'tags'     => array( 'forminator', 'cf7' ),
				'steps'    => array(
					__( 'Cek folder Spam/Junk di email tujuan — ini penyebab paling umum.', 'fanaloka-selfhelp' ),
					__( 'Kalau pakai Forminator: buka menu Forminator > Forms > pilih form > Submissions, cek apakah data submission sebenarnya sudah masuk (berarti formnya jalan, cuma emailnya yang gagal terkirim).', 'fanaloka-selfhelp' ),
					__( 'Cek pengaturan notifikasi email di form tersebut — pastikan alamat email tujuan benar.', 'fanaloka-selfhelp' ),
					__( 'Kalau server tidak bisa kirim email sama sekali, biasanya perlu plugin SMTP (WP Mail SMTP/dll) — hubungi tim teknis untuk setup ini.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'seo-title-meta',
				'title'    => __( 'Cara ubah judul & deskripsi SEO halaman', 'fanaloka-selfhelp' ),
				'keywords' => array( 'seo title', 'meta description', 'judul google', 'tampil di google' ),
				'tags'     => array( 'smartcrawl', 'yoast' ),
				'steps'    => array(
					__( 'Buka halaman/artikel yang ingin diubah lewat menu Pages atau Posts.', 'fanaloka-selfhelp' ),
					__( 'Scroll ke bawah, cari bagian SEO (SmartCrawl/Yoast/dll) di editor halaman tersebut.', 'fanaloka-selfhelp' ),
					__( 'Isi SEO Title (maks ~60 karakter) dan Meta Description (maks ~155 karakter) sesuai kata kunci yang relevan.', 'fanaloka-selfhelp' ),
					__( 'Simpan/Update halaman.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'image-too-big',
				'title'    => __( 'Cara kompres/perkecil ukuran gambar', 'fanaloka-selfhelp' ),
				'keywords' => array( 'kompres gambar', 'ukuran gambar', 'foto besar', 'gambar berat' ),
				'tags'     => array( 'smush' ),
				'steps'    => array(
					__( 'Kompres gambar sebelum upload lewat tool online (TinyPNG/Squoosh) supaya di bawah 300KB.', 'fanaloka-selfhelp' ),
					__( 'Kalau plugin Smush terpasang, buka Media > Smush, jalankan "Bulk Smush" untuk kompres otomatis gambar yang sudah terupload.', 'fanaloka-selfhelp' ),
					__( 'Gunakan format WebP kalau memungkinkan — ukurannya lebih kecil dari JPG/PNG dengan kualitas serupa.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'multilingual',
				'title'    => __( 'Cara menambah bahasa lain di website', 'fanaloka-selfhelp' ),
				'keywords' => array( 'bahasa lain', 'multi bahasa', 'terjemahan', 'translate', 'multilingual' ),
				'tags'     => array( 'polylang' ),
				'steps'    => array(
					__( 'Kalau Polylang terpasang: buka Languages > Languages untuk menambah bahasa baru.', 'fanaloka-selfhelp' ),
					__( 'Setiap halaman/post akan punya versi per bahasa — isi kontennya masing-masing lewat editor biasa.', 'fanaloka-selfhelp' ),
					__( 'Hubungkan versi terjemahan lewat kotak "Languages" di sidebar editor halaman.', 'fanaloka-selfhelp' ),
					__( 'Kalau belum ada plugin multi-bahasa, ini perlu setup awal oleh tim teknis sebelum bisa dipakai sendiri.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'security-scan',
				'title'    => __( 'Cara cek keamanan website', 'fanaloka-selfhelp' ),
				'keywords' => array( 'keamanan', 'diretas', 'kena hack', 'malware', 'security' ),
				'tags'     => array( 'defender', 'wordfence' ),
				'steps'    => array(
					__( 'Kalau ada Defender/Wordfence: buka menu plugin tersebut dan jalankan security scan manual.', 'fanaloka-selfhelp' ),
					__( 'Ganti password admin dan semua user dengan akses admin.', 'fanaloka-selfhelp' ),
					__( 'Update semua plugin/tema/WordPress core ke versi terbaru.', 'fanaloka-selfhelp' ),
					__( 'Kalau menemukan tanda-tanda website benar-benar diretas (halaman aneh, redirect ke situs lain), segera hubungi tim maintenance — jangan ditunda.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'new-user',
				'title'    => __( 'Cara menambah user/admin baru', 'fanaloka-selfhelp' ),
				'keywords' => array( 'tambah user', 'user baru', 'admin baru', 'tambah admin' ),
				'tags'     => array(),
				'steps'    => array(
					__( 'Buka wp-admin > Users / Pengguna > Add New.', 'fanaloka-selfhelp' ),
					__( 'Isi username, email, dan pilih Role sesuai kebutuhan (Administrator hanya untuk yang benar-benar perlu akses penuh).', 'fanaloka-selfhelp' ),
					__( 'Klik "Add New User" — undangan/password akan dikirim ke email yang didaftarkan.', 'fanaloka-selfhelp' ),
				),
			),
			array(
				'id'       => 'clear-cache',
				'title'    => __( 'Cara clear cache website', 'fanaloka-selfhelp' ),
				'keywords' => array( 'clear cache', 'hapus cache', 'cache lama', 'perubahan tidak muncul' ),
				'tags'     => array( 'litespeed', 'cloudflare' ),
				'steps'    => array(
					__( 'Kalau perubahan di website tidak langsung muncul, kemungkinan besar itu cache.', 'fanaloka-selfhelp' ),
					__( 'Kalau LiteSpeed Cache terpasang: buka menu LiteSpeed Cache > Toolbox > Purge All.', 'fanaloka-selfhelp' ),
					__( 'Kalau pakai Cloudflare: purge cache juga perlu dilakukan di dashboard Cloudflare, bukan cuma di WordPress.', 'fanaloka-selfhelp' ),
					__( 'Terakhir, clear cache browser sendiri (Ctrl+Shift+R / Cmd+Shift+R) untuk memastikan bukan cache lokal di komputer.', 'fanaloka-selfhelp' ),
				),
			),
		);
	}
}
