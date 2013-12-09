<?php
load_theme_textdomain ('multiloquent');
get_template_part('classes/Bootstrap_Walker.class');
get_template_part('classes/Shoestrap_Walker_Comment.class');

/**
 * Returns a version number
 * @return string
 */

function version(){
	$version = '6.0.0';
	return $version;
}

function featured_image_in_feed( $content ) {
	global $post;
	if( is_feed() ) {
		if ( has_post_thumbnail( $post->ID ) ){
			$output = get_the_post_thumbnail( $post->ID, 'medium', array( 'style' => 'float:right; margin:0 0 10px 10px;' ) );
			$content = $output . $content;
		}
	}
	return $content;
}
add_filter( 'the_content', 'featured_image_in_feed' );
remove_filter('the_content','tptn_add_viewed_count');

// remove jetpack open graph tags
remove_action('wp_head','jetpack_og_tags');



if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 605, 100 );
}
if ( function_exists( 'add_image_size' ) ) {
	add_image_size( 'featured-post-thumbnail', 605, 100 );
}

if ( ! isset( $content_width ) ) $content_width = 900;
/**
 *
 * performs my initialisation stuff
 */

function my_init() {

	if( !is_admin()){
		wp_deregister_script('jquery');
	}


	add_theme_support( 'automatic-feed-links' );

	remove_action('wp_head', 'wp_print_scripts');
	remove_action('wp_head', 'wp_print_head_scripts', 9);
	remove_action('wp_head', 'wp_enqueue_scripts', 1);

	remove_action('wp_head', 'feed_links_extra', 3); // Display the links to the extra feeds such as category feeds
	remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
	remove_action( 'personal_options', '_admin_bar_preferences' );

	// put the wp head at the bottom, to see if the pageloads are faster....
	add_action('wp_footer', 'wp_print_scripts', 5);
	add_action('wp_footer', 'wp_enqueue_scripts', 5);
	add_action('wp_footer', 'wp_print_head_scripts', 5);


}

function dequeue_devicepx() {
	wp_dequeue_script( 'devicepx' );
}
add_action( 'wp_enqueue_scripts', 'dequeue_devicepx', 20 );
add_filter( 'post_class', 'remove_hentry_function', 20 );

function remove_hentry_function( $classes ) {
	if( ( $key = array_search( 'hentry', $classes ) ) !== false )
		unset( $classes[$key] );
	return $classes;
}

add_action('init', 'my_init');
add_action( 'after_setup_theme', 'register_my_menus' );

function register_my_menus() {
	register_nav_menus( array(
		'header_menu' => __( 'Header Navigation', 'multiloquent' ),
		'footer_menu' => __( 'Footer Navigation', 'multiloquent' ),

		) );
}

function add_class_the_tags($html){
	$postid = get_the_ID();
	$html = str_replace('<a','<a class="label"',$html);
	return $html;
}
add_filter('the_tags','add_class_the_tags',10,1);

//Widgetized sidebar
if ( function_exists('register_sidebar') ){
	register_sidebars((10),array(
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '<p class="nav-header">',
		'after_title' => '</p>',
		'class'         => '',
		));
}

add_filter( 'widget_tag_cloud_args', 'my_widget_tag_cloud_args' );
add_action('wp_tag_cloud', 'add_tag_class');
add_filter('wp_tag_cloud','wp_tag_cloud_filter', 10, 2);
function my_widget_tag_cloud_args( $args ) {
	$args['number'] = 20; // show less tags
	$args['largest'] = 9.75; // make largest and smallest the same - i don't like the varying font-size look
	$args['smallest'] = 9.75;
	$args['unit'] = 'px';
	return $args;
}

// filter tag clould output so that it can be styled by CSS
function add_tag_class( $taglinks ) {
	$tags = explode('</a>', $taglinks);
	$regex = "#(.*tag-link[-])(.*)(' title.*)#e";
	foreach( $tags as $tag ) {
		$tagn[] = preg_replace($regex, "('$1$2 label tag-'.get_tag($2)->slug.'$3')", $tag );
	}
	$taglinks = implode('</a>', $tagn);
	return $taglinks;
}

function wp_tag_cloud_filter($return, $args){
	return '<div id="tag-cloud">'.$return.'</div>';
}


/**
 *
 * outputs the breadcrumb
 *
 */
