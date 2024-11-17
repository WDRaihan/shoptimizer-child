<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Edit_Order_Customer_Email extends WC_Email {

    // Constructor
    public function __construct() {
        $this->id             = 'edit_order_customer_email';
        $this->title          = 'Edit Order Customer Email';
        $this->description    = 'Customer edit order email notification';
        $this->heading        = 'Your Order Has Been Edited By Vendor';
        $this->subject        = 'Your Order is updated!';

        // WooCommerce email templates
        $this->template_html  = 'emails/edit-order-customer-email.php';

        // Trigger the email
        //add_action('woocommerce_order_status_completed_notification', [$this, 'trigger']);

        // Load the email template
        parent::__construct();
    }

    // Trigger function
    public function trigger($order_id) {
        if ( ! $order_id ) return;

        $this->object = wc_get_order( $order_id );
        $this->recipient = $this->object->get_billing_email();

        if ( ! $this->is_enabled() || ! $this->get_recipient() ) return;

        $this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
    }

    // HTML email content
    public function get_content_html() {
        return wc_get_template_html($this->template_html, [
            'order'         => $this->object,
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'         => $this
        ]);
    }

}
