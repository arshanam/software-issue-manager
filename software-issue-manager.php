<?php
/** 
 * Plugin Name: Software Issue Manager
 * Plugin URI: http://emdplugins.com
 * Description: Software Issue Manager allows to track the progress and resolution of every project issue in a productive and efficient way.
 * Version: 1.0.0
 * Author: eMarket Design
 * Author URI: http://emarketdesign.com
 * Text Domain: sim-com
 * @package SIM_COM
 * @since WPAS 4.0
 */
/*
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
if (!defined('ABSPATH')) exit;
if (!class_exists('Software_Issue_Manager')):
	/**
	 * Main class for Software Issue Manager
	 *
	 * @class Software_Issue_Manager
	 */
	final class Software_Issue_Manager {
		/**
		 * @var Software_Issue_Manager single instance of the class
		 */
		private static $_instance;
		public $textdomain = 'sim-com';
		public $app_name = 'sim_com';
		/**
		 * Main Software_Issue_Manager Instance
		 *
		 * Ensures only one instance of Software_Issue_Manager is loaded or can be loaded.
		 *
		 * @static
		 * @see SIM_COM()
		 * @return Software_Issue_Manager - Main instance
		 */
		public static function instance() {
			if (!isset(self::$_instance)) {
				self::$_instance = new self();
				self::$_instance->define_constants();
				self::$_instance->includes();
				self::$_instance->load_plugin_textdomain();
				add_filter('the_content', array(
					self::$_instance,
					'change_content_excerpt'
				));
				add_filter('the_excerpt', array(
					self::$_instance,
					'change_content_excerpt'
				));
				add_action('admin_menu', array(
					self::$_instance,
					'display_settings'
				));
				add_action('widgets_init', array(
					self::$_instance,
					'include_widgets'
				));
			}
			return self::$_instance;
		}
		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', $this->textdomain) , '1.0');
		}
		/**
		 * Define Software_Issue_Manager Constants
		 *
		 * @access private
		 * @return void
		 */
		private function define_constants() {
			define('SIM_COM_VERSION', '1.0.0');
			define('SIM_COM_AUTHOR', 'eMarket Design');
			define('SIM_COM_PLUGIN_FILE', __FILE__);
			define('SIM_COM_PLUGIN_DIR', plugin_dir_path(__FILE__));
			define('SIM_COM_PLUGIN_URL', plugin_dir_url(__FILE__));
		}
		/**
		 * Include required files
		 *
		 * @access private
		 * @return void
		 */
		private function includes() {
			if (is_admin()) {
				//these files are in all apps
				if (!function_exists('emd_shc_button')) {
					require_once SIM_COM_PLUGIN_DIR . 'includes/admin/wpas-btn-functions.php';
				}
				if (!function_exists('emd_settings_page')) {
					require_once SIM_COM_PLUGIN_DIR . 'includes/admin/settings-functions.php';
				}
				//the rest
				if (!class_exists('Emd_Single_Taxonomy')) {
					require_once SIM_COM_PLUGIN_DIR . 'includes/admin/singletax/class-emd-single-taxonomy.php';
					require_once SIM_COM_PLUGIN_DIR . 'includes/admin/singletax/class-emd-walker-radio.php';
				}
			}
			//these files are in all apps
			if (!class_exists('RW_Meta_Box')) {
				require_once SIM_COM_PLUGIN_DIR . 'assets/ext/meta-box/meta-box.php';
			}
			if (!function_exists('emd_translate_date_format')) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/date-functions.php';
			}
			if (!function_exists('emd_limit_author_search')) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/common-functions.php';
			}
			if (!class_exists('Emd_Entity')) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/entities/class-emd-entity.php';
			}
			if (!function_exists('emd_get_template_part')) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/layout-functions.php';
			}
			//the rest
			if (!class_exists('Emd_Query')) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/class-emd-query.php';
			}
			if (!function_exists('emd_get_p2p_connections')) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/relationship-functions.php';
				require_once SIM_COM_PLUGIN_DIR . 'assets/ext/posts-to-posts/posts-to-posts.php';
			}
			if (!function_exists('emd_submit_form')) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/form-functions.php';
			}
			if (!function_exists('emd_shc_get_layout_list')) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/shortcode-functions.php';
			}
			if (!class_exists('Emd_Widget')) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/class-emd-widget.php';
			}
			//app specific files
			if (is_admin()) {
				require_once SIM_COM_PLUGIN_DIR . 'includes/admin/misc-functions.php';
				require_once SIM_COM_PLUGIN_DIR . 'includes/admin/glossary.php';
			}
			require_once SIM_COM_PLUGIN_DIR . 'includes/class-install-deactivate.php';
			require_once SIM_COM_PLUGIN_DIR . 'includes/entities/class-emd-project.php';
			require_once SIM_COM_PLUGIN_DIR . 'includes/entities/class-emd-issue.php';
			require_once SIM_COM_PLUGIN_DIR . 'includes/entities/emd-issue-shortcodes.php';
			require_once SIM_COM_PLUGIN_DIR . 'includes/forms.php';
			require_once SIM_COM_PLUGIN_DIR . 'includes/scripts.php';
			require_once SIM_COM_PLUGIN_DIR . 'includes/query-filters.php';
		}
		/**
		 * Loads plugin language files
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters('plugin_locale', get_locale() , $this->textdomain);
			$mofile = sprintf('%1$s-%2$s.mo', $this->textdomain, $locale);
			$mofile_shared = sprintf('%1$s-emd-plugins-%2$s.mo', $this->textdomain, $locale);
			$lang_file_list = Array(
				$this->textdomain . '-emd-plugins' => $mofile_shared,
				$this->textdomain => $mofile
			);
			foreach ($lang_file_list as $lang_key => $lang_file) {
				$localmo = SIM_COM_PLUGIN_DIR . '/lang/' . $lang_file;
				$globalmo = WP_LANG_DIR . '/' . $this->textdomain . '/' . $lang_file;
				if (file_exists($globalmo)) {
					load_textdomain($lang_key, $globalmo);
				} elseif (file_exists($localmo)) {
					load_textdomain($lang_key, $localmo);
				} else {
					load_plugin_textdomain($lang_key, false, SIM_COM_PLUGIN_DIR . '/lang/');
				}
			}
		}
		/**
		 * Changes content and excerpt on frontend views
		 *
		 * @access public
		 * @param string $content
		 *
		 * @return string $content , content or excerpt
		 */
		public function change_content_excerpt($content) {
			if (!is_admin()) {
				if (post_password_required()) {
					$content = get_the_password_form();
				} else {
					$mypost_type = get_post_type();
					if ($mypost_type == 'post' || $mypost_type == 'page') {
						$mypost_type = "emd_" . $mypost_type;
					}
					if (class_exists($mypost_type)) {
						$func = "change_content";
						$obj = new $mypost_type;
						$content = $obj->$func($content);
					}
				}
			}
			return $content;
		}
		/**
		 * Creates plugin settings submenu page under settings
		 *
		 * @access public
		 * @return void
		 */
		public function display_settings() {
			add_submenu_page('options-general.php', __('Software Issue Manager Settings', $this->textdomain) , __('Software Issue Manager Settings', $this->textdomain) , 'manage_options', $this->app_name . '_settings', array(
				$this,
				'display_settings_page'
			));
		}
		/**
		 * Calls settings function to display plugin settings page
		 *
		 * @access public
		 * @return void
		 */
		public function display_settings_page() {
			emd_settings_page($this->app_name);
		}
		/**
		 * Loads sidebar widgets
		 *
		 * @access public
		 * @return void
		 */
		public function include_widgets() {
			require_once SIM_COM_PLUGIN_DIR . 'includes/entities/class-emd-issue-widgets.php';
		}
	}
endif;
/**
 * Returns the main instance of Software_Issue_Manager
 *
 * @return Software_Issue_Manager
 */
function SIM_COM() {
	return Software_Issue_Manager::instance();
}
// Get the Software_Issue_Manager instance
SIM_COM();