<?php
/**
 * Comments template.
 *
 * @package MagPro
 */

if ( post_password_required() ) {
	return;
}
?>

<section id="comments" class="magpro-comments" aria-label="<?php esc_attr_e( 'Comments', 'magpro' ); ?>">

	<?php if ( have_comments() ) : ?>
		<h3 class="magpro-comments-title">
			<?php
			$count = get_comments_number();
			printf(
				/* translators: 1: number of comments, 2: post title */
				esc_html( _n( '%1$s Comment on &ldquo;%2$s&rdquo;', '%1$s Comments on &ldquo;%2$s&rdquo;', $count, 'magpro' ) ),
				esc_html( number_format_i18n( $count ) ),
				esc_html( get_the_title() )
			);
			?>
		</h3>

		<ol class="magpro-comment-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 60,
				'callback'    => 'magpro_comment_callback',
			) );
			?>
		</ol>

		<?php
		the_comments_navigation( array(
			'prev_text' => __( '&laquo; Older Comments', 'magpro' ),
			'next_text' => __( 'Newer Comments &raquo;', 'magpro' ),
			'class'     => 'magpro-comments-nav',
		) );
		?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="magpro-no-comments"><?php esc_html_e( 'Comments are closed.', 'magpro' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'class_form'    => 'magpro-comment-form',
		'title_reply'   => __( 'Leave a Comment', 'magpro' ),
		'comment_field' => '<div class="magpro-comment-field"><label for="comment" class="screen-reader-text">' . __( 'Comment', 'magpro' ) . '</label><textarea id="comment" name="comment" cols="45" rows="6" required placeholder="' . esc_attr__( 'Write your comment here...', 'magpro' ) . '"></textarea></div>',
	) );
	?>

</section>

<?php
/**
 * Custom comment callback.
 *
 * @param WP_Comment $comment Comment object.
 * @param array      $args    Arguments.
 * @param int        $depth   Depth.
 */
function magpro_comment_callback( $comment, $args, $depth ) {
	$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
	?>
	<<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class( 'magpro-comment' ); ?>>
		<article class="magpro-comment-body">
			<header class="magpro-comment-header">
				<div class="magpro-comment-avatar">
					<?php echo get_avatar( $comment, $args['avatar_size'], '', '', array( 'loading' => 'lazy' ) ); ?>
				</div>
				<div class="magpro-comment-meta">
					<span class="magpro-comment-author"><?php echo get_comment_author_link(); ?></span>
					<time class="magpro-comment-date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>">
						<?php
						printf(
							/* translators: 1: date, 2: time */
							esc_html__( '%1$s at %2$s', 'magpro' ),
							esc_html( get_comment_date() ),
							esc_html( get_comment_time() )
						);
						?>
					</time>
				</div>
			</header>

			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="magpro-comment-awaiting"><?php esc_html_e( 'Your comment is awaiting moderation.', 'magpro' ); ?></p>
			<?php endif; ?>

			<div class="magpro-comment-content">
				<?php comment_text(); ?>
			</div>

			<div class="magpro-comment-actions">
				<?php
				comment_reply_link( array_merge( $args, array(
					'depth'     => $depth,
					'max_depth' => $args['max_depth'],
					'class'     => 'magpro-comment-reply',
				) ) );
				edit_comment_link( __( 'Edit', 'magpro' ), '<span class="magpro-comment-edit">', '</span>' );
				?>
			</div>
		</article>
	<?php
}