function breadcrumbs() {
	//$image_url =  get_template_directory_uri() ;
	if ( !is_home() ) {
		echo '<li><a href="';
		echo home_url();
		echo '">';
		echo 'home';
		echo '</a><span class="divider">/</span></li>';
	}

	if ( is_category() || (is_single() && !is_attachment())) {
		$category = get_the_category();
		$ID = $category[0]->cat_ID;
		echo '<li>'.get_category_parents($ID, TRUE, '<span class="divider">/</span>', FALSE );
	}

	if(is_single()) {echo '<li><h5 style="margin:0;padding:0">'.get_the_title().'</h5></li>';}
	if(is_page()) {echo '<li><h5 style="margin:0;padding:0">'.get_the_title().'</h5></li>';}
	if(is_tag()){ echo '<li><h5 style="margin:0;padding:0">Tag: '.single_tag_title('',FALSE).'</h5></li>'; }
	if(is_404()){ echo '<li><h5 style="margin:0;padding:0">404 - Page not Found</h5><li>'; }
	if(is_search()){ echo '<li><h5 style="margin:0;padding:0">Search</span></li>'; }
	if(is_year()){ echo '<li><h5 style="margin:0;padding:0">'.get_the_time('Y').'</h5></li>'; }

	//TODO - make it return rather than echo
}


function theimg2(){
	global $theimg2;
	// if its empty, set a random value upto 40 [I have 40 images that i want to return..]
	if (empty($theimg2)){
		$theimg2 = rand(1,39);
	} else {
		$theimg2++;
		if ($theimg2 > 39){
			$theimg2 = rand(1,39);
		}
	}
	return $theimg2;
}

/**
 *
 *  Function that Rounds To The Nearest Value.
 * Needed for the pagenavi() function
 **/
function round_num($num, $to_nearest) {
	/*Round fractions down (http://php.net/manual/en/function.floor.php)*/
	return floor($num/$to_nearest)*$to_nearest;
}


function jb_get_previous_posts_link( $label = null ) {
	global $paged;

	if ( null === $label )
		$label = __( '&laquo; Previous Page','multiloquent' );

	if ( !is_single() && $paged > 1 ) {
		$attr = apply_filters( 'previous_posts_link_attributes', '' );
		return '<a href="' . untrailingslashit(previous_posts( false )) . "\" $attr>". preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) .'</a>';
	}
}

/**
 *
 *  Function that performs a Boxed Style Numbered Pagination (also called Page Navigation).
 * Function is largely based on Version 2.4 of the WP-PageNavi plugin
 * */
