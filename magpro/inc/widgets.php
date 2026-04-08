<?php
/**
 * MagPro Custom Widgets
 *
 * @package MagPro
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register widget areas.
 */
function magpro_register_sidebars() {
	register_sidebar( array(
		'name'          => __( 'Main Sidebar', 'magpro' ),
		'id'            => 'sidebar-main',
		'description'   => __( 'Widgets in this area appear on posts and pages.', 'magpro' ),
		'before_widget' => '<div id="%1$s" class="magpro-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="magpro-widget-title"><span>',
		'after_title'   => '</span></h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Column 1', 'magpro' ),
		'id'            => 'footer-1',
		'before_widget' => '<div id="%1$s" class="magpro-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="magpro-widget-title"><span>',
		'after_title'   => '</span></h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Column 2', 'magpro' ),
		'id'            => 'footer-2',
		'before_widget' => '<div id="%1$s" class="magpro-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="magpro-widget-title"><span>',
		'after_title'   => '</span></h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer Column 3', 'magpro' ),
		'id'            => 'footer-3',
		'before_widget' => '<div id="%1$s" class="magpro-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="magpro-widget-title"><span>',
		'after_title'   => '</span></h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Header Ad Area', 'magpro' ),
		'id'            => 'header-ad',
		'description'   => __( 'Widget area for header banner ad (728x90).', 'magpro' ),
		'before_widget' => '<div id="%1$s" class="magpro-header-ad %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '',
		'after_title'   => '',
	) );
}
add_action( 'widgets_init', 'magpro_register_sidebars' );

/**
 * Popular Posts Widget
 */
class MagPro_Popular_Posts_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'magpro_popular_posts',
			__( 'MagPro: Popular Posts', 'magpro' ),
			array( 'description' => __( 'Display popular posts by comment count or views.', 'magpro' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title   = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Popular Posts', 'magpro' );
		$count   = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		$orderby = ! empty( $instance['orderby'] ) ? $instance['orderby'] : 'comment_count';

		$query_args = array(
			'post_type'      => 'post',
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'orderby'        => $orderby,
			'order'          => 'DESC',
			'no_found_rows'  => true,
		);

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return;
		}

		echo $args['before_widget'];
		echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title ) ) . $args['after_title'];

		echo '<ul class="magpro-popular-posts">';
		$counter = 1;
		while ( $query->have_posts() ) {
			$query->the_post();
			echo '<li class="magpro-popular-post-item">';
			echo '<span class="magpro-popular-count">' . $counter . '</span>';
			echo '<div class="magpro-popular-content">';
			if ( has_post_thumbnail() ) {
				echo '<a href="' . esc_url( get_permalink() ) . '" class="magpro-popular-thumb">';
				the_post_thumbnail( 'thumbnail', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
				echo '</a>';
			}
			echo '<div class="magpro-popular-text">';
			echo '<a href="' . esc_url( get_permalink() ) . '" class="magpro-popular-title">' . esc_html( get_the_title() ) . '</a>';
			echo '<time class="magpro-popular-date" datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>';
			echo '</div></div></li>';
			$counter++;
		}
		echo '</ul>';

		wp_reset_postdata();
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title   = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Popular Posts', 'magpro' );
		$count   = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		$orderby = ! empty( $instance['orderby'] ) ? $instance['orderby'] : 'comment_count';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'magpro' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Number of posts:', 'magpro' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="15" value="<?php echo esc_attr( $count ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>"><?php esc_html_e( 'Order By:', 'magpro' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>">
				<option value="comment_count" <?php selected( $orderby, 'comment_count' ); ?>><?php esc_html_e( 'Comment Count', 'magpro' ); ?></option>
				<option value="date" <?php selected( $orderby, 'date' ); ?>><?php esc_html_e( 'Most Recent', 'magpro' ); ?></option>
				<option value="rand" <?php selected( $orderby, 'rand' ); ?>><?php esc_html_e( 'Random', 'magpro' ); ?></option>
			</select>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance            = array();
		$instance['title']   = sanitize_text_field( $new_instance['title'] );
		$instance['count']   = absint( $new_instance['count'] );
		$instance['orderby'] = sanitize_text_field( $new_instance['orderby'] );
		return $instance;
	}
}

/**
 * Social Links Widget
 */
class MagPro_Social_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'magpro_social',
			__( 'MagPro: Social Links', 'magpro' ),
			array( 'description' => __( 'Display social media links with icons.', 'magpro' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Follow Us', 'magpro' );

		echo $args['before_widget'];
		echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title ) ) . $args['after_title'];

		$networks = array(
			'facebook_url'  => 'Facebook',
			'twitter_url'   => 'Twitter',
			'instagram_url' => 'Instagram',
			'youtube_url'   => 'YouTube',
			'linkedin_url'  => 'LinkedIn',
			'tiktok_url'    => 'TikTok',
			'telegram_url'  => 'Telegram',
		);

		echo '<div class="magpro-social-links">';
		foreach ( $networks as $key => $label ) {
			$url = magpro_get_option( $key );
			if ( $url ) {
				$slug = strtolower( str_replace( '_url', '', $key ) );
				echo '<a href="' . esc_url( $url ) . '" class="magpro-social-link magpro-social-' . esc_attr( $slug ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $label ) . '">';
				echo '<span class="magpro-social-icon">' . esc_html( $label ) . '</span>';
				echo '</a>';
			}
		}
		echo '</div>';

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Follow Us', 'magpro' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'magpro' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p class="description"><?php esc_html_e( 'Configure social URLs in Customizer > MagPro Settings > Social Media.', 'magpro' ); ?></p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return array( 'title' => sanitize_text_field( $new_instance['title'] ) );
	}
}

/**
 * Category Posts Widget
 */
