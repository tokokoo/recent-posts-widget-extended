<?php
/**
 * Various functions used by the plugin.
 *
 * @package    Recent_Posts_Widget_Extended
 * @since      0.9.4
 * @author     Satrya
 * @copyright  Copyright (c) 2014, Satrya
 * @license    http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Sets up the default arguments.
 * 
 * @since  0.9.4
 */
function rpwe_get_default_args() {

	$css_defaults = ".rpwe-block ul{\nlist-style: none !important;\nmargin-left: 0 !important;\npadding-left: 0 !important;\n}\n\n.rpwe-block li{\nborder-bottom: 1px solid #eee;\nmargin-bottom: 10px;\npadding-bottom: 10px;\nlist-style-type: none;\n}\n\n.rpwe-block a{\ndisplay: inline !important;\ntext-decoration: none;\n}\n\n.rpwe-block h3{\nbackground: none !important;\nclear: none;\nmargin-bottom: 0 !important;\nmargin-top: 0 !important;\nfont-weight: 400;\nfont-size: 12px !important;\nline-height: 1.5em;\n}\n\n.rpwe-thumb{\nborder: 1px solid #eee !important;\nbox-shadow: none !important;\nmargin: 2px 10px 2px 0;\npadding: 3px !important;\n}\n\n.rpwe-summary{\nfont-size: 12px;\n}\n\n.rpwe-time{\ncolor: #bbb;\nfont-size: 11px;\n}\n\n.rpwe-alignleft{\ndisplay: inline;\nfloat: left;\n}\n\n.rpwe-alignright{\ndisplay: inline;\nfloat: right;\n}\n\n.rpwe-aligncenter{\ndisplay: block;\nmargin-left: auto;\nmargin-right: auto;\n}\n\n.rpwe-clearfix:before,\n.rpwe-clearfix:after{\ncontent: \"\";\ndisplay: table !important;\n}\n\n.rpwe-clearfix:after{\nclear: both;\n}\n\n.rpwe-clearfix{\nzoom: 1;\n}\n";

	$defaults = array(
		'title'             => esc_attr__( 'Recent Posts', 'rpwe' ),
		'title_url'         => '',

		'limit'            => 5,
		'offset'           => 0,
		'order'            => 'DESC',
		'orderby'          => 'date',
		'cat'              => array(),
		'tag'              => array(),
		'taxonomy'         => '',
		'post_type'        => array( 'post' ),
		'post_status'      => 'publish',
		'ignore_sticky'    => 1,

		'excerpt'          => false,
		'length'           => 10,
		'thumb'            => true,
		'thumb_height'     => 45,
		'thumb_width'      => 45,
		'thumb_default'    => 'http://placehold.it/45x45/f0f0f0/ccc',
		'thumb_align'      => 'rpwe-alignleft',
		'date'             => true,
		'readmore'         => false,
		'readmore_text'    => __( 'Read More &raquo;', 'rpwe' ),

		'styles_default'   => true,
		'css'              => $css_defaults,
		'cssID'            => '',
		'before'           => '',
		'after'            => ''
	);

	// Allow plugins/themes developer to filter the default arguments.
	return apply_filters( 'rpwe_default_args', $defaults );

}

/**
 * Outputs the recent posts.
 * 
 * @since  0.9.4
 */
function rpwe_recent_posts( $args = array() ) {
	echo rpwe_get_recent_posts( $args );
}

/**
 * Generates the posts markup.
 *
 * @since  0.9.4
 * @param  array  $args
 * @return string|array The HTML for the random posts.
 */
