<?php

/**
 * Advanced Ads Model
 */
class Advanced_Ads_Model {

	/**
	 * Cache group for WP Object Cache
	 *
	 * @var string
	 */
	const OBJECT_CACHE_GROUP = 'advanced-ads';

	/**
	 * Default time-to-live for WP Object Cache
	 *
	 * @var string
	 */
	const OBJECT_CACHE_TTL = 720; // 12 Minutes

	/**
	 * WordPress database object.
	 *
	 * @var wpdb
	 */
	protected $db;

	/**
	 * General ad conditions.
	 *
	 * @var array
	 */
	protected $ad_conditions;

	/**
	 * Placements
	 *
	 * @var array
	 */
	protected $ad_placements;

	/**
	 * Advanced_Ads_Model constructor.
	 *
	 * @param wpdb $wpdb WordPress database access.
	 */
	public function __construct( wpdb $wpdb ) {
		$this->db = $wpdb;
	}

	/**
	 * Load ad conditions.
	 *
	 * @return array
	 */
	public function get_ad_conditions() {
		if ( ! isset( self::$ad_conditions ) ) {
			$this->ad_conditions = include ADVADS_BASE_PATH . 'includes/array_ad_conditions.php';
		}

		return $this->ad_conditions;
	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 * @return   array|false    The blog ids, false if no matches.
	 */
	public function get_blog_ids() {
		// get an array of blog ids.
		$sql = "SELECT blog_id FROM $this->db->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";

		return $this->db->get_col( $sql );
	}

	/**
	 * Load all ads based on WP_Query conditions
	 *
	 * @since 1.1.0
	 * @param array $args WP_Query arguments that are more specific that default.
	 * @return array $ads array with post objects.
	 */
	public function get_ads( $args = [] ) {
		// add default WP_Query arguments.
		$args['post_type']      = Advanced_Ads::POST_TYPE_SLUG;
		$args['posts_per_page'] = -1;
		if ( empty( $args['post_status'] ) ) {
			$args['post_status'] = [ 'publish', 'future' ]; }
		$ads = new WP_Query( $args );

		return $ads->posts;
	}

	/**
	 * Load all ad groups
	 *
	 * @since 1.1.0
	 * @param array $args array with options.
	 * @return array array with ad groups
	 * @link http://codex.wordpress.org/Function_Reference/get_terms
	 */
	public function get_ad_groups( $args = [] ) {
		$args['hide_empty'] = isset( $args['hide_empty'] ) ? $args['hide_empty'] : false; // display groups without any ads.

		return get_terms( Advanced_Ads::AD_GROUP_TAXONOMY, $args );
	}

	/**
	 * Get the array with ad placements
	 *
	 * @since 1.1.0
	 * @return array $ad_placements
	 */
	public function get_ad_placements_array() {

		if ( ! isset( $this->ad_placements ) ) {
			$this->ad_placements = get_option( 'advads-ads-placements', [] );

			// load default array if not saved yet.
			if ( ! is_array( $this->ad_placements ) ) {
				$this->ad_placements = [];
			}

			$this->ad_placements = apply_filters( 'advanced-ads-get-ad-placements-array', $this->ad_placements );
		}

		return $this->ad_placements;
	}

	/**
	 * Reset placement array.
	 */
	public function reset_placement_array() {
		$this->ad_placements = null;
	}

	/**
	 * Update the array with ad placements
	 *
	 * @param array $ad_placements array with placements.
	 */
	public function update_ad_placements_array( $ad_placements ) {
		$ad_placements = Advanced_Ads_Placements::sort( $ad_placements, 'type' );
		update_option( 'advads-ads-placements', $ad_placements );
		$this->ad_placements = $ad_placements;
	}



}
