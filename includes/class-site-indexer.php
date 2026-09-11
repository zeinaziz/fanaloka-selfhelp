<?php
/**
 * SiteIndexer - scans this specific website (active plugins, custom post
 * types) and fits the KnowledgeBase to what's actually installed here:
 * drops starter guides for plugins that aren't active, and generates
 * guides for content types unique to this site. Never touches an article
 * once an admin has saved edits to it (article['edited'] === true).
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SiteIndexer Class.
 */
class SiteIndexer {

	/**
	 * Run the scan and reconcile the knowledge base with it.
	 *
	 * @return array{added:int,updated:int,removed:int}
	 */
	public static function run(): array {
		$snapshot = ( new Detector() )->snapshot();

		$removed = self::drop_irrelevant_builtins( $snapshot['tags'] );
		$result  = self::sync_auto_articles( self::custom_post_type_articles() );

		return array(
			'added'   => $result['added'],
			'updated' => $result['updated'],
			'removed' => $removed + $result['removed'],
		);
	}

	/**
	 * Remove untouched builtin articles that are tagged to a specific
	 * plugin this site doesn't have active.
	 *
	 * @param array<int,string> $site_tags Tags detected on this site.
	 * @return int Number removed.
	 */
	private static function drop_irrelevant_builtins( array $site_tags ): int {
		$removed = 0;

		foreach ( KnowledgeBase::articles() as $article ) {
			$is_builtin       = 'builtin' === $article['source'];
			$is_plugin_tagged = ! empty( $article['tags'] );
			$is_relevant      = ! empty( array_intersect( $article['tags'], $site_tags ) );

			if ( $is_builtin && $is_plugin_tagged && ! $is_relevant && ! $article['edited'] ) {
				KnowledgeBase::delete( $article['id'] );
				++$removed;
			}
		}

		return $removed;
	}

	/**
	 * Upsert a fresh batch of auto-generated articles, skip any the admin
	 * has already edited, and remove auto articles no longer produced by
	 * the scan (e.g. a custom post type that was deactivated).
	 *
	 * @param array<int,array<string,mixed>> $fresh_articles Freshly scanned articles.
	 * @return array{added:int,updated:int,removed:int}
	 */
	private static function sync_auto_articles( array $fresh_articles ): array {
		$added   = 0;
		$updated = 0;
		$removed = 0;

		$existing_auto_ids = array();
		foreach ( KnowledgeBase::articles() as $article ) {
			if ( 'auto' === $article['source'] ) {
				$existing_auto_ids[] = $article['id'];
			}
		}

		$fresh_ids = array();

		foreach ( $fresh_articles as $article ) {
			$fresh_ids[] = $article['id'];
			$existing    = KnowledgeBase::get( $article['id'] );

			if ( $existing && ! empty( $existing['edited'] ) ) {
				continue; // Admin customized this one — leave it alone.
			}

			KnowledgeBase::upsert( $article );
			$existing ? ++$updated : ++$added;
		}

		foreach ( array_diff( $existing_auto_ids, $fresh_ids ) as $stale_id ) {
			$stale = KnowledgeBase::get( $stale_id );

			if ( $stale && empty( $stale['edited'] ) ) {
				KnowledgeBase::delete( $stale_id );
				++$removed;
			}
		}

		return compact( 'added', 'updated', 'removed' );
	}

	/**
	 * Build one article per custom post type that actually has an admin
	 * list screen on this site (skips internal/hidden post types).
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private static function custom_post_type_articles(): array {
		$post_types = get_post_types( array( 'show_ui' => true, '_builtin' => false ), 'objects' );
		$articles   = array();

		foreach ( $post_types as $post_type ) {
			if ( false === $post_type->show_in_menu ) {
				continue;
			}

			$label     = $post_type->labels->name ?? $post_type->label;
			$list_url  = admin_url( 'edit.php?post_type=' . $post_type->name );
			$add_url   = admin_url( 'post-new.php?post_type=' . $post_type->name );

			$articles[] = array(
				'id'       => 'auto-cpt-' . $post_type->name,
				'title'    => sprintf(
					/* translators: %s: post type label, e.g. "Portfolio" */
					__( 'Cara kelola %s', 'fanaloka-selfhelp' ),
					$label
				),
				'keywords' => array(
					strtolower( $label ),
					sprintf( 'kelola %s', strtolower( $label ) ),
					sprintf( 'edit %s', strtolower( $label ) ),
					sprintf( 'tambah %s', strtolower( $label ) ),
				),
				'tags'     => array(),
				'steps'    => array(
					sprintf(
						/* translators: 1: post type label, 2: admin list URL */
						__( 'Buka menu "%1$s" di sidebar wp-admin (%2$s).', 'fanaloka-selfhelp' ),
						$label,
						$list_url
					),
					sprintf(
						/* translators: %s: admin "add new" URL */
						__( 'Untuk menambah baru, klik "Add New" atau buka langsung: %s', 'fanaloka-selfhelp' ),
						$add_url
					),
					__( 'Isi datanya, lalu klik Publish/Update untuk menyimpan.', 'fanaloka-selfhelp' ),
				),
				'source'   => 'auto',
				'edited'   => false,
			);
		}

		return $articles;
	}
}
