<?php

namespace wpscholar;

/**
 * Class PostExpiration
 *
 * @author Micah Wood
 * @package wpscholar
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
		if ( ! defined( 'WPSCHOLAR_POST_EXPIRATION_URL' ) ) {
			define( 'WPSCHOLAR_POST_EXPIRATION_URL', plugins_url( '', __FILE__ ) );
		}
		?>
        <label>
            <span>Expiration Date / Time</span>
            <input class="js-filthypillow widefat" type="search">
            <input type="hidden"
                   name="_post_expiration"
                   value="<?php echo esc_attr( get_post_meta( get_the_ID(), '_post_expiration', true ) ); ?>"/>
        </label>
        <script>
            jQuery(document).ready(function ($) {

                var dateFormat = 'MMMM Do, YYYY \\at h:mm A';

                $('.js-filthypillow').each(function () {
                    var input = $(this);
                    var hiddenInput = $(this).find('~ input[type="hidden"]');
                    input.filthypillow({
                        initialDateTime: function (m) {
                            if (hiddenInput.val().length) {
                                var datetime = moment(hiddenInput.val(), 'X');
                                if (datetime.isValid()) {
                                    m = datetime.seconds(0);
                                    input.val(m.format(dateFormat));
                                }
                            } else {
                                m = m.seconds(0);
                            }
                            return m;
                        }
                    });
                    input.on({
                        'search': function () {
                            input.find('~ input[type="hidden"]').val('');
                        },
                        'focus': function () {
                            input.filthypillow('show');
                            input.next().show();
                        },
                        'fp:save': function (e, m) {
                            // Update visible date field
                            input.val(m.format(dateFormat));
                            // Hide date/time selector
                            input.next().hide();
                            // Store Unix timestamp in hidden field
                            hiddenInput.val(m.seconds(0).format('X'));
                        }.bind(this)
                    });
                });
            });
        </script>
		<?php
		wp_enqueue_script( 'moment', WPSCHOLAR_POST_EXPIRATION_URL . '/assets/moment.js' );
		wp_enqueue_script( 'filthypillow', WPSCHOLAR_POST_EXPIRATION_URL . '/assets/jquery.filthypillow.min.js', [
			'jquery',
			'moment'
		] );
		wp_enqueue_style( 'filthypillow', WPSCHOLAR_POST_EXPIRATION_URL . '/assets/jquery.filthypillow.css' );
		wp_nonce_field( 'set_post_expiration', 'post_expiration_nonce' );
	}

	/**
	 * On post save
	 *
	 * @internal
	 *
	 * @param int $post_id
	 */
	public static function _onSave( $post_id ) {
		if ( wp_verify_nonce( filter_input( INPUT_POST, 'post_expiration_nonce' ), 'set_post_expiration' ) ) {
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