function pagenavi($before = '', $after = '') {
	global $wpdb, $wp_query;
	$pagenavi_options = array();
	$pagenavi_options['pages_text'] = ('Page %CURRENT_PAGE% of %TOTAL_PAGES%:');
	$pagenavi_options['current_text'] = '%PAGE_NUMBER%';
	$pagenavi_options['page_text'] = '%PAGE_NUMBER%';
	$pagenavi_options['first_text'] = ('First Page');
	$pagenavi_options['last_text'] = ('Last Page');
	$pagenavi_options['next_text'] = 'Next &raquo;';
	$pagenavi_options['prev_text'] = '&laquo; Previous';
	$pagenavi_options['dotright_text'] = '...';
	$pagenavi_options['dotleft_text'] = '...';
	$pagenavi_options['num_pages'] = 5; //continuous block of page numbers
	$pagenavi_options['always_show'] = 0;
	$pagenavi_options['num_larger_page_numbers'] = 0;
	$pagenavi_options['larger_page_numbers_multiple'] = 5;

	//If NOT a single Post is being displayed
	/*http://codex.wordpress.org/Function_Reference/is_single)*/
if (!is_single()) {
	$request = $wp_query->request;
			//intval Get the integer value of a variable
	/*http://php.net/manual/en/function.intval.php*/
	$posts_per_page = intval(get_query_var('posts_per_page'));
			//Retrieve variable in the WP_Query class.
	/*http://codex.wordpress.org/Function_Reference/get_query_var*/
	$paged = intval(get_query_var('paged'));
	$numposts = $wp_query->found_posts;
	$max_page = $wp_query->max_num_pages;

			//empty Determine whether a variable is empty
	/*http://php.net/manual/en/function.empty.php*/
	if(empty($paged) || $paged == 0) {
		$paged = 1;
	}

	$pages_to_show = intval($pagenavi_options['num_pages']);
	$larger_page_to_show = intval($pagenavi_options['num_larger_page_numbers']);
	$larger_page_multiple = intval($pagenavi_options['larger_page_numbers_multiple']);
	$pages_to_show_minus_1 = $pages_to_show - 1;
	$half_page_start = floor($pages_to_show_minus_1/2);
			//ceil Round fractions up (http://us2.php.net/manual/en/function.ceil.php)
	$half_page_end = ceil($pages_to_show_minus_1/2);
	$start_page = $paged - $half_page_start;

	if($start_page <= 0) {
		$start_page = 1;
	}

	$end_page = $paged + $half_page_end;
	if(($end_page - $start_page) != $pages_to_show_minus_1) {
		$end_page = $start_page + $pages_to_show_minus_1;
	}
	if($end_page > $max_page) {
		$start_page = $max_page - $pages_to_show_minus_1;
		$end_page = $max_page;
	}
	if($start_page <= 0) {
		$start_page = 1;
	}

	$larger_per_page = $larger_page_to_show*$larger_page_multiple;
			//round_num() custom function - Rounds To The Nearest Value.
	$larger_start_page_start = (round_num($start_page, 10) + $larger_page_multiple) - $larger_per_page;
	$larger_start_page_end = round_num($start_page, 10) + $larger_page_multiple;
	$larger_end_page_start = round_num($end_page, 10) + $larger_page_multiple;
	$larger_end_page_end = round_num($end_page, 10) + ($larger_per_page);

	if($larger_start_page_end - $larger_page_multiple == $start_page) {
		$larger_start_page_start = $larger_start_page_start - $larger_page_multiple;
		$larger_start_page_end = $larger_start_page_end - $larger_page_multiple;
	}
	if($larger_start_page_start <= 0) {
		$larger_start_page_start = $larger_page_multiple;
	}
	if($larger_start_page_end > $max_page) {
		$larger_start_page_end = $max_page;
	}
	if($larger_end_page_end > $max_page) {
		$larger_end_page_end = $max_page;
	}
	if($max_page > 1 || intval($pagenavi_options['always_show']) == 1) {
		/*http://php.net/manual/en/function.str-replace.php */
		/*number_format_i18n(): Converts integer number to format based on locale (wp-includes/functions.php*/
			$pages_text = str_replace("%CURRENT_PAGE%", number_format_i18n($paged), $pagenavi_options['pages_text']);
			$pages_text = str_replace("%TOTAL_PAGES%", number_format_i18n($max_page), $pages_text);
			echo $before.'<div class="pagination pagination-centered"><ul>'."\n";

			if(!empty($pages_text)) {
				echo '<li><span>'.$pages_text.'</span></li>';
			}
				//Displays a link to the previous post which exists in chronological order from the current post.
			/*http://codex.wordpress.org/Function_Reference/previous_post_link*/
			$prev_link = jb_get_previous_posts_link($pagenavi_options['prev_text']);
			echo '<li>'.$prev_link.'</li>';

			if ($start_page >= 2 && $pages_to_show < $max_page) {
				$first_page_text = str_replace("%TOTAL_PAGES%", number_format_i18n($max_page), $pagenavi_options['first_text']);
					//esc_url(): Encodes < > & " ' (less than, greater than, ampersand, double quote, single quote).
				/*http://codex.wordpress.org/Data_Validation*/
					//get_pagenum_link():(wp-includes/link-template.php)-Retrieve get links for page numbers.
				echo '<li><a href="'.untrailingslashit(esc_url(get_pagenum_link())).'" class="first" title="'.$first_page_text.'">1</a></li>';
				if(!empty($pagenavi_options['dotleft_text'])) {
					echo '<li><span>'.$pagenavi_options['dotleft_text'].'</span></li>';
				}
			}

			if($larger_page_to_show > 0 && $larger_start_page_start > 0 && $larger_start_page_end <= $max_page) {
				for($i = $larger_start_page_start; $i < $larger_start_page_end; $i+=$larger_page_multiple) {
					$page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $pagenavi_options['page_text']);
					echo '<li><a href="'.untrailingslashit(esc_url(get_pagenum_link($i))).'" class="single_page" title="'.$page_text.'">'.$page_text.'</a></li>';
				}
			}

			for($i = $start_page; $i  <= $end_page; $i++) {
				if($i == $paged) {
					$current_page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $pagenavi_options['current_text']);
					echo '<li><span>'.$current_page_text.'</span></li>';
				} else {
					$page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $pagenavi_options['page_text']);
					echo '<li><a href="'.untrailingslashit(esc_url(get_pagenum_link($i))).'" class="single_page" title="'.$page_text.'">'.$page_text.'</a></li>';
				}
			}

			if ($end_page < $max_page) {
				if(!empty($pagenavi_options['dotright_text'])) {
					echo '<li><span>'.$pagenavi_options['dotright_text'].'</span></li>';
				}
				$last_page_text = str_replace("%TOTAL_PAGES%", number_format_i18n($max_page), $pagenavi_options['last_text']);
				echo '<li><a href="'.untrailingslashit(esc_url(get_pagenum_link($max_page))).'" class="last" title="'.$last_page_text.'">'.$max_page.'</a></li>';
			}

			$next_link = get_next_posts_link($pagenavi_options['next_text'], $max_page);
			echo '<li>'.$next_link.'</li>';

			if($larger_page_to_show > 0 && $larger_end_page_start < $max_page) {
				for($i = $larger_end_page_start; $i <= $larger_end_page_end; $i+=$larger_page_multiple) {
					$page_text = str_replace("%PAGE_NUMBER%", number_format_i18n($i), $pagenavi_options['page_text']);
					echo '<li><a href="'.untrailingslashit(esc_url(get_pagenum_link($i))).'" class="single_page" title="'.$page_text.'">'.$page_text.'</a></li>';
				}
			}
			echo '</ul></div>'.$after."\n";
		}
	}
}

