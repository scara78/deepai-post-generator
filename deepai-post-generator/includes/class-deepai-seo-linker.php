<?php
/**
 * SEO internal and external linking system.
 *
 * @package DeepAI_Post_Generator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DeepAI_SEO_Linker {

	/**
	 * Maximum internal links to insert.
	 *
	 * @var int
	 */
	private $max_internal_links;

	/**
	 * Maximum external links to insert.
	 *
	 * @var int
	 */
	private $max_external_links;

	/**
	 * Rel attribute for external links.
	 *
	 * @var string
	 */
	private $external_link_rel;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$settings = get_option( 'deepai_pg_settings', array() );

		$this->max_internal_links = isset( $settings['max_internal_links'] ) ? absint( $settings['max_internal_links'] ) : 3;
		$this->max_external_links = isset( $settings['max_external_links'] ) ? absint( $settings['max_external_links'] ) : 2;
		$this->external_link_rel  = isset( $settings['external_link_rel'] ) ? sanitize_text_field( $settings['external_link_rel'] ) : 'nofollow';
	}

	/**
	 * Process content to add internal links.
	 *
	 * Scans existing posts for relevant topics and inserts contextual links.
	 *
	 * @param string $content     The generated post content (HTML).
	 * @param string $topic       The post topic.
	 * @param array  $keywords    Keywords used for generation.
	 * @param int    $exclude_id  Post ID to exclude from linking.
	 * @return string Modified content with internal links.
	 */
	public function add_internal_links( $content, $topic, $keywords = array(), $exclude_id = 0 ) {
		if ( $this->max_internal_links <= 0 ) {
			return $content;
		}

		$related_posts = $this->find_related_posts( $topic, $keywords, $exclude_id );

		if ( empty( $related_posts ) ) {
			return $content;
		}

		$links_added = 0;
		foreach ( $related_posts as $post ) {
			if ( $links_added >= $this->max_internal_links ) {
				break;
			}

			$link_text = $post->post_title;
			$link_url  = get_permalink( $post->ID );

			// Find a suitable anchor location in the content.
			$content_modified = $this->insert_contextual_link( $content, $link_text, $link_url, false );

			if ( $content_modified !== $content ) {
				$content = $content_modified;
				$links_added++;
			}
		}

		return $content;
	}

	/**
	 * Add external links to the content via AI.
	 *
	 * This adds placeholders that the AI will fill with relevant external links.
	 *
	 * @param string $content The generated post content.
	 * @return string Content with external link suggestions.
	 */
	public function get_external_link_prompt_addition() {
		if ( $this->max_external_links <= 0 ) {
			return '';
		}

		return sprintf(
			/* translators: %1$d: max number of external links, %2$s: rel attribute */
			__(
				"\n\nAdditionally, include up to %1\$d relevant external links to authoritative sources. "
				. 'Use rel="%2$s" for external links. Make the anchor text natural and contextual.',
				'deepai-post-generator'
			),
			$this->max_external_links,
			$this->external_link_rel
		);
	}

	/**
	 * Find related posts based on topic and keywords.
	 *
	 * @param string $topic      The post topic.
	 * @param array  $keywords   Keywords.
	 * @param int    $exclude_id Post ID to exclude.
	 * @return WP_Post[] Array of related posts.
	 */
	private function find_related_posts( $topic, $keywords = array(), $exclude_id = 0 ) {
		$search_terms = array_merge( array( $topic ), $keywords );
		$search_query = implode( ' ', array_slice( $search_terms, 0, 5 ) );

		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $this->max_internal_links * 2,
			's'              => $search_query,
			'orderby'        => 'relevance',
			'order'          => 'DESC',
		);

		if ( $exclude_id > 0 ) {
			$args['post__not_in'] = array( $exclude_id );
		}

		$query = new WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Insert a contextual link into HTML content.
	 *
	 * Finds a relevant paragraph and appends a link naturally.
	 *
	 * @param string $content   The HTML content.
	 * @param string $text      The link anchor text.
	 * @param string $url       The link URL.
	 * @param bool   $external  Whether this is an external link.
	 * @return string Modified content.
	 */
	private function insert_contextual_link( $content, $text, $url, $external = false ) {
		// Find words from the title to match in content.
		$title_words = array_filter(
			explode( ' ', strtolower( $text ) ),
			function( $word ) {
				return strlen( $word ) > 3;
			}
		);

		if ( empty( $title_words ) ) {
			return $content;
		}

		// Build link HTML.
		$rel_attr = $external ? ' rel="' . esc_attr( $this->external_link_rel ) . '" target="_blank"' : '';
		$link_html = sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $url ),
			$rel_attr,
			esc_html( $text )
		);

		// Try to find a paragraph that contains a related word.
		$paragraphs = preg_split( '/(<\/p>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
		$best_match = -1;
		$best_score = 0;

		for ( $i = 0; $i < count( $paragraphs ); $i += 2 ) {
			if ( empty( trim( $paragraphs[ $i ] ) ) ) {
				continue;
			}

			// Skip paragraphs that already have links added by us.
			if ( substr_count( strtolower( $paragraphs[ $i ] ), '<a href' ) > 2 ) {
				continue;
			}

			$score = 0;
			$lower = strtolower( $paragraphs[ $i ] );
			foreach ( $title_words as $word ) {
				if ( strpos( $lower, $word ) !== false ) {
					$score++;
				}
			}

			if ( $score > $best_score ) {
				$best_score = $score;
				$best_match = $i;
			}
		}

		// If we found a matching paragraph, append the link before </p>.
		if ( $best_match >= 0 && $best_score > 0 ) {
			$sentence = sprintf(
				/* translators: %s: internal link HTML */
				' ' . __( 'Learn more about %s.', 'deepai-post-generator' ),
				$link_html
			);
			$paragraphs[ $best_match ] .= $sentence;
			return implode( '', $paragraphs );
		}

		return $content;
	}

	/**
	 * Analyze existing posts for potential internal linking opportunities.
	 *
	 * @param int $post_id The post to analyze.
	 * @return array Array of linking suggestions.
	 */
	public function analyze_post_links( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		// Get keywords from post title and content.
		$title_words = array_filter(
			explode( ' ', strtolower( $post->post_title ) ),
			function( $word ) {
				return strlen( $word ) > 3;
			}
		);

		$suggestions = array();
		$related     = $this->find_related_posts( $post->post_title, $title_words, $post_id );

		foreach ( $related as $related_post ) {
			$suggestions[] = array(
				'post_id'    => $related_post->ID,
				'title'      => $related_post->post_title,
				'url'        => get_permalink( $related_post->ID ),
				'relevance'  => $this->calculate_relevance( $post->post_title, $related_post->post_title ),
			);
		}

		// Sort by relevance descending.
		usort( $suggestions, function( $a, $b ) {
			return $b['relevance'] - $a['relevance'];
		});

		return array_slice( $suggestions, 0, 10 );
	}

	/**
	 * Calculate basic relevance score between two titles.
	 *
	 * @param string $title1 First title.
	 * @param string $title2 Second title.
	 * @return int Relevance score.
	 */
	private function calculate_relevance( $title1, $title2 ) {
		$words1 = array_filter( explode( ' ', strtolower( $title1 ) ), function( $w ) {
			return strlen( $w ) > 3;
		});
		$words2 = array_filter( explode( ' ', strtolower( $title2 ) ), function( $w ) {
			return strlen( $w ) > 3;
		});

		return count( array_intersect( $words1, $words2 ) );
	}
}