function rpwe_get_recent_posts( $args = array() ) {

	// Set up a default, empty variable.
	$html = '';

	// Merge the input arguments and the defaults.
	$args = wp_parse_args( $args, rpwe_get_default_args() );

	// Extract the array to allow easy use of variables.
	extract( $args );

	// Allow devs to hook in stuff before the loop.
	do_action( 'rpwe_before_loop' );

	// Display the default style of the plugin.
	if ( $args['styles_default'] == true ) {
		rpwe_custom_styles();
	}

	// If the default style is disbale then use the custom css if it not empty.
	if ( $args['styles_default'] == false && ! empty( $args['css'] ) ) {
		echo '<style>' . $args['css'] . '</style>';
	}
	
	// Get the posts query.
	$posts = rpwe_get_posts( $args );
	
	if ( $posts->have_posts() ) :

		$html = '<div ' . ( ! empty( $args['cssID'] ) ? 'id="' . sanitize_html_class( $args['cssID'] ) . '"' : '' ) . ' class="rpwe-block">';

			$html .= '<ul class="rpwe-ul">';

				while ( $posts->have_posts() ) : $posts->the_post();

					// Experiment!
					// Cropping image using Aqua Resizer
					$thumb_id = get_post_thumbnail_id();
					$img_url  = wp_get_attachment_url( $thumb_id, 'full' ); // Get img URL.
					$image    = aq_resize( $img_url, $args['thumb_width'], $args['thumb_height'], true ); // Resize & crop img.

					$html .= '<li class="rpwe-li rpwe-clearfix">';

						if ( $args['thumb'] ) :

							// Check if post has post thumbnail.
							if ( has_post_thumbnail() ) :
								$html .= '<a href="' . esc_url( get_permalink() ) . '"  rel="bookmark">';
									$html .= '<img class="' . $args['thumb_align'] . ' rpwe-thumb get-the-image" src="' . esc_url( $image ) . '" alt="' . esc_attr( get_the_title() ) . '">';
								$html .= '</a>';

							// If no post thumbnail found, check if Get The Image plugin exist and display the image.
							elseif ( function_exists( 'get_the_image' ) ) :
								$html .= get_the_image( array( 
									'height'        => $args['thumb_height'],
									'width'         => $args['thumb_width'],
									'image_class'   => $args['thumb_align'] . ' rpwe-thumb get-the-image',
									'image_scan'    => true,
									'default_image' => $args['thumb_default']
								) );

							// Display default image.
							elseif ( ! empty( $args['thumb_default'] ) ) :
								$html .= sprintf( '<a href="%1$s" rel="bookmark"><img class="%2$s rpwe-thumb rpwe-default-thumb" src="%3$s" alt="%4$s" width="%5$s" height="%6$s"></a>',
									esc_url( get_permalink() ),
									$args['thumb_align'],
									$args['thumb_default'],
									esc_attr( get_the_title() ),
									$args['thumb_width'],
									$args['thumb_height']
								);

							endif;

						endif;

						$html .= '<h3 class="rpwe-title"><a href="' . esc_url( get_permalink() ) . '" title="' . sprintf( esc_attr__( 'Permalink to %s', 'rpwe' ), the_title_attribute( 'echo=0' ) ) . '" rel="bookmark">' . esc_attr( get_the_title() ) . '</a></h3>';

						if ( $args['date'] ) :
							$html .= '<time class="rpwe-time published" datetime="' . esc_html( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>';
						endif;

						if ( $args['excerpt'] ) :
							$html .= '<div class="rpwe-summary">';
								$html .= wp_trim_words( apply_filters( 'rpwe_excerpt', get_the_excerpt() ), $args['length'], ' &hellip;' );
								if ( $args['readmore'] ) :
									$html .= '<a href="' . esc_url( get_permalink() ) . '" class="more-link">' . $args['readmore_text'] . '</a>';
								endif;
							$html .= '</div>';
						endif;

					$html .= '</li>';

				endwhile;

			$html .= '</ul>';

		$html .= '</div><!-- Generated by http://wordpress.org/plugins/recent-posts-widget-extended/ -->';

	endif;

	// Restore original Post Data.
	wp_reset_postdata();

	// Allow devs to hook in stuff after the loop.
	do_action( 'rpwe_after_loop' );
	
	// Return the  posts markup.
	return $args['before'] . apply_filters( 'rpwe_markup', $html ) . $args['after'];

}

/**
 * The posts query.
 *
 * @since  0.0.1
 * @param  array  $args
 * @return array
 */
function rpwe_get_posts( $args = array() ) {

	// Query arguments.
	$query = array(
		'offset'              => $args['offset'],
		'posts_per_page'      => $args['limit'],
		'orderby'             => $args['orderby'],
		'order'               => $args['order'],
		'post_type'           => $args['post_type'],
		'post_status'         => $args['post_status'],
		'ignore_sticky_posts' => $args['ignore_sticky'],
	);

	// Limit posts based on category.
	if ( ! empty( $args['cat'] ) ) {
		$query['category__in'] = $args['cat'];
	}

	// Limit posts based on post tag.
	if ( ! empty( $args['tag'] ) ) {
		$query['tag__in'] = $args['tag'];
	}

	/**
	 * Taxonomy query.
	 * Prop Miniloop plugin by Kailey Lampert.
	 */
	if ( ! empty( $args['taxonomy'] ) ) {

		parse_str( $args['taxonomy'], $taxes );

		$operator  = 'IN';
		$tax_query = array();
		foreach( array_keys( $taxes ) as $k => $slug ) {
			$ids = explode( ',', $taxes[$slug] );
			if ( count( $ids ) == 1 && $ids['0'] < 0 ) {
				// If there is only one id given, and it's negative
				// Let's treat it as 'posts not in'
				$ids['0'] = $ids['0'] * -1;
				$operator = 'NOT IN';
			}
			$tax_query[] = array(
				'taxonomy' => $slug,
				'field'    => 'id',
				'terms'    => $ids,
				'operator' => $operator 
			);
		}

		$query['tax_query'] = $tax_query;

	}

	// Allow plugins/themes developer to filter the default query.
	$query = apply_filters( 'rpwe_default_query_arguments', $query );

	// Perform the query.
	$posts = new WP_Query( $query );
	
	return $posts;

}

/**
 * Custom Styles.
 *
 * @since  0.8
 */
function rpwe_custom_styles() {
	?>
<style>
.rpwe-block ul{list-style:none!important;margin-left:0!important;padding-left:0!important;}.rpwe-block li{border-bottom:1px solid #eee;margin-bottom:10px;padding-bottom:10px;list-style-type: none;}.rpwe-block a{display:inline!important;text-decoration:none;}.rpwe-block h3{background:none!important;clear:none;margin-bottom:0!important;margin-top:0!important;font-weight:400;font-size:12px!important;line-height:1.5em;}.rpwe-thumb{border:1px solid #EEE!important;box-shadow:none!important;margin:2px 10px 2px 0;padding:3px!important;}.rpwe-summary{font-size:12px;}.rpwe-time{color:#bbb;font-size:11px;}.rpwe-alignleft{display:inline;float:left;}.rpwe-alignright{display:inline;float:right;}.rpwe-aligncenter{display:block;margin-left: auto;margin-right: auto;}.rpwe-clearfix:before,.rpwe-clearfix:after{content:"";display:table !important;}.rpwe-clearfix:after{clear:both;}.rpwe-clearfix{zoom:1;}
</style>
	<?php
}