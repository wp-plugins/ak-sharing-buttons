<?php
/*
Plugin Name: AK Sharing Buttons
Plugin URI: http://colourstheme.com/forums/forum/wordpress/plugin/ak-sharing-buttons/
Description: Ajax load and append a list of sharing button to single-post, static-page. Ex: facebook, twitter, pinterst, google-plus, linkedin.
Version: 1.0.5
Author: Colours Theme
Author URI: http://colourstheme.com
License: GNU General Public License v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

AK Sharing Buttons plugin, Copyright 2014 Kopatheme.com
AK Sharing Buttons is distributed under the terms of the GNU GPL.

Requires at least: 3.9
Tested up to: 4.2.2
Text Domain: ak-sharing-buttons
Domain Path: /languages/
*/

define('AKSB_IS_DEV', false);
define('AKSB_DIR_URL', plugin_dir_url(__FILE__));
define('AKSB_DIR_PATH', plugin_dir_path(__FILE__));

add_action('plugins_loaded', array('AK_Sharing_Buttons', 'plugins_loaded'));	
add_action('after_setup_theme', array('AK_Sharing_Buttons', 'after_setup_theme'), 11);	

class AK_Sharing_Buttons {

	/*
	 * Init action hook and filter hook for plugin.
	 */
	function __construct(){		
		if(!is_admin()){
				add_action('loop_start', array($this, 'loop_start'));
				add_action('loop_end', array($this, 'loop_end'));				
				add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
				add_action('wp_footer', array($this, 'add_security_key'));							
		}else{
			add_action( 'admin_init', array($this, 'admin_init'));
		}

		add_action('wp_ajax_aksb_load_sharing_buttons', array($this, 'load_sharing_buttons'));
		add_action('wp_ajax_nopriv_aksb_load_sharing_buttons', array($this, 'load_sharing_buttons'));					
	}

	public function admin_init(){
	 	add_settings_section(
			'aksb_config',
			'AK Sharing Buttons',
			array($this,'setting_section_callback_function'),
			'reading'
		);
	 	
	 	add_settings_field(
			'aksb_style',
			'Style',
			array($this,'setting_callback_function'),
			'reading',
			'aksb_config'
		);
	 	
	 	register_setting( 'reading', 'aksb_style' );		
	}

	public function setting_section_callback_function() {
		printf('Select a style for sharing buttons.');
	}


	function setting_callback_function() {
		$selected = get_option('aksb_style', 'static-links');
		$options = array(
			'classic'       => __('Classic - with sharing counter', 'ak-sharing-buttons'),
			'static-links'  => __('Static links (loading fastest - recommended)', 'ak-sharing-buttons')			
		);
		?>
		<select name="aksb_style" id="aksb_style">
			<?php 
			foreach ($options as $option_value => $option_label) {
				?>
				<option 
					value="<?php echo esc_attr($option_value); ?>" 
					<?php selected( $selected, $option_value, true); ?>>
					<?php echo esc_attr($option_label); ?>
				</option>
				<?php
			}
			?>
		</select>
		<?php		
	}

	/*
	 * load plugin text-domain for features "multi-languages"
	 */
	public static function plugins_loaded(){
		load_plugin_textdomain('ak-sharing-buttons', false, AKSB_DIR_PATH . '/languages/');
	}

	/*
	 * Create instance of class AK_Sharing_Buttons on action hook "after_setup_theme"
	 */
	public static function after_setup_theme(){
			new AK_Sharing_Buttons();							
	}	

	/*
	 * Check current page is post, page, (a singular object).
	 * And add fiter to append button wrap
	 */
	public function loop_start($query){
		if($query->is_main_query() && is_singular()){
			add_filter('the_content', array($this, 'add_buttons_wrap'));
		}
	}

	/*
	 * Check current page is post, page, (a singular object).
	 * Remove filter the_content / add_buttons_wrap after fire it.
	 */
	public function loop_end($query){
		if($query->is_main_query()  && is_singular()){
			remove_filter('the_content', array($this, 'add_buttons_wrap'));
		}
	}

	/*
	 * Add a div with ID:aksb-buttons-wrap, 
	 * with jquery event window.load(), a ajax request will be get sharing buttons and fill to this element.
	 */	
	public function add_buttons_wrap($content){		
		if(!empty($content)){
			$style = get_option('aksb_style', 'classic');
			switch ($style) {
				case 'classic':
					$content .= $this->get_layout_classic();
					break;
				default:
					$content .= $this->get_layout_static_links();
					break;
			}		
		}

		return $content;
	}

