<?php
/**
 * Matcher - plain keyword scoring against the KnowledgeBase. No external
 * API, no AI model: this is intentional (see plugin description).
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Matcher Class.
 */
class Matcher {

	/**
	 * Minimum score for an article to be considered a real match.
	 */
	private const MIN_SCORE = 1.5;

	/**
	 * Find the best-matching article(s) for a free-text question.
	 *
	 * @param string             $question   Raw question text from the user.
	 * @param array<int,string>  $site_tags  Tags detected on this site (from Detector).
	 * @return array{matched:bool,article:?array<string,mixed>,score:float,alternatives:array<int,array<string,mixed>>}
	 */
	public function find( string $question, array $site_tags = array() ): array {
		$normalized = $this->normalize( $question );
		$q_words    = array_filter( explode( ' ', $normalized ) );

		$scored = array();

		foreach ( KnowledgeBase::articles() as $article ) {
			$score = $this->score_article( $article, $normalized, $q_words, $site_tags );

			if ( $score > 0 ) {
				$scored[] = array(
					'article' => $article,
					'score'   => $score,
				);
			}
		}

		usort(
			$scored,
			static function ( $a, $b ) {
				return $b['score'] <=> $a['score'];
			}
		);

		$best = $scored[0] ?? null;

		if ( null === $best || $best['score'] < self::MIN_SCORE ) {
			return array(
				'matched'      => false,
				'article'      => null,
				'score'        => 0.0,
				'alternatives' => array_map(
					static function ( $s ) {
						return $s['article'];
					},
					array_slice( $scored, 0, 3 )
				),
			);
		}

		return array(
			'matched'      => true,
			'article'      => $best['article'],
			'score'        => $best['score'],
			'alternatives' => array_map(
				static function ( $s ) {
					return $s['article'];
				},
				array_slice( $scored, 1, 2 )
			),
		);
	}

	/**
	 * Score a single article against the normalized question.
	 *
	 * @param array<string,mixed> $article    Article definition.
	 * @param string              $normalized Normalized question string.
	 * @param array<int,string>   $q_words    Question split into words.
	 * @param array<int,string>   $site_tags  Detected site tags.
	 * @return float
	 */
	private function score_article( array $article, string $normalized, array $q_words, array $site_tags ): float {
		$score = 0.0;

		foreach ( $article['keywords'] as $keyword ) {
			$keyword_norm = $this->normalize( $keyword );

			if ( '' === $keyword_norm ) {
				continue;
			}

			if ( false !== strpos( $normalized, $keyword_norm ) ) {
				$score += 3.0;
				continue;
			}

			$keyword_words = array_filter( explode( ' ', $keyword_norm ) );
			$matched_words = array_intersect( $keyword_words, $q_words );

			if ( ! empty( $keyword_words ) ) {
				$score += 0.6 * ( count( $matched_words ) / count( $keyword_words ) );
			}
		}

		$title_words   = array_filter( explode( ' ', $this->normalize( $article['title'] ) ) );
		$title_overlap = array_intersect( $title_words, $q_words );
		$score        += 0.3 * count( $title_overlap );

		if ( ! empty( $article['tags'] ) && ! empty( array_intersect( $article['tags'], $site_tags ) ) ) {
			$score += 1.0;
		}

		return $score;
	}

	/**
	 * Lowercase, strip punctuation, collapse whitespace.
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	private function normalize( string $text ): string {
		$text = strtolower( $text );
		$text = preg_replace( '/[^\p{L}\p{N}\s]/u', ' ', $text ) ?? $text;
		$text = preg_replace( '/\s+/', ' ', $text ) ?? $text;

		return trim( $text );
	}
}
