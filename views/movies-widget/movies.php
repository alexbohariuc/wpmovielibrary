<?php
/**
 * Movies-by-Rating Default Template
 * 
 * Display a list of movies links with thumbnails
 * 
 * @since    1.2
 * 
 * @uses    $style current Widget style
 * @uses    $description Widget's description
 * @uses    $items array of movies
 * @uses    $show_poster which way to show posters
 * @uses    $show_title which way to show titles
 * @uses    $show_rating which way to show ratings
 */
?>

	<div class="<?php echo $style ?>">

<?php if ( '' != $description ) : ?>
		<div class="wpmoly-widget-description"><?php echo $description ?></div>
<?php endif; ?>

<?php foreach ( $items as $item ) : ?>
		<a href="<?php echo $item['link'] ?>" title="<?php echo __( 'Read more about', 'wpmovielibrary' ) . $item['title'] ?>">
			<figure id="movie-<?php the_ID(); ?>" class="wpmoly-movies-widget wpmoly-movie">
<?php if ( 'no' == $show_poster || 'before' == $show_title ) : ?>
				<div id="movie-<?php the_ID(); ?>-title" class="wpmoly-movies-widget movie-title"><?php echo $item['title'] ?></div>
<?php endif; ?>

<?php if ( 'small' == $show_poster || 'normal' == $show_poster ) : ?>
				<?php echo $item['thumbnail']; ?>
<?php endif; ?>

<?php if ( 'no' != $show_rating ) : ?>
				<div class="movie_rating_display <?php echo $item['rating_str'] . ' ' . $show_rating ?>"><?php if ( 'starsntext' == $show_rating ) echo '<small>' . $item['rating'] . '/5</small>' ?></div>
<?php endif; ?>

<?php if ( 'after' == $show_title ) : ?>
				<div id="movie-<?php the_ID(); ?>-title" class="wpmoly-movies-widget movie-title"><?php echo $item['title'] ?></div>
<?php endif; ?>

			</figure>
		</a>

<?php endforeach; ?>
	</div>