function jb_flex_slider(){
	global $wpdb;
	$output='';
	$post_per_slide = '1';
	$total_posts = '10';
	if ( function_exists('tptn_pop_posts') ){
		$sql = "SELECT postnumber, sum(cntaccess) as sumCount, ID, post_type, post_status, post_content
		FROM wp_top_ten_daily INNER JOIN wp_posts ON postnumber=ID
		AND post_type = 'post'
		AND post_status = 'publish'
		and dp_date BETWEEN SYSDATE() - INTERVAL 30 DAY AND SYSDATE() group by ID
		ORDER BY sumCount DESC LIMIT 10;";
		$recent_posts = $wpdb->get_results($sql);
	} else {
		$args = array(
			'numberposts'     => $total_posts,
			'offset'          => 0,
			'category'        => '',
			'orderby'         => 'post_date',
			'order'           => 'DESC',
			'include'         => '',
			'exclude'         => '',
			'post_type'       => 'post',
			'post_status'     => 'publish' );
		$recent_posts = get_posts( $args );
	}
	$output = '<section id="myCarousel" class="carousel slide"><div class="carousel-inner">';
	$firsttime = 1;
	foreach ( $recent_posts as $key=>$val ) {
		$slider_image = get_post_meta($val->ID, 'bootstrap_slider', true);
		$widthheight= ' width="1100" height="500" ';
		if($slider_image){
			$theimg = get_post_meta($val->ID, 'bootstrap_slider', true);
			// in here I need to check if its a mobile, and then give a different image:
			if(is_mobile_device()){
				$theimg = substr($theimg,0,-4).'-300x136.png';
				$widthheight= ' width="300" height="136" ';
			}
		} else {
			$theimg =  get_template_directory_uri().'/images/default-slider.png';
		}

		$output .= '<article class="item';
		if($firsttime=='1'){
			$output .= ' active';
		}
		$output .= '"><img '.$widthheight.' src="'.$theimg.'" alt="image for '.trim(stripslashes(get_the_title($val->ID))).'"/>';
		$output .= '<div class="container">
		<div class="carousel-caption">
		<p><a href="'.get_permalink($val->ID).'">';
		$output .= '<marquee scrollamount="6" behavior="alternate">'.trim(stripslashes(get_the_title($val->ID))).'</marquee>';
		$output .= '</a></p></div></div></article>';

		$firsttime++;
	}
	$output .= '</div><a class="left carousel-control" href="#myCarousel" data-slide="prev">&lsaquo;</a><a class="right carousel-control" href="#myCarousel" data-slide="next">&rsaquo;</a></section>';
	return $output;
}

