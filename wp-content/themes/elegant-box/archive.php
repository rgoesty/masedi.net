<?php get_header(); ?>
<?php
	if (function_exists('wp_list_comments')) {
		add_filter('get_comments_number', 'comment_count', 0);
	}
?>

		<div class="post">
			<?php if (have_posts()) : ?>
				<div class="title bottom_space">
					<h2>
<?php
	$post = $posts[0]; // Hack. Set $post so that the_date() works.

	if (is_search()) {
		_e('Search Results', 'elegantbox');
	} else {
		// If this is a category archive
		if (is_category()) {
			printf( __('Archive for the &#8216;%1$s&#8217; Category', 'elegantbox'), single_cat_title('', false) );
		// If this is a tag archive
		} elseif(is_tag()) {
			printf( __('Posts Tagged &#8216;%1$s&#8217;', 'elegantbox'), single_tag_title('', false) );
		// If this is a daily archive
		} elseif (is_day()) {
			printf( __('Archive for %1$s', 'elegantbox'), get_the_time(__('F jS, Y', 'elegantbox')) );
		// If this is a monthly archive
		} elseif (is_month()) {
			printf( __('Archive for %1$s', 'elegantbox'), get_the_time(__('F, Y', 'elegantbox')) );
		// If this is a yearly archive
		} elseif (is_year()) {
			printf( __('Archive for %1$s', 'elegantbox'), get_the_time(__('Y', 'elegantbox')) );
		// If this is an author archive
		} elseif (is_author()) {
			_e('Author Archive', 'elegantbox');
		// If this is a paged archive
		} elseif (isset($_GET['paged']) && !empty($_GET['paged'])) {
			_e('Blog Archives', 'elegantbox');
		}
	}
?>
					</h2>
					<div class="fixed"></div>
				</div><!-- title -->
			<?php endif; ?>

			<div class="content">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
			<div class="boxcaption"><h3><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h3></div>
			<div class="box">
				<div class="excerpt">
					<?php the_excerpt(); ?>
				</div>
				<small><?php printf( __('%1$s at %2$s', 'elegantbox'), get_the_time(__('l, F jS, Y', 'elegantbox')), get_the_time(__('H:i', 'elegantbox')) ); ?> | <?php comments_popup_link(__('0 comments', 'elegantbox'), __('1 comment', 'elegantbox'), __('% comments', 'elegantbox')); ?><?php edit_post_link(__('Edit', 'elegantbox'), ' | ', ''); ?></small>
				<div><small><?php _e('Categories: ', 'elegantbox'); the_category(', ') ?></small></div>
				<div><small><?php _e('Tags: ', 'elegantbox'); the_tags('', ', ', ''); ?></small></div>
			</div>

<?php endwhile; else: ?>
			<div class="messagebox">
				<div class="content small">
					<?php _e('Sorry, no posts matched your criteria.', 'elegantbox'); ?>
				</div>
			</div>

<?php endif; ?>
			</div><!-- content -->
		</div> <!-- post -->

		<div class="fixed"></div>
	</div>

	<?php get_sidebar(); ?>

	<div class="fixed"></div>

		<div id="bottom">
			<div class="postnav">

				<?php if(function_exists('wp_pagenavi')) : ?>
					<?php wp_pagenavi() ?>
				<?php else : ?>
					<span class="alignleft"><?php previous_posts_link(__('&laquo; Newer Entries', 'elegantbox')); ?></span>
					<span class="alignright"><?php next_posts_link(__('Older Entries &raquo;', 'elegantbox')); ?></span>
				<?php endif; ?>

				<div class="fixed"></div>
			</div>

<?php get_footer(); ?>
