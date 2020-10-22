<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Mwb_Wc_Bk
 * @subpackage Mwb_Wc_Bk/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mwb_Wc_Bk
 * @subpackage Mwb_Wc_Bk/admin
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Mwb_Wc_Bk_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public $check;

	/**
	 * MWB Booking Fields
	 *
	 * @var array
	 */
	public $setting_fields = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		register_activation_hook( __FILE__, array( $this, 'install' ) );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mwb-wc-bk-admin.css', array(), $this->version, 'all' );
		//wp_enqueue_style( 'select2_css', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mwb-wc-bk-admin.js', array( 'jquery' ), $this->version, false );
		/*wp_enqueue_script( 'select2_js', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( 'mwb_booking_select2', plugin_dir_url( __FILE__ ) . 'js/mwb_select2.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( 'mwb_booking_select2', 'ajax_url', admin_url( 'admin-ajax.php' ) );*/

	}

	/**
	 * Include class for booking product type
	 */
	public function register_booking_product_type() {
		require_once MWB_WC_BK_BASEPATH . 'includes/class-mwb-wc-bk-product.php';
	}

	/**
	 * Add booking product option in products tab
	 *
	 * @param array $type Defining product type.
	 * @return $type
	 */
	public function add_mwb_booking_product_selector( $type ) {
		$type['mwb_booking'] = __( 'MWB Booking', 'mwb-wc-bk' );
		return $type;
	}
	/**
	 * Add virtual option for bookable product.
	 *
	 * @param array $options Contains the default virtual and downloadable options.
	 * @return array
	 */
	public function mwb_booking_virtual_product_options( $options ) {
		$options['virtual']['wrapper_class'] .= 'show_if_mwb_booking';
		return $options;
	}
	/**
	 * Add General Settings Tab for bookable product type
	 *
	 * @param array $tabs Product Panel Tabs.
	 * @return array
	 */
	public function mwb_add_general_settings( $tabs ) {

		$tabs = array_merge(
			$tabs,
			array(
				'general_settings' => array(
					'label'    => 'General Settings',
					'target'   => 'mwb_booking_general_data',
					'class'    => array( 'show_if_mwb_booking' ),
					'priority' => 10,
				),
				'cost'             => array(
					'label'    => 'Costs',
					'target'   => 'mwb_booking_cost_data',
					'class'    => array( 'show_if_mwb_booking' ),
					'priority' => 20,
				),
				'availability'     => array(
					'label'    => 'Availability',
					'target'   => 'mwb_booking_availability_data',
					'class'    => array( 'show_if_mwb_booking' ),
					'priority' => 30,
				),
				'people'           => array(
					'label'    => 'People',
					'target'   => 'mwb_booking_people_data',
					'class'    => array( 'show_if_mwb_booking' ),
					'priority' => 40,
				),
				'services'         => array(
					'label'    => 'Services',
					'target'   => 'mwb_booking_services_data',
					'class'    => array( 'show_if_mwb_booking' ),
					'priority' => 50,
				),
			)
		);
		return $tabs;
	}

	/**
	 * Installing on activation
	 *
	 * @return void
	 */
	public function install() {
		// If there is no advanced product type taxonomy, add it.
		if ( ! get_term_by( 'slug', 'mwb_booking', 'product_type' ) ) {
			wp_insert_term( 'mwb_booking', 'product_type' );
		}
	}
	/**
	 * General Settings fields.
	 *
	 * @return void
	 */
	public function product_booking_fields() {

		global $post;
		$product = wc_get_product($post->ID);
		$product_id = $product->get_id();
		$this->set_prouduct_settings_fields($product_id);

		include MWB_WC_BK_BASEPATH . 'admin/partials/product-booking-tabs/general-setting-fields-tab.php';
		include MWB_WC_BK_BASEPATH . 'admin/partials/product-booking-tabs/availability-fields-tab.php';
		include MWB_WC_BK_BASEPATH . 'admin/partials/product-booking-tabs/people-fields-tab.php';
		include MWB_WC_BK_BASEPATH . 'admin/partials/product-booking-tabs/services-fields-tab.php';
		include MWB_WC_BK_BASEPATH . 'admin/partials/product-booking-tabs/cost-fields-tab.php';
		

	}

	/**
	 * Saving the booking fields
	 *
	 * @return void
	 */
	public function save_product_booking_fields( $post_id ) {

		foreach ($this->get_product_settings() as $key => $value) {
			$posted_data = ! empty( $_POST[$key] ) ? sanitize_text_field( wp_unslash( $_POST[$key] ) ) : $value['default'];
		 	update_post_meta( $post_id , $key , $posted_data) ; 
		} 
	}

	public function get_product_settings(){
		return array(
			'mwb_booking_unit_select' => array('default' => 'customer'),
			'mwb_booking_unit_input' => array('default' => '1'),
			'mwb_booking_unit_duration' => array('default' => 'day'),
			'start_booking_date' => array('default' => ''),
			'start_booking_time' => array('default' => ''),
			'start_booking_custom_date' => array('default' => ''),
			'enable_range_picker'=> array('default' => 'no') ,
			'full_day_booking' => array('default' => 'no') ,
			'admin_confirmation' => array('default' => 'no') ,
			'allow_cancellation' => array('default' => 'no'),
			'max_days_for_cancellation' => array('default' => ''),
			'mwb_enable_range_picker' => array('default' => 'no') , 
			'mwb_full_day_booking' => array('default' => 'no') ,
			'mwb_admin_confirmation_required' => array('default' => 'no') ,
			'mwb_allow_booking_cancellation' => array('default' => 'no'),
			'mwb_max_day_for_cancellation' => array('default' => '') 
		);
	}

	/**
	 * Ajax Handler to search weekdays
	 *
	 * @return void
	 */
	public function mwb_booking_search_weekdays() {
		$arr = array(
			'sunday'    => __( 'Sunday', 'mwb-wc-bk' ),
			'monday'    => __( 'Monday', 'mwb-wc-bk' ),
			'tuesday'   => __( 'Tuesday', 'mwb-wc-bk' ),
			'wednesday' => __( 'Wednesday', 'mwb-wc-bk' ),
			'thursday'  => __( 'Thursday', 'mwb-wc-bk' ),
			'friday'    => __( 'Friday', 'mwb-wc-bk' ),
			'saturday'  => __( 'Saturday', 'mwb-wc-bk' ),
		);
		return $arr ; 
	}

	public function set_prouduct_settings_fields($product_id){
		foreach ($this->get_product_settings() as $key => $value) {
			$data = get_post_meta( $product_id , $key , true) ; 
			$this->setting_fields[$key] = !empty( $data ) ? $data : $value['default'] ; 
		}
	}

	public function get_booking_unit_duration_options(){
		return array(
			'month' => __('Month(s)' , 'mwb-wc-bk') , 
			'day' => __('Day(s)' , 'mwb-wc-bk') , 
			'hour' => __('Hour(s)' , 'mwb-wc-bk') , 
			'minute' => __('Minute(s)' , 'mwb-wc-bk') , 
		);
	}

}
