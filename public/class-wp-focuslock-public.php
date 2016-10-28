<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_FocusLock_Public {

  /**
   * The version number.
   * @var     string
   * @access  public
   */
  public $version;

  /**
   * The main plugin file.
   * @var     string
   * @access  public
   */
  public $file;

  /**
   * Constructor function.
   * @access  public
   * @return  void
   */
  public function __construct ( $file = '', $version = '1.0.0' ) {

    $this->version = $version;
    $this->file = $file;

    add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue_styles' ), 10, 1 );
    add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue_scripts' ), 10, 1 );
  }

  /**
   * Register the stylesheets for the public area.
   *
   */
  public function public_enqueue_styles() {
    wp_enqueue_style( 'jquery_focuspoint_css', plugin_dir_url( __FILE__ ) . 'css/focuspoint.css');
  }

  /**
   * Register the scripts for the public area.
   *
   */
  public function public_enqueue_scripts() {
    wp_enqueue_script( 'jquery_focuspoint', plugin_dir_url( __FILE__ ) . 'js/jquery.focuspoint.min.js', array('jquery'), $this->version, true );
    wp_enqueue_script( 'wp_focuslock', plugin_dir_url( __FILE__ ) . 'js/wp-focuslock.js', array('jquery', 'jquery_focuspoint'), $this->version, true );
  }

  public function focus_coords( $attachment_id = false ){

	  $coords = false;

	  if( $attachment_id ){

		  $meta = get_post_meta( $attachment_id, 'focuslock_coords', true);

		  $coords = new stdClass();
		  $coords->data_focus_x = '0';
	      $coords->data_focus_y = '0';

		  if ( $meta ) {
			  $c = explode( '|', $meta );
			  $coords->data_focus_x = $c[0];
			  $coords->data_focus_y = $c[1];
		  }

	  }

	  return $coords;

  }

  public function get_size( $size, $meta, $width, $height ){

		$s = new stdClass();

		if( ! empty( $size ) ){

  			$s->width  = $meta[ 'sizes' ][ $size ][ 'width' ];
  			$s->height = $meta[ 'sizes' ][ $size ][ 'height' ];

  		} else {

  			$s->width  = $meta[ 'width' ];
  			$s->height = $meta[ 'height' ];

		}

		return $s;

	}

}


function get_focuslock_image( $args ){

	if( ! isset( $args[ 'id' ] ) ) {
		return new WP_Error( 'error', 'The attachment id is missing!' );
	}

	$default = [
		'width'  => false,
		'height' => false
	];

	$args = wp_parse_args( $args, $default );

	$wp_focusLock_public = new WP_FocusLock_Public();

	$image 			= new stdClass();
	$image->meta 	= wp_get_attachment_metadata( $args[ 'id' ] );
	$image->image 	= wp_get_attachment_image( $args[ 'id' ], $args['size']  );
	$image->size 	= $wp_focusLock_public->get_size( $args[ 'size' ], $image->meta, $args[ 'width' ], $args[ 'height' ] );
	$image->coords 	= $wp_focusLock_public->focus_coords( $args[ 'id' ] );
	$image->classes = 'focuspoint ' . $args[ 'classes' ];

	return $image;
}


function focuslock_image( $attachment_id, $image_size = false, $additional_classes = '', $width = false, $height = false ){

	  $args = [
	  	'id'		=> $attachment_id,
	  	'size'		=> $image_size,
	  	'classes'	=> $additional_classes,
	  	'width'		=> $width,
	  	'height'	=> $height,
	  ];

	  $image_args = get_focuslock_image( $args );

	  $style = [];

	  if( ! empty( $width ) ){
	  	$style[] = 'width: ' . $image_args->size->width . '; ';
	  }else{
	  	$style[] = 'width: ' . $width . '; ';
	  }

	  if( ! empty( $height ) ){
	  	$style[] = 'height: ' . $image_args->size->height . '; ';
	  }else{
	  	$style[] = 'height: ' . $height . '; ';
	  }

	  $attr = 'class="' . $image_args->classes . '" ';
	  $attr .= 'data-focus-x=" ' . $image_args->coords->data_focus_x . '" ';
	  $attr .= 'data-focus-y=" ' . $image_args->coords->data_focus_y . '" ';
	  $attr .= 'data-focus-w=" ' . $image_args->size->width . '" ';
	  $attr .= 'data-focus-h=" ' . $image_args->size->height . '" ';
  
	  $html = '<div style="' . implode( $style ) . '" id="focuslock_image_' . $attachment_id . '" ' . $attr . ' >';
	  $html .= $image_args->image;
	  $html .= '</div>';

  	echo $html;

}
