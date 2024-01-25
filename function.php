<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_VERSION', '3.0.0' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'hello_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function hello_elementor_setup() {
		if ( is_admin() ) {
			hello_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'hello_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'hello-elementor' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'hello-elementor' ) ] );
		}

		if ( apply_filters( 'hello_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'hello_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);

			/*
			 * Editor Style.
			 */
			add_editor_style( 'classic-editor.css' );

			/*
			 * Gutenberg wide images.
			 */
			add_theme_support( 'align-wide' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'hello_elementor_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'hello_elementor_setup' );

function hello_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'hello_theme_version';
	// The theme version saved in the database.
	$hello_theme_db_version = get_option( $theme_version_option_name );

	// If the 'hello_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $hello_theme_db_version || version_compare( $hello_theme_db_version, HELLO_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, HELLO_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function hello_elementor_display_header_footer() {
		$hello_elementor_header_footer = true;

		return apply_filters( 'hello_elementor_header_footer', $hello_elementor_header_footer );
	}
}

if ( ! function_exists( 'hello_elementor_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function hello_elementor_scripts_styles() {
		$min_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( apply_filters( 'hello_elementor_enqueue_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor',
				get_template_directory_uri() . '/style' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'hello_elementor_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'hello-elementor-theme-style',
				get_template_directory_uri() . '/theme' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
		wp_enqueue_style(
			'hello-elementor-custom-scroll-style',
			get_template_directory_uri() . '/custom-scroll.css',
			[],
			HELLO_ELEMENTOR_VERSION
		);
		
		if ( hello_elementor_display_header_footer() ) {
			wp_enqueue_style(
				'hello-elementor-header-footer',
				get_template_directory_uri() . '/header-footer' . $min_suffix . '.css',
				[],
				HELLO_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_scripts_styles' );

if ( ! function_exists( 'hello_elementor_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function hello_elementor_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'hello_elementor_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'hello_elementor_register_elementor_locations' );

if ( ! function_exists( 'hello_elementor_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function hello_elementor_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'hello_elementor_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'hello_elementor_content_width', 0 );

if ( ! function_exists( 'hello_elementor_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function hello_elementor_add_description_meta_tag() {
		if ( ! apply_filters( 'hello_elementor_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'hello_elementor_add_description_meta_tag' );

// Admin notice
if ( is_admin() ) {
	require get_template_directory() . '/includes/admin-functions.php';
}

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'hello_elementor_customizer' ) ) {
	// Customizer controls
	function hello_elementor_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! hello_elementor_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'hello_elementor_customizer' );

if ( ! function_exists( 'hello_elementor_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function hello_elementor_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'hello_elementor_page_title', 'hello_elementor_check_hide_title' );

/**
 * BC:
 * In v2.7.0 the theme removed the `hello_elementor_body_open()` from `header.php` replacing it with `wp_body_open()`.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		wp_body_open();
	}
}



// custom functions


add_action('wp_enqueue_scripts', 'style1');  
function style1()
	{
		global $post;
		
		 if($post->ID == 1325){
					wp_enqueue_script( 'snap-scroll', get_template_directory_uri().'/assets/js/snap-scroll.js', false );	
			}
	}

	add_action( 'init', 'deque_script_snap_scroll' );
	function deque_script_snap_scroll(){
		if(is_admin()){
			wp_dequeue_script( 'snap-scroll');
		}
	}
function wp_heading_theme_switcher() {
	?>
	<script>
	/* On Mouse Over Change Images */
		jQuery('#project_type_list .elementor-widget-icon-list').mouseover(function(){		
	
		var pro_type = jQuery(this).attr('id')
	
		var image_src = 'https://www.butlersigns.uk/wp-content/uploads/2022/06/Yoga-House-Wooden-Shop-Front-Signage-with-gold-Leaf-lettering-WIP-8-scaled.jpg';
			switch (pro_type) {
				case 'corporate_type':
					image_src = 'https://www.butlersigns.uk/wp-content/uploads/2023/02/Corporate-Signage-APEX-External-Flat-Cut-Letters-00043Edit.png';
					break;
				case 'education_type':
					image_src = 'https://www.butlersigns.uk/wp-content/uploads/2023/02/Newbury-College-Renewbales-Centre-External-Signage-4Edit.png'; 
					break;
				case 'property_type':
					image_src = 'https://www.butlersigns.uk/wp-content/uploads/2022/12/Savills-1-scaled.jpg'; 
					break;
				case 'retail_type':
					image_src = 'https://www.butlersigns.uk/wp-content/uploads/2021/04/Skin-HQ-Leeds-Cut-Acrylic-Letters-October-2021-4Edit.png';
					break;
	
			}
			
			jQuery('#project_type_image').css("background-image", "url(" + image_src + ")");
		}); 
		
	/* On Mouse Out Change Images */	
			jQuery('#project_type_list').mouseleave(function(){
				var image_src = 'https://www.butlersigns.uk/wp-content/uploads/2022/06/Yoga-House-Wooden-Shop-Front-Signage-with-gold-Leaf-lettering-WIP-8-scaled.jpg';
				jQuery('#project_type_image').css("background-image", "url(" + image_src + ")");
			});
		
		
		
		
	document.addEventListener('DOMContentLoaded', function() {
		let observedElements = document.querySelectorAll('.elementor-section-wrap > *, .elementor > *:not(.elementor-section-wrap, #navsection)');
		const options = {
		threshold: 0,
		rootMargin: '-6% 0px -94%',
		}
		
		const inViewCallback = entries => {
			entries.forEach((entry, i, arr) => {
				if (entry.isIntersecting && entry.target.classList.contains('alternate-theme-switch')) {
					document.body.classList.add('alternate-theme');
				} else if (entry.isIntersecting) {
					document.body.classList.remove('alternate-theme');
					if (jQuery('#section-1').length > 0 ) {
						isScrolledIntoView( '#section-1' );
					}
				}
				if (entry.isIntersecting && entry.target.classList.contains('alternate-white-logo')) {	
					document.body.classList.add('alternate-white-logo-theme');
				}else{
					document.body.classList.remove('alternate-white-logo-theme');
				}
			});
		}
		
		let observer = new IntersectionObserver(inViewCallback, options);
		observedElements.forEach(element => {
			observer.observe(element);
		});
	});
	
		
	jQuery(window).scroll(function() {
		if (jQuery('#section-1').length > 0 ) {
			var section_height = jQuery('#section-1').height() - 5;
			var scroll = jQuery(window).scrollTop();
			isScrolledIntoView( '#section-1' );
		}
	});


	jQuery("ul#menu-1-b437f63 li").mouseover(function(){
		jQuery("ul#menu-1-b437f63 li:first-child").removeClass("home_heading_hover");
	});
	jQuery("ul#menu-1-b437f63 li").mouseout(function(){
		jQuery("ul#menu-1-b437f63 li:first-child").addClass("home_heading_hover");
	});


	jQuery(".below_main_heading li").mouseover(function(){
		jQuery(".first_heading_white").removeClass("home_heading_hover_second");
	});
	jQuery(".below_main_heading li").mouseout(function(){
		jQuery(".first_heading_white ").addClass("home_heading_hover_second ");
	});
		
	
	/* Scrolling Effect JS Start */
	//--- DEFINE a reusable animation function: ---//
	function scrollThere(targetElement, speed) {
		var check_aternate_theme = jQuery(targetElement).hasClass("alternate-theme-switch");
		if (check_aternate_theme) {
			jQuery('body').addClass('alternate-theme');
		} else {
			jQuery('body').removeClass('alternate-theme');
		}
		// initiate an animation to a certain page element:
		jQuery('html, body').stop().animate(
		{
			scrollTop: targetElement.offset().top 
		}, // move window so target element is at top of window
		speed, // speed in milliseconds
		'linear' // easing
		); // end animate
	} // end scrollThere function definition
	
	/* Scrolling Effect JS End */
	var userScroll = jQuery(document).scrollTop();
	  jQuery(window).scroll(function() {
		var newScroll = jQuery(document).scrollTop();
		if (jQuery(".page-id-5502")[0]){
		  if(userScroll - newScroll > 350 || newScroll - userScroll > 20){
			if( jQuery(".page-id-5502 .alternate-theme-only").css("display") == "none" ){
			  jQuery(".default-theme-only").show();
			}
			else{
			  jQuery(".default-theme-only").hide();
			}
		  }
	   }
	});
	jQuery('.arrow_down_key a').click(function(){
		jQuery('html, body').animate({
			scrollTop: jQuery( jQuery(this).attr('href') ).offset().top
		}, 1500);
		return false;
	});
	
	jQuery.fn.isInViewport = function() {
		var elementClass = jQuery(this).hasClass("alternate-white-logo");
		var elementTop = jQuery(this).offset().top;
		var elementBottom = elementTop + jQuery(this).outerHeight();
		var viewportTop = jQuery(window).scrollTop();
		var viewportBottom = viewportTop + jQuery(window).height();
		var view_top_check = viewportTop + 25;
		// console.log(viewportTop); console.log(elementTop);
		var alt_section_check = elementBottom > viewportTop && elementTop < viewportBottom;
		if ( alt_section_check ) {
			if ( elementTop <= view_top_check ) {
				jQuery('body').removeClass('white-bg-theme');
				if(elementClass){
					jQuery('body').addClass('alternate-white-logo-theme');
				}
				jQuery('body').addClass('green-nav-theme');
			} else {
				jQuery('body').addClass('white-bg-theme');
				jQuery('body').removeClass('green-nav-theme');
				if(elementClass){
					jQuery('body').removeClass('alternate-white-logo-theme');
				}
			}
		} else {
			jQuery('body').addClass('white-bg-theme');
			jQuery('body').removeClass('green-nav-theme');
			if(elementClass){
				jQuery('body').removeClass('alternate-white-logo-theme');
			}
		}
		return alt_section_check;
	};
	
	jQuery(window).on('resize scroll', function() {
		if (jQuery('.alternate-logo-menu').length){
			if (jQuery('.alternate-logo-menu').isInViewport()) {
				 // console.log('p');		
			} else {

			}
		}
		if (jQuery('.alternate-white-logo').length){
			if (jQuery('.alternate-white-logo').isInViewport()) {
				 // console.log('p');		
			} else {

			}
		}
	});
	
	function isScrolledIntoView(elem)
	{
		var docViewTop = jQuery(window).scrollTop();
		var docViewBottom = docViewTop + jQuery(window).height();
		var elemTop = jQuery(elem).offset().top;
		var elemBottom = elemTop + jQuery(elem).height();
		var elemheight = elemBottom - elemTop;
		var first_section = (elemBottom <= docViewBottom) && (elemheight <= docViewTop);
		if ( first_section ) {
			jQuery("body").removeClass("hide_site_logo");
		} else {
			jQuery("body").addClass("alternate-theme");
			if (jQuery("body.home").length) {
				jQuery("body").addClass("hide_site_logo");
			}
			// jQuery("#get-in-touch-black").hide();
		}
		return first_section;
	}
		
	</script>
	<?php
	}
	add_action( 'wp_footer', 'wp_heading_theme_switcher');
	
	
	/**
	 * Enqueue a script with jQuery as a dependency.
	 */
	function mousewheel_scroll_scripts_method() {
		wp_enqueue_script( 'mousewheel-scroll-script', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-mousewheel/3.1.12/jquery.mousewheel.min.js', array( 'jquery' ) );
	}
	add_action( 'wp_enqueue_scripts', 'mousewheel_scroll_scripts_method' );
	
	
	// Add specific CSS class by filter.

	add_filter( 'body_class', function( $classes ) {
        $new_classes = array();
        if ( is_front_page() ) {
            $new_classes = array( 'alternate-theme', 'hide_site_logo' );
        }
		return array_merge( $classes, $new_classes );
	} );	


add_filter( 'wp_lazy_loading_enabled', '__return_false' );

add_action( 'init', 'my_add_excerpts_to_pages' );
function my_add_excerpts_to_pages() {
     add_post_type_support( 'projects', 'excerpt' ); //change page with your post type slug.
	 add_post_type_support( 'the-blog', 'excerpt' );
	 add_post_type_support( 'products', 'excerpt' );
}