function get_random_solid_class($class=''){
	$input = array(
		"swatch-red",
		"swatch-orange",
		"swatch-yellow",
		"swatch-green",
		"swatch-teal",
		"swatch-blue",
		"swatch-violet",
		"swatch-pink",
		"swatch-mid-gray",
		"swatch-gray"

		);

	$apps =  array(
		"phone",
		"appstore",
		"calculator",
		"compass",
		"itunes",
		"mail",
		"music",
		"weather",
		"maps",
		"videos",
		"notes",
		"reminders",
		"calendar",
		"facebook",
		"google",
		"twitter",
		"linkedin",
		"finder",
		"safari",
		"firefox"
		);

	if(!empty($class) && in_array($class, $apps)){
		return $tile_colour = $class;
	} else {
		$rand_keys = array_rand($input);
		return $tile_colour = $input[$rand_keys];
	}
}


function get_random_blue_class(){
	$input = array(
		"swatch-blue1",
		"swatch-blue2",
		"swatch-blue3",
		"swatch-blue4",
		"swatch-blue5",
		"swatch-blue",
		"swatch-gray",
		"swatch-violet"

		);

	$apps =  array(
		"phone",
		"appstore",
		"calculator",
		"compass",
		"itunes",
		"mail",
		"music",
		"weather",
		"maps",
		"videos",
		"notes",
		"reminders",
		"calendar",
		"facebook",
		"google",
		"twitter",
		"linkedin",
		"finder",
		"safari",
		"firefox"
		);

	if(!empty($class) && in_array($class, $apps)){
		return $tile_colour = $class;
	} else {
		$rand_keys = array_rand($input);
		return $tile_colour = $input[$rand_keys];
	}

}

function get_random_colour_class($class=''){
	$input = array(
		"gradient-red",
		"gradient-orange",
		"gradient-yellow",
		"gradient-green",
		"gradient-teal",
		"gradient-blue",
		"gradient-violet",
		"gradient-magenta",
		"gradient-black",
		"gradient-silver",
		);

	$apps =  array(
		"phone",
		"appstore",
		"calculator",
		"compass",
		"itunes",
		"mail",
		"music",
		"weather",
		"maps",
		"videos",
		"notes",
		"reminders",
		"calendar",
		"facebook",
		"google",
		"twitter",
		"linkedin",
		"finder",
		"safari",
		"firefox"
		);

	if(!empty($class) && in_array($class, $apps)){
		return $tile_colour = $class;
	} else {
		$rand_keys = array_rand($input);
		return $tile_colour = $input[$rand_keys];
	}
}


