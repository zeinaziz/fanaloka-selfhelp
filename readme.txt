=== Fanaloka Self-Help Assistant ===
Contributors: fanaloka
Tags: helpdesk, self-service, maintenance
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Asisten swalayan di wp-admin untuk klien yang tidak berlangganan maintenance — jawab pertanyaan umum lewat pencocokan kata kunci ke basis panduan, tanpa API AI berbayar.

== Description ==

Fanaloka Self-Help Assistant membaca kondisi teknis website (tema, plugin aktif, versi PHP/WordPress, status SSL) secara lokal, lalu mencocokkan pertanyaan bebas dari admin ke daftar panduan (knowledge base) memakai pencocokan kata kunci sederhana — bukan model AI.

Prinsip desain:
* 100% gratis, tanpa API AI berbayar.
* Semua fitur ada di wp-admin saja, tidak menyentuh halaman publik/pengunjung.
* Tidak ada riwayat/log percakapan yang disimpan — setiap sesi tanya-jawab hilang begitu halaman ditutup.
* Data teknis website dibaca langsung di server yang sama, tidak dikirim ke server pihak ketiga mana pun.

== Changelog ==

= 1.0.0 =
* Rilis awal: deteksi teknis lokal + basis panduan + pencocokan kata kunci.
