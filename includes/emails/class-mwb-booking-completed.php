<?php
/**
 * Class MWB_Booking_Completed file.
 *
 * @author  makewebbetter
 * @package mwb-woocommerce-booking/included/emails
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

/**
 * Class WC_Customer_Cancel_Order
 */
class MWB_Booking_Completed extends WC_Email {

	/**
	 * MWB Booking meta
	 *
	 * @var [type]
	 */
	public $booking_id;

	/**
	 * MWB Booking meta
	 *
	 * @var [type]
	 */
	public $booking_meta;

	/**
	 * Create an instance of the class.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		// Email slug we can use to filter other data.
		// $this->id          = 'wc_customer_completed_booking';
		// $this->title       = __( 'Booking Completed', 'custom-wc-email' );
		// $this->description = __( 'An email sent to the customer when an order is cancelled.', 'custom-wc-email' );
		// // For admin area to let the user know we are sending this email to customers.
		// $this->customer_email = true;
		// $this->heading     = __( 'Booking Completed', 'custom-wc-email' );
		// // translators: placeholder is {blogname}, a variable that will be substituted when email is sent out.

		// // Template paths.
		// $this->template_html  = 'emails/wc-customer-complete-booking.php';
		// $this->template_plain = 'emails/plain/wc-customer-complete-booking.php';
		// $this->template_base  = MWB_WC_BK_BASEPATH . 'public/templates/';

		$this->id             = 'customer_completed_booking';
		$this->customer_email = true;
		$this->title          = __( 'Payment Completed Booking', 'mwb-wc-bk' );
		$this->description    = __( 'Booking payment complete emails are sent to customers when their payment is marked completed.', 'mwb-wc-bk' );
		$this->heading        = __( 'Booking Payment Completed', 'mwb-wc-bk' );

		// translators: placeholder is {blogname}, a variable that will be substituted when email is sent out.
		$this->subject = sprintf( _x( '[%s] Booking Payment Complete', 'Congratulations your booking payment is complete', 'mwb-wc-bk' ), '{blogname}' );

		$this->template_html  = 'emails/mwb-customer-complete-booking.php';
		$this->template_plain = 'emails/plain/mwb-customer-complete-booking.php';
		$this->template_base  = MWB_WC_BK_BASEPATH . 'admin/templates/';

		$this->placeholders = array(
			'{order_date}'   => '',
			'{order_number}' => '',
		);
		// Action to which we hook onto to send the email.

		add_action( 'mwb_booking_status_completed', array( $this, 'trigger' ), 10, 2 );
		add_action( 'mwb_booking_status_pending_to_completed', array( $this, 'trigger', 10, 2 ) );

		parent::__construct();
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int $booking_id The Booking ID.
	 * @param int $order_id The order ID.
	 */
	public function trigger( $booking_id, $order_id ) {
		$this->setup_locale();

		$order = wc_get_order( $order_id );

		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}
		$pos = get_post( ! empty( $booking_id ) ? $booking_id : 0 );
		if ( 'mwb_cpt_booking' !== $pos->post_type ) {
			return;
		}

		$this->booking_meta = get_post_meta( $booking_id, 'mwb_meta_data', true );
		$this->booking_id   = $booking_id;

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object    = $order;
			$this->recipient = $this->object->get_billing_email();

			$this->subject = __( 'Completed booking', 'mwb-wc-bk' );

			$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
			$this->placeholders['{order_number}'] = $this->object->get_order_number();
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
				'booking_id'         => $this->booking_id,
				'booking_meta'       => $this->booking_meta,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'order'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
				'booking_id'         => $this->booking_id,
				'booking_meta'       => $this->booking_meta,
			),
			'',
			$this->template_base
		);
	}
}