function jb_tiled_slider(){
	global $wpdb;
	$output='';
	$post_per_slide = '1';
	$total_posts = '10';
	if ( function_exists('tptn_pop_posts') ){
		$sql = "SELECT postnumber, sum(cntaccess) as sumCount, ID, post_type, post_status, post_content
		FROM wp_top_ten_daily INNER JOIN wp_posts ON postnumber=ID
		AND post_type = 'post'
		AND post_status = 'publish'
		and dp_date BETWEEN SYSDATE() - INTERVAL 30 DAY AND SYSDATE() group by ID
		ORDER BY sumCount DESC LIMIT 10;";
		$recent_posts = $wpdb->get_results($sql);
	} else {
		$args = array(
			'numberposts'     => $total_posts,
			'offset'          => 0,
			'category'        => '',
			'orderby'         => 'post_date',
			'order'           => 'DESC',
			'include'         => '',
			'exclude'         => '',
			'post_type'       => 'post',
			'post_status'     => 'publish' );
		$recent_posts = get_posts( $args );
	}
	$count=1;
	$output = '<div class="container">';
	$output .= '<ul class="thumbnails">';
	$tile_colour = get_random_solid_class();
	foreach ( $recent_posts as $key=>$val ) {
		$slider_image = get_post_meta($val->ID, 'bootstrap_slider', true);
		//$widthheight= ' width="1100" height="500" ';
		if($slider_image){
			$theimg = $slider_image;
			// in here I need to check if its a mobile, and then give a different image:
			if(is_mobile_device()){
				$theimg = substr($theimg,0,-4).'-300x136.png';
				$widthheight= ' width="300" height="136" ';
			}
		} else {
			$theimg =  get_template_directory_uri().'/images/default-slider.png';
		}
		$output .= '<li ';
		if($count == '1'){
			$output .= 'class="tag-item tile tile-double double-height '.$tile_colour.'">';
		}
		if($count == '2'){
			$output .= 'class="tag-item tile tile-double double-height '.$tile_colour.'">';
		}
		if( $count == '3'){
			$output .= 'class="scroll-box tag-item tile tile-double '.$tile_colour.'">';
		}
		if($count == '4'){
			$output .= 'class="scroll-box tag-item tile tile-double '.$tile_colour.'">';
		}
		if($count == '5'){
			$output .= 'class="scroll-box tag-item tile tile-double '.$tile_colour.'">';
		}
		if($count == '6'){
			$output .= 'class="scroll-box tag-item tile '.$tile_colour.'">';
		}
		if($count == '7'){
			$output .= 'class="scroll-box tag-item tile '.$tile_colour.'">';
		}
		if($count == '8'){
			$output .= 'class="scroll-box tag-item tile tile-double '.$tile_colour.'">';
		}
		if($count == '9'){
			$output .= 'class="scroll-box tag-item tile '.$tile_colour.'">';
		}
		if($count == '10'){
			$output .= 'class="scroll-box tag-item tile '.$tile_colour.'">';
		}
		//$output .= '<li class="tag-item tile tile-double '.$tile_colour.'">';
		if($count=='6'||$count=='7'||$count=='9'||$count=='10'){
			$output .= '<h6 class="nowrap"><a href="'.get_permalink($val->ID).'">';
			$output .= '<span>'.trim(stripslashes(get_the_title($val->ID))).'</span>';
			$output .= '</a></h6>';
		}elseif($count=='3'||$count=='4'||$count=='5'||$count=='8'){
			$output .= '<h3 class="nowrap"><a href="'.get_permalink($val->ID).'">';
			$output .= '<span>'.trim(stripslashes(get_the_title($val->ID))).'</span>';
			$output .= '</a></h3>';
		} else {
			if(strlen(trim(stripslashes(get_the_title($val->ID)))) > 40){
				$output .= '<h3 class="nowrap"><a href="'.get_permalink($val->ID).'">'.trim(stripslashes(get_the_title($val->ID))).'</a></h3>';
			}else{
				$output .= '<h2 class="nowrap"><a href="'.get_permalink($val->ID).'">'.trim(stripslashes(get_the_title($val->ID))).'</a></h2>';
			}

		}
		$output .= '</li>';
		$count++;
	}
	$output .= '</ul>';
	$output .= '</div>';
	return $output;
}

function get_user_agents_list() {
	$useragents = array(
		"iPhone",        // Apple iPhone
		"iPod",       // Apple iPod touch
		"incognito",     // Other iPhone browser
		"iPad",      // iPad
		"webmate",     // Other iPhone browser
		"Android",      // 1.5+ Android
		"dream",       // Pre 1.5 Android
		"CUPCAKE",      // 1.5+ Android
		"blackberry9500",   // Storm
		"blackberry9530",   // Storm
		"blackberry9520",   // Storm v2
		"blackberry9550",   // Storm v2
		"blackberry 9800", // Torch
		"webOS",     // Palm Pre Experimental
		"s8000",       // Samsung Dolphin browser
		"bada",       // Samsung Dolphin browser
		"Googlebot-Mobile", // the Google mobile crawler
		"MSIE" // force internet explorer to not get the cool stuff cos its crap

		);

	asort( $useragents );
	return $useragents;
}

function is_mobile_device(){
	$useragents = get_user_agents_list();
	//echo '<!-- useragents'.print_r($userAgents).'-->';
	if(!empty($_SERVER['HTTP_USER_AGENT'])){
		$browser = $_SERVER['HTTP_USER_AGENT'];
	} else {
		$browser = '';
	}
	foreach( $useragents as $agent ) {
		if ( preg_match( "#$agent#i", $browser ) ) {
			$return = true;
			break;
		} else {
			$return = false;
		}
	}
	return $return;
}

function get_PostViews($post_ID){
	global $wpdb;
	$table_name = $wpdb->prefix . "top_ten";
	$cntaccess = '';
	if(is_admin()){
		$resultscount = $wpdb->get_row("select postnumber, cntaccess from $table_name WHERE postnumber = $post_ID");
		$cntaccess = number_format((($resultscount) ? $resultscount->cntaccess : 0));
	}
	return $cntaccess;
}

//Function: Add/Register the Non-sortable 'Views' Column to your Posts tab in WP Dashboard.
function post_column_views($newcolumn){
	//Retrieves the translated string, if translation exists, and assign it to the 'default' array.
	$newcolumn['post_views'] = __('Views','multiloquent');
	return $newcolumn;
}

