<?php
/**
 * General Template for Items lists
 * 
 * Display a list of items links
 * 
 * @since    1.2
 * 
 * @uses    $items array of movies
 * @uses    $style container classes
 * @uses    $description Widget's description
 */
?>
	<div class="<?php echo $style ?>">

<?php if ( '' != $description ) : ?>
		<div class="wpmoly-widget-description"><?php echo $description ?></div>
<?php endif; ?>

		<ul class="wpmoly-widget-list">
<?php foreach ( $items as $item ) : ?>
			<li class="wpmoly-widget-list-item"><a href="<?php echo $item['link'] ?>" title="<?php echo $item['attr_title'] ?>"><?php echo $item['title'] ?></a></li>

<?php endforeach; ?>
		</ul>

	</div>