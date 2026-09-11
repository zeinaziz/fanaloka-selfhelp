<?php
/**
 * Stats - anonymous, aggregate-only usage counters: how many times each
 * article answered a question, and how many questions went unanswered.
 * Never stores question text or anything else about the conversation —
 * just tallies, consistent with the plugin's stateless-chat design.
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stats Class.
 */
class Stats {

	private const OPTION_KEY = 'fsh_kb_stats';

	/**
	 * Record that an article answered a question.
	 *
	 * @param string $article_id Article id.
	 * @return void
	 */
	public static function record_match( string $article_id ): void {
		$stats                            = self::all();
		$stats['matched'][ $article_id ] = ( $stats['matched'][ $article_id ] ?? 0 ) + 1;

		update_option( self::OPTION_KEY, $stats, false );
	}

	/**
	 * Record that no article matched the question.
	 *
	 * @return void
	 */
	public static function record_unanswered(): void {
		$stats                = self::all();
		$stats['unanswered'] = ( $stats['unanswered'] ?? 0 ) + 1;

		update_option( self::OPTION_KEY, $stats, false );
	}

	/**
	 * How many times a specific article has answered a question.
	 *
	 * @param string $article_id Article id.
	 * @return int
	 */
	public static function matched_count( string $article_id ): int {
		$stats = self::all();

		return (int) ( $stats['matched'][ $article_id ] ?? 0 );
	}

	/**
	 * How many questions went unanswered in total.
	 *
	 * @return int
	 */
	public static function unanswered_count(): int {
		return (int) self::all()['unanswered'];
	}

	/**
	 * Clear all counters.
	 *
	 * @return void
	 */
	public static function reset(): void {
		delete_option( self::OPTION_KEY );
	}

	/**
	 * Raw stored stats, normalized.
	 *
	 * @return array{matched:array<string,int>,unanswered:int}
	 */
	private static function all(): array {
		$stats               = get_option( self::OPTION_KEY, array() );
		$stats['matched']    = is_array( $stats['matched'] ?? null ) ? $stats['matched'] : array();
		$stats['unanswered'] = (int) ( $stats['unanswered'] ?? 0 );

		return $stats;
	}
}
