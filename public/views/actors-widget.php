<?php
$title = $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title;
$list  = ( 1 == $instance['list'] ? true : false );
$css = ( 1 == $instance['css'] ? true : false );
$count = ( 1 == $instance['count'] ? true : false );

$actors = get_terms( array( 'actor' ) );
?>
		<?php echo $title; ?>
<?php
if ( $actors && ! is_wp_error( $actors ) ) :

	$items = array();

	foreach ( $actors as $actor )
		$items[] = array(
			'attr_title'  => sprintf( __( 'Permalink for &laquo; %s &raquo;', 'wpml' ), $actor->name ),
			'link'        => get_term_link( sanitize_term( $actor, 'actor' ), 'actor' ),
			'title'       => $actor->name . ( 1 == $count ? sprintf( '&nbsp;(%d)', $actor->count ) : '' )
		);

	$html = apply_filters( 'wpml_format_widget_lists', $items, $list, $css, __( 'Select an Actor', 'wpml' ) );

	echo $html;
else :
	printf( '<em>%s</em>', __( 'Nothing to display for "Actor" taxonomy.', 'wpml' ) );
endif; ?>