	public function get_layout_classic(){
		return '<div id="aksb-buttons-wrap" class="aksb-layout-classic clearfix"></div>';
	}

	public function get_layout_gooey_effect(){
		ob_start();
		?>
		gooey_effect
		<?php
		return ob_get_clean();
	}

	public function get_layout_static_links(){
		wp_reset_postdata();
		
		global $post;
		$post_id    = $post->ID;
		$post_url   = get_permalink($post_id);
		$post_title = get_the_title($post);
		$post_name  = get_post_field('post_name', $post);

		ob_start();
		?>
		<div id="aksb-buttons-wrap" class="aksb-layout-static-links">
		    
		    <div class="aksb-line aksb-first clearfix">
			    <!-- Facebook -->
			    <?php $url = sprintf('http://www.facebook.com/sharer.php?u=%s', $post_url); ?>
			    <a class="aksb-facebook" 
				    target="_blank"
				    title="<?php _e('Share on Facebook', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">
			    	<i class="aksb_icon_facebook"></i>
			    </a>

			    <!-- Twitter -->
			    <?php $url = sprintf('https://twitter.com/share?url=%s&amp;name=%s&amp;hashtags=%s', $post_url, $post_title, $post_name); ?>
			    <a class="aksb-twitter" 
				    target="_blank"
				    title="<?php _e('Share on Twitter', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">
			        <i class="aksb_icon_twitter"></i>
			    </a>

			    <!-- Google+ -->
			    <?php $url = sprintf('https://plus.google.com/share?url=%s', $post_url); ?>
			    <a class="aksb-google-plus" 
			    	target="_blank"
				    title="<?php _e('Share on Google plus', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">
			        <i class="aksb_icon_google-plus"></i>
			    </a>

			    
			    <!-- Pinterest -->			    
			    <a class="aksb-pinterest" 
			    	href="javascript:void((function()%7Bvar%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','http://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)%7D)());"
			    	target="_blank"
				    title="<?php _e('Share on Pinterest', 'ak-sharing-buttons'); ?>">
			       <i class="aksb_icon_pinterest"></i>
			    </a>

			    <!-- LinkedIn -->
			    <?php $url = sprintf('http://www.linkedin.com/shareArticle?mini=true&amp;url=%s', $post_url); ?>
			    <a class="aksb-linkedin" 
						target="_blank"
				    title="<?php _e('Share on Linkedin', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">			    
			        <i class="aksb_icon_linkedin"></i>
			    </a>

			    <!-- Tumblr-->
			    <?php $url = sprintf('http://www.tumblr.com/share/link?url=%s&amp;title=', $post_url, $post_title); ?>
			    <a class="aksb-tumblr" 
						target="_blank"
				    title="<?php _e('Share on Tumblr', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">
			         <i class="aksb_icon_tumblr"></i>
			    </a>

		  	</div>



		  	<div class="aksb-line aksb-last clearfix">

			    <!-- Digg -->
			    <?php $url = sprintf('http://www.digg.com/submit?url=%s', $post_url); ?>
			    <a class="aksb-digg"
			    	target="_blank"
				    title="<?php _e('Share on Digg', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">
			        <i class="aksb_icon_digg"></i>
			    </a>
			            
			    <!-- Reddit -->
			    <?php $url = sprintf('http://reddit.com/submit?url=%s&amp;title=%s', $post_url, $post_title); ?>
			    <a class="aksb-reddit"
			    	target="_blank"
				    title="<?php _e('Share on Reddit', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">
			       <i class="aksb_icon_reddit"></i> 
			    </a>
			    
			    <!-- StumbleUpon-->
			    <?php $url = sprintf('http://www.stumbleupon.com/submit?url=%s&amp;title=%s', $post_url, $post_title); ?>
			    <a class="aksb-stumbleupon"
			    	target="_blank"
				    title="<?php _e('Share on Stumbleupon', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">
			        <i class="aksb_icon_stumbleupon"></i>
			    </a>
			    
			    <!-- VK -->
			    <?php $url = sprintf('http://vkontakte.ru/share.php?url=%s', $post_url); ?>
			    <a class="aksb-vk"
			    	target="_blank"
				    title="<?php _e('Share on Vkontakte', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">
			        <i class="aksb_icon_vk"></i>
			    </a>
			    
			    <!-- Print -->			    
			    <a class="aksb-print" href="javascript:;" onclick="window.print()"
			    	title="<?php _e('Print this article', 'ak-sharing-buttons'); ?>">
			        <i class="aksb_icon_print"></i>
			    </a>

			    <!-- Email -->
			    <?php $url = sprintf('mailto:?Subject=%s&amp;Body=%s', $post_title, $post_url); ?>
			    <a class="aksb-envelope" 
			    	target="_blank"
				    title="<?php _e('Share on Email', 'ak-sharing-buttons'); ?>"
				    href="<?php echo esc_attr($url); ?>">			    
			         <i class="aksb_icon_envelope"></i>
			    </a>			    
		    </div>
		 
		</div>
		
		<?php		
		return ob_get_clean();
	}