function post_custom_column_views($column_name, $id){
	if($column_name === 'post_views'){
		echo get_PostViews(get_the_ID());
	}
}

function register_post_column_views_sortable( $newcolumn ) {
	$newcolumn['post_views'] = 'post_views';
	return $newcolumn;
}

// Add the sorting SQL for the themes
function new_posts_orderby($orderby, $wp_query)
{
	global $wpdb,$post;
	//$orderby = '';
	if(is_admin()){
		$table_name = $wpdb->prefix . "top_ten";
		$wp_query->query = wp_parse_args($wp_query->query);
		if ( 'post_views' == @$wp_query->query['orderby'] ) {
			$orderby = "(select cntaccess from ".$table_name." WHERE postnumber = $wpdb->posts.ID) ".$wp_query->get('order')."";

		}
		return $orderby;
	}
}

if(is_admin()){
	add_filter( 'manage_posts_columns', 'post_column_views' );
	add_action('manage_posts_custom_column', 'post_custom_column_views',10,2);
	add_filter( 'manage_edit-post_sortable_columns', 'register_post_column_views_sortable' );
	add_filter('posts_orderby', 'new_posts_orderby', 10, 2);
	add_editor_style('style.css');
	add_editor_style('bootstrap/bootstrap-min.css');
}

function make_category_list_as_hierarchy($cat='0'){
	$tags = get_categories('hide_empty=true&orderby=name&order=ASC&parent=' . $cat);
	// Output a wrapper so that our arrays will be contained in 4 columns.
	$html = '';
	if ($tags) {
		// Output the markup for each tag found for each character.
		// in here I need to recurse down
		$old_tile_colour = get_random_blue_class();
		foreach ( (array) $tags as $tag ) {
			// set the old colour so I can re-set it at the bottom
			$new_tile_colour = get_random_solid_class($tag->slug);
			// fetch the new colour, if the returned string matches the slug, then set the tile_colour to it, otherwise, set it to the old one which is only set before this loop
			if($new_tile_colour == $tag->slug){
				$tile_colour = $new_tile_colour;
			} else {
				$tile_colour = $old_tile_colour;
			}
			if($cat == '0'){
				$html .= '<ul class="thumbnails">';
			}
			$tag_link = get_category_link($tag->term_id);
			if(strlen($tag->name) > '30'){
				$html .= '<li class="tag-item tile tile-double double-height '.$tile_colour.'"onclick="javascript:window.location.href=';
				$html .= "'".$tag_link."'";
				$html .= '" >';
			}elseif(strlen($tag->name) > '10'){
				$html .= '<li class="tag-item tile tile-double '.$tile_colour.'"onclick="javascript:window.location.href=';
				$html .= "'".$tag_link."'";
				$html .= '" >';
			}elseif(strlen($tag->name) > '5'){
				$html .= '<li class="tag-item tile tile-double '.$tile_colour.'"onclick="javascript:window.location.href=';
				$html .= "'".$tag_link."'";
				$html .= '" >';
			} else {
				$html .= '<li class="tag-item tile '.$tile_colour.'"onclick="javascript:window.location.href=';
				$html .= "'".$tag_link."'";
				$html .= '" >';

			}
			if(strlen($tag->name) > '30'){
				$html .= '<h2 class="nowrap"><a href="'.$tag_link.'" title="View the article tagged '.$tag->name.'" >'.$tag->name.'</a></h2>';
				$html .= "<h4>{$tag->count}</h4>";
			}elseif(strlen($tag->name) > '10'){
				$html .= '<h3><a href="'.$tag_link.'" title="View the article tagged '.$tag->name.'" >'.$tag->name.'</a></h3>';
				$html .= "<h4>{$tag->count}</h4>";
			}else {
				$html .= '<h2><a href="'.$tag_link.'" title="View the article tagged '.$tag->name.'" >'.$tag->name.'</a></h2>';
				$html .= "<h4>{$tag->count}</h4>";
			}
			$html .= "</li>";
			$html .= make_category_list_as_hierarchy($tag->term_id);
			if($cat == '0'){
				$html .= '</ul>';
			}
		}
	}
	return $html;
}

function jb_paralax_slider(){
	global $wpdb;
	$output='';
	$post_per_slide = '1';
	$total_posts = '5';
	if ( function_exists('tptn_pop_posts') ){
		$sql = "SELECT postnumber, sum(cntaccess) as sumCount, ID, post_type, post_status, post_content
		FROM wp_top_ten_daily INNER JOIN wp_posts ON postnumber=ID
		AND post_type = 'post'
		AND post_status = 'publish'
		and dp_date BETWEEN SYSDATE() - INTERVAL 30 DAY AND SYSDATE() group by ID
		ORDER BY sumCount DESC LIMIT 5;";
		$recent_posts = $wpdb->get_results($sql);
	} else {
		$args = array(
			'numberposts'     => $total_posts,
			'offset'          => 0,
			'category'        => '',
			'orderby'         => 'post_date',
			'order'           => 'DESC',
			'include'         => '',
			'exclude'         => '',
			'post_type'       => 'post',
			'post_status'     => 'publish' );
		$recent_posts = get_posts( $args );
	}
	$count=1;
	$output = '<div class="container"><div class="row alpha">';
	foreach ( $recent_posts as $key=>$val ) {
		$tile_colour = get_random_blue_class();
		$slider_image = wp_get_attachment_image_src(get_post_thumbnail_id($val->ID), 'single-post-thumbnail');;
		if($slider_image){
			$theimg = $slider_image[0];
			$width = $slider_image[1];
			$height = $slider_image[2];
		} else {
			$theimg =  get_template_directory_uri().'/images/default-slider.png';
			$width = '1100';
			$height = '500';
		}
		if($count == '1'){
			$output .= '<div class="paralax_image_holder float_left span8 alpha omega doubleheight"> ';
			$output .= '<img src="'.$theimg.'" class="grayscale" alt="'.trim(stripslashes(get_the_title($val->ID))).'" width="'.$width.'" height="'. $height.'">';
			$output .= '<div class="paralax_image_bg doubleheight swatch-blue4"></div>';
		}
		if($count == '2'){
			$output .= '<div class="paralax_image_holder float_left span4 alpha omega"> ';
			$output .= '<img src="'.$theimg.'" class="grayscale" alt="'.trim(stripslashes(get_the_title($val->ID))).'" width="'.$width.'" height="'. $height.'">';
			$output .= '<div class="paralax_image_bg swatch-blue2"></div>';
		}
		if( $count == '3'){
			$output .= '<div class="paralax_image_holder float_left span4 alpha omega"> ';
			$output .= '<img src="'.$theimg.'" class="grayscale" alt="'.trim(stripslashes(get_the_title($val->ID))).'" width="'.$width.'" height="'. $height.'">';
			$output .= '<div class="paralax_image_bg swatch-blue5"></div>';
		}
		if($count == '4'){
			$output .= '<div class="paralax_image_holder float_left span4 alpha omega"> ';
			$output .= '<img src="'.$theimg.'" class="grayscale" alt="'.trim(stripslashes(get_the_title($val->ID))).'" width="'.$width.'" height="'. $height.'">';
			$output .= '<div class="paralax_image_bg swatch-blue"></div>';
		}
		if($count == '5'){
			$output .= '<div class="paralax_image_holder float_left span8 alpha omega"> ';
			$output .= '<img src="'.$theimg.'" class="grayscale" alt="'.trim(stripslashes(get_the_title($val->ID))).'" width="'.$width.'" height="'. $height.'">';
			$output .= '<div class="paralax_image_bg swatch-blue2"></div>';
		}
		$output .= '<div class="paralax_image_text"><h1><a href="'.get_permalink($val->ID).'">'.trim(stripslashes(get_the_title($val->ID))).'</a></h1>';
		$output .= '<p>';
		$posttags = wp_get_post_tags($val->ID);
		if ($posttags) {
			foreach($posttags as $tag) {
				$output .=  '<a class="label ';
				$output .=  get_random_solid_class($tag->slug);
				$output .=  '" rel="nofollow" href="/tag/'.$tag->slug.'"><span class="fa fa-folder-o fa-fw"></span> '.$tag->name.'</a>';
			}
		}
		$output .='</p></div>';
		$output .= '</div>';
		$count++;
	}
	$output .= '</div></div>';
	return $output;
}

function shoestrap_get_avatar($avatar) {
	$avatar = str_replace("class='avatar", "class='avatar pull-left media-object", $avatar);
	return $avatar;
}
add_filter('get_avatar', 'shoestrap_get_avatar');