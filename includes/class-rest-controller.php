<?php
/**
 * REST API - single stateless "ask" endpoint. The question and answer
 * themselves are never persisted — no conversation/history table exists
 * in this plugin by design. The only thing recorded is an anonymous tally
 * (see Stats): which article answered, or that nothing did.
 *
 * @package Fanaloka\SelfHelp
 */

namespace Fanaloka\SelfHelp\REST;

use Fanaloka\SelfHelp\Detector;
use Fanaloka\SelfHelp\Matcher;
use Fanaloka\SelfHelp\Stats;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * RESTController Class.
 */
class RESTController {

	/**
	 * Hook into REST API init.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			'fanaloka-selfhelp/v1',
			'/ask',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_ask' ),
				'permission_callback' => array( $this, 'permission_check' ),
				'args'                => array(
					'question' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			'fanaloka-selfhelp/v1',
			'/snapshot',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_snapshot' ),
				'permission_callback' => array( $this, 'permission_check' ),
			)
		);
	}

	/**
	 * Only logged-in users who can manage the site may use this endpoint —
	 * it is a wp-admin tool, not a public-facing one.
	 *
	 * @return bool
	 */
	public function permission_check(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Handle POST /ask.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function handle_ask( \WP_REST_Request $request ): \WP_REST_Response {
		$question = trim( (string) $request->get_param( 'question' ) );

		if ( '' === $question ) {
			return new \WP_REST_Response(
				array(
					'matched' => false,
					'answer'  => __( 'Pertanyaannya kosong nih, coba ketik dulu ya.', 'fanaloka-selfhelp' ),
				),
				400
			);
		}

		$detector = new Detector();
		$snapshot = $detector->snapshot();

		$matcher = new Matcher();
		$result  = $matcher->find( $question, $snapshot['tags'] );

		if ( $result['matched'] ) {
			Stats::record_match( $result['article']['id'] );
		} else {
			Stats::record_unanswered();
		}

		return new \WP_REST_Response( $this->format_result( $result ), 200 );
	}

	/**
	 * Handle GET /snapshot — technical overview shown immediately in the UI.
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_snapshot(): \WP_REST_Response {
		$detector = new Detector();

		return new \WP_REST_Response( $detector->snapshot(), 200 );
	}

	/**
	 * Shape a Matcher result into the response the JS UI expects.
	 *
	 * @param array<string,mixed> $result Matcher::find() result.
	 * @return array<string,mixed>
	 */
	private function format_result( array $result ): array {
		if ( ! $result['matched'] ) {
			$suggestions = array_map(
				static function ( $article ) {
					return $article['title'];
				},
				$result['alternatives']
			);

			return array(
				'matched'     => false,
				'answer'      => __( 'Belum nemu jawaban yang pas buat pertanyaan itu. Coba pertanyaan lain, atau hubungi tim maintenance kalau butuh bantuan langsung.', 'fanaloka-selfhelp' ),
				'suggestions' => $suggestions,
			);
		}

		$article = $result['article'];

		return array(
			'matched'      => true,
			'title'        => $article['title'],
			'steps'        => $article['steps'],
			'alternatives' => array_map(
				static function ( $a ) {
					return $a['title'];
				},
				$result['alternatives']
			),
		);
	}
}