class MagPro_Category_Posts_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'magpro_category_posts',
			__( 'MagPro: Category Posts', 'magpro' ),
			array( 'description' => __( 'Display recent posts from a specific category.', 'magpro' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title   = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$cat_id  = ! empty( $instance['category'] ) ? absint( $instance['category'] ) : 0;
		$count   = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		$style   = ! empty( $instance['style'] ) ? $instance['style'] : 'list';

		$query = new WP_Query( array(
			'post_type'      => 'post',
			'posts_per_page' => $count,
			'cat'            => $cat_id,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		) );

		if ( ! $query->have_posts() ) {
			return;
		}

		// Use category name if title empty.
		if ( empty( $title ) && $cat_id ) {
			$cat   = get_category( $cat_id );
			$title = $cat ? $cat->name : '';
		}

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title ) ) . $args['after_title'];
		}

		echo '<div class="magpro-cat-posts magpro-cat-posts-' . esc_attr( $style ) . '">';

		if ( 'grid' === $style ) {
			echo '<div class="magpro-cat-grid">';
		}

		while ( $query->have_posts() ) {
			$query->the_post();

			echo '<article class="magpro-cat-post-item">';
			if ( has_post_thumbnail() ) {
				echo '<a href="' . esc_url( get_permalink() ) . '" class="magpro-cat-post-thumb">';
				the_post_thumbnail( 'thumbnail', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
				echo '</a>';
			}
			echo '<div class="magpro-cat-post-content">';
			echo '<h4 class="magpro-cat-post-title"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h4>';
			echo '<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>';
			echo '</div></article>';
		}

		if ( 'grid' === $style ) {
			echo '</div>';
		}

		echo '</div>';
		wp_reset_postdata();
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title    = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$category = ! empty( $instance['category'] ) ? absint( $instance['category'] ) : 0;
		$count    = ! empty( $instance['count'] ) ? absint( $instance['count'] ) : 5;
		$style    = ! empty( $instance['style'] ) ? $instance['style'] : 'list';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (leave empty for category name):', 'magpro' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php esc_html_e( 'Category:', 'magpro' ); ?></label>
			<?php
			wp_dropdown_categories( array(
				'name'            => $this->get_field_name( 'category' ),
				'id'              => $this->get_field_id( 'category' ),
				'selected'        => $category,
				'show_option_all' => __( 'All Categories', 'magpro' ),
				'class'           => 'widefat',
			) );
			?>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Number of posts:', 'magpro' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" max="15" value="<?php echo esc_attr( $count ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>"><?php esc_html_e( 'Display Style:', 'magpro' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>">
				<option value="list" <?php selected( $style, 'list' ); ?>><?php esc_html_e( 'List', 'magpro' ); ?></option>
				<option value="grid" <?php selected( $style, 'grid' ); ?>><?php esc_html_e( 'Grid', 'magpro' ); ?></option>
			</select>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return array(
			'title'    => sanitize_text_field( $new_instance['title'] ),
			'category' => absint( $new_instance['category'] ),
			'count'    => absint( $new_instance['count'] ),
			'style'    => sanitize_text_field( $new_instance['style'] ),
		);
	}
}

/**
 * Newsletter Signup Widget
 */
class MagPro_Newsletter_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'magpro_newsletter',
			__( 'MagPro: Newsletter Signup', 'magpro' ),
			array( 'description' => __( 'Email newsletter signup form.', 'magpro' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title       = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Newsletter', 'magpro' );
		$description = ! empty( $instance['description'] ) ? $instance['description'] : __( 'Subscribe to get the latest news delivered to your inbox.', 'magpro' );
		$action_url  = ! empty( $instance['action_url'] ) ? $instance['action_url'] : '#';

		echo $args['before_widget'];
		echo '<div class="magpro-newsletter">';
		echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title ) ) . $args['after_title'];

		if ( $description ) {
			echo '<p class="magpro-newsletter-desc">' . esc_html( $description ) . '</p>';
		}

		echo '<form action="' . esc_url( $action_url ) . '" method="post" class="magpro-newsletter-form">';
		echo '<label for="magpro-newsletter-email" class="screen-reader-text">' . esc_html__( 'Email Address', 'magpro' ) . '</label>';
		echo '<input type="email" id="magpro-newsletter-email" name="email" placeholder="' . esc_attr__( 'Your email address', 'magpro' ) . '" required>';
		echo '<button type="submit" class="magpro-newsletter-btn">' . esc_html__( 'Subscribe', 'magpro' ) . '</button>';
		echo '</form>';

		echo '</div>';
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title       = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Newsletter', 'magpro' );
		$description = ! empty( $instance['description'] ) ? $instance['description'] : '';
		$action_url  = ! empty( $instance['action_url'] ) ? $instance['action_url'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'magpro' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>"><?php esc_html_e( 'Description:', 'magpro' ); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'description' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>"><?php echo esc_textarea( $description ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'action_url' ) ); ?>"><?php esc_html_e( 'Form Action URL (Mailchimp, etc.):', 'magpro' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'action_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'action_url' ) ); ?>" type="url" value="<?php echo esc_url( $action_url ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		return array(
			'title'       => sanitize_text_field( $new_instance['title'] ),
			'description' => sanitize_text_field( $new_instance['description'] ),
			'action_url'  => esc_url_raw( $new_instance['action_url'] ),
		);
	}
}

/**
 * Register custom widgets.
 */
function magpro_register_widgets() {
	register_widget( 'MagPro_Popular_Posts_Widget' );
	register_widget( 'MagPro_Social_Widget' );
	register_widget( 'MagPro_Category_Posts_Widget' );
	register_widget( 'MagPro_Newsletter_Widget' );
}
add_action( 'widgets_init', 'magpro_register_widgets' );
