<?php

namespace wpscholar\WordPress;

/**
 * Class PostExpiration
 *
 * @author Micah Wood
 * @package wpscholar\WordPress
 */
class PostExpiration {

	/**
	 * Setup hooks
	 */
	public static function initialize() {

		// Setup callback for cron
		add_action( __CLASS__, [ __CLASS__, 'expirePosts' ] );

		// Schedule cron task
		if ( ! wp_next_scheduled( __CLASS__ ) ) {
			wp_schedule_event( time(), 'hourly', __CLASS__ );
		}

		// Add UI for setting post expiration
		foreach ( get_post_types_by_support( 'expiration' ) as $post_type ) {
			add_action( 'add_meta_boxes_' . $post_type, [ __CLASS__, '_addMetaBox' ] );
			add_action( 'save_post_' . $post_type, [ __CLASS__, '_onSave' ] );
		}
	}

	/**
	 * Add meta box
	 *
	 * @internal
	 */
	public static function _addMetaBox() {
		add_meta_box(
			'wp-post-expiration',
			esc_html__( 'Post Expiration', 'wp-post-expiration' ),
			[ __CLASS__, '_renderMetaBox' ],
			null,
			'side'
		);
	}

	/**
	 * Render meta box
	 *
	 * @internal
	 */
	public static function _renderMetaBox() {
		?>
        <label class="wp-post-expiration">
            <span class="wp-post-expiration__label-text">
                <?php esc_html_e( 'Expiration Date/Time', 'wp-post-expiration' ); ?>
            </span>
            <div class="wp-post-expiration__field">
                <input class="wp-post-expiration__input js-flatpickr widefat"
                       name="_post_expiration"
                       type="text"
                       value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_post_expiration', true ) ); ?>"
                />
                <span class="wp-post-expiration__reset js-flatpickr-reset">×</span>
            </div>
        </label>
		<?php
		self::_loadAssets();
		wp_nonce_field( 'set_post_expiration', 'wp_post_expiration_nonce' );
	}

	/**
	 * Load scripts and stylesheets
	 */
	public static function _loadAssets() {

		if ( ! defined( 'WPSCHOLAR_POST_EXPIRATION_URL' ) ) {
			define( 'WPSCHOLAR_POST_EXPIRATION_URL', plugins_url( '', __FILE__ ) );
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'wp-post-expiration', WPSCHOLAR_POST_EXPIRATION_URL . "/assets/css/wp-post-expiration{$suffix}.css" );
		wp_enqueue_script( 'wp-post-expiration', WPSCHOLAR_POST_EXPIRATION_URL . "/assets/js/wp-post-expiration{$suffix}.js" );
	}

	/**
	 * On post save
	 *
	 * @internal
	 *
	 * @param int $post_id
	 */
	public static function _onSave( $post_id ) {
		if ( wp_verify_nonce( filter_input( INPUT_POST, 'wp_post_expiration_nonce' ), 'set_post_expiration' ) ) {
			$expiration = filter_input( INPUT_POST, '_post_expiration' );
			if ( empty( $expiration ) ) {
				self::removeExpiration( $post_id );
			} else {
				self::setExpiration( $post_id, $expiration );
			}
		}
	}

	/**
	 * Set expiration for a specific post.
	 *
	 * @param int $post_id
	 * @param string|int $expiration In Unix timestamp format
	 */
	public static function setExpiration( $post_id, $expiration ) {
		update_post_meta( $post_id, '_post_expiration', absint( $expiration ) );
	}

	/**
	 * Remove expiration for a specific post.
	 *
	 * @param int $post_id
	 */
	public static function removeExpiration( $post_id ) {
		delete_post_meta( $post_id, '_post_expiration' );
	}

	/**
	 * Expire a specific post.
	 *
	 * @param int $post_id
	 */
	public static function expirePost( $post_id ) {
		wp_update_post( [
			'ID'          => $post_id,
			'post_status' => apply_filters( __CLASS__ . '_status', 'trash', $post_id ),
		] );
	}

	/**
	 * Expire multiple posts.
	 */
	public static function expirePosts() {

		// Only expire post types that support expiration!
		foreach ( get_post_types_by_support( 'expiration' ) as $post_type ) {

			// This query trashes posts. Don't cache this query!
			$query = new \WP_Query( [
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'meta_query'     => [
					[
						'key'     => '_post_expiration',
						'value'   => time(),
						'compare' => '<=',
					]
				],
				'posts_per_page' => 100,
				'fields'         => 'ids',
			] );

			if ( $query->have_posts() ) {
				foreach ( $query->posts as $post_id ) {
					self::expirePost( $post_id );
				}
			}

		}
	}

}