	/*
	 * Add a hidden field to before tag body close.
	 * This field need for ajax security
	 */
	public function add_security_key(){
		wp_nonce_field('aksb_load_sharing_buttons', 'aksb-sharing-buttons-security');
	}

	/*
	 * An ajax response. Return sharing buttons to client.	 
	 */
	public function load_sharing_buttons(){
		check_ajax_referer('aksb_load_sharing_buttons', 'security');

		$post_id   = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
	
		if($post_id):
			$url   = get_permalink($post_id);
			$title = get_the_title($post_id);
			$thumb = '';

			if(has_post_thumbnail($post_id)){
				$image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
				if(isset($image[0]) && !empty($image[0])){
					$thumb = $image[0];
				}				
			}

			?>
				<!-- Twitter -->
				<a href="https://twitter.com/share" class="twitter-share-button" data-lang="en"></a>
				<script type="text/javascript">
					!function(d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
							if (!d.getElementById(id)) {
							js = d.createElement(s);
							js.id = id;
							js.src = "//platform.twitter.com/widgets.js";
							fjs.parentNode.insertBefore(js, fjs);
						}
					}(document, "script", "twitter-wjs");
				</script>

				<!-- Facebook -->
				<div class="fb-like" 
				data-send="false" 
				data-layout="button_count" 
				data-width="200" 
				data-show-faces="false"></div>
				<div id="fb-root"></div>
				<script type="text/javascript">
					(function(d, s, id) {
					var js, fjs = d.getElementsByTagName(s)[0];
					if (d.getElementById(id))
					return;
					js = d.createElement(s);
					js.id = id;
					js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
					fjs.parentNode.insertBefore(js, fjs);
					}(document, 'script', 'facebook-jssdk'));
				</script>

				<!-- Linkedin -->
				<script type="IN/Share" data-counter="right"></script>       
				<script src="//platform.linkedin.com/in.js" type="text/javascript"></script>

				<!-- Pinterest -->
				<?php $pinterest_url = sprintf('http://pinterest.com/pin/create/button/?url=%s&media=%s&description=%s', esc_url($url), esc_url($thumb), esc_attr( $title)); ?>
				<span class="pin-it">
					<a href="<?php echo esc_url($pinterest_url); ?>" 
						class="pin-it-button" 
						count-layout="horizontal"></a>
				</span>
				<script type="text/javascript" src="http://assets.pinterest.com/js/pinit.js"></script>				

				<!-- Google Plus -->
				<div class="g-plusone" data-size="medium"></div>
				<script type="text/javascript">
					(function() {
						var po = document.createElement('script');
						po.type = 'text/javascript';
						po.async = true;
						po.src = 'https://apis.google.com/js/plusone.js';
						var s = document.getElementsByTagName('script')[0];
						s.parentNode.insertBefore(po, s);
					})();
				</script>				
			<?php
		endif;

		exit();
	}

	/*
	 * Enqueu javascript, stylesheet for sharing buttons
	 */
	public function enqueue_scripts(){		
		$suffix = AKSB_IS_DEV ? '' : '.min';
		wp_enqueue_style('aksb-style', AKSB_DIR_URL . "css/style{$suffix}.css", array(), NULL);
    wp_enqueue_script('aksb-script', AKSB_DIR_URL . "js/script{$suffix}.js", array('jquery'), FALSE, TRUE);
    wp_localize_script('aksb-script', 'aksb', array(
			'url'     => admin_url('admin-ajax.php'),
			'post_id' => is_singular() ? get_queried_object_id() : 0
    ));
	}
}