<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

/**
 * Remove Product Types
 */
function dokan_remove_product_types( $product_types ){
    //unset( $product_types['variable'] );
	unset( $product_types['external'] );
	unset( $product_types['grouped'] );
    return $product_types;
}
add_filter( 'dokan_product_types', 'dokan_remove_product_types', 11 );


/* Custom registration fields */
//All states of US
function us_states_data_array(){
	return array(
	  'AL' => 'Alabama',
	  'AK' => 'Alaska',
	  'AS' => 'American Samoa',
	  'AZ' => 'Arizona',
	  'AR' => 'Arkansas',
	  'CA' => 'California',
	  'CO' => 'Colorado',
	  'CT' => 'Connecticut',
	  'DE' => 'Delaware',
	  'DC' => 'District Of Columbia',
	  'FM' => 'Federated States Of Micronesia',
	  'FL' => 'Florida',
	  'GA' => 'Georgia',
	  'GU' => 'Guam Gu',
	  'HI' => 'Hawaii',
	  'ID' => 'Idaho',
	  'IL' => 'Illinois',
	  'IN' => 'Indiana',
	  'IA' => 'Iowa',
	  'KS' => 'Kansas',
	  'KY' => 'Kentucky',
	  'LA' => 'Louisiana',
	  'ME' => 'Maine',
	  'MH' => 'Marshall Islands',
	  'MD' => 'Maryland',
	  'MA' => 'Massachusetts',
	  'MI' => 'Michigan',
	  'MN' => 'Minnesota',
	  'MS' => 'Mississippi',
	  'MO' => 'Missouri',
	  'MT' => 'Montana',
	  'NE' => 'Nebraska',
	  'NV' => 'Nevada',
	  'NH' => 'New Hampshire',
	  'NJ' => 'New Jersey',
	  'NM' => 'New Mexico',
	  'NY' => 'New York',
	  'NC' => 'North Carolina',
	  'ND' => 'North Dakota',
	  'OH' => 'Ohio',
	  'OK' => 'Oklahoma',
	  'OR' => 'Oregon',
	  'PA' => 'Pennsylvania',
	  'PR' => 'Puerto Rico',
	  'RI' => 'Rhode Island',
	  'SC' => 'South Carolina',
	  'SD' => 'South Dakota',
	  'TN' => 'Tennessee',
	  'TX' => 'Texas',
	  'UT' => 'Utah',
	  'VT' => 'Vermont',
	  'VI' => 'Virgin Islands',
	  'VA' => 'Virginia',
	  'WA' => 'Washington',
	  'WV' => 'West Virginia',
	  'WI' => 'Wisconsin',
	  'WY' => 'Wyoming',
	  'AE' => 'Armed Forces Africa \ Canada \ Europe \ Middle East',
	  'AA' => 'Armed Forces America (Except Canada)',
	  'AP' => 'Armed Forces Pacific'
	);
}

//Add fields to WooCommerce register form
add_action( 'woocommerce_register_form', 'add_custom_registration_field', 5 );
function add_custom_registration_field() {
	?>
	<p class="form-row form-group form-row-wide">
		<label for="licensed_business_name"><?php esc_html_e( 'Licensed Business Name', 'dokan-lite' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text form-control" name="licensed_business_name" id="licensed_business_name" value="<?php echo ! empty( $_POST['licensed_business_name'] ) ? esc_attr( $_POST['licensed_business_name'] ) : ''; ?>"  />
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="license_id"><?php esc_html_e( 'License ID', 'dokan-lite' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text form-control" name="license_id" id="license_id" value="<?php echo ! empty( $_POST['license_id'] ) ? esc_attr( $_POST['license_id'] ) : ''; ?>"  />
	</p>
	<p class="form-row form-group form-row-wide">
		<label for="state"><?php esc_html_e( 'State', 'dokan-lite' ); ?> <span class="required">*</span></label>
		<select class="form-control" name="state" id="state">
			<option value="">-Select State-</option>
			<?php
			$us_states = us_states_data_array();
			foreach( $us_states as $val=>$us_state ){
				echo '<option value="'.$val.'" '.selected($val, $_POST['state']).' >'.$us_state.'</option>';
			}
			?>
		</select>
	</p>
	<?php
}

//Validate form fields
add_action('woocommerce_register_post', 'validate_custom_reg_form_fields', 10, 3);
function validate_custom_reg_form_fields($username, $email, $validation_errors) {
    if (isset($_POST['licensed_business_name']) && empty($_POST['licensed_business_name'])) {
        $validation_errors->add('licensed_business_name_error', __('Licensed business name is required!', 'text_domain'));
    }

    if (isset($_POST['license_id']) && empty($_POST['license_id'])) {
        $validation_errors->add('license_id', __('License ID is required!.', 'text_domain'));
    }

    if (isset($_POST['state']) && empty($_POST['state'])) {
        $validation_errors->add('state', __('State is required!.', 'text_domain'));
    }
    return $validation_errors;
}

//Save fields
add_action( 'woocommerce_created_customer', 'woo_save_extra_fields', 10, 2 );
function woo_save_extra_fields( $vendor_id ) {
    $post_data = wp_unslash( $_POST );

    $licensed_business_name = $post_data['licensed_business_name'];
	$license_id = $post_data['license_id'];
	$state = $post_data['state'];
   
    update_user_meta( $vendor_id, 'licensed_business_name', sanitize_text_field($licensed_business_name) );
	update_user_meta( $vendor_id, 'license_id', sanitize_text_field($license_id) );
	update_user_meta( $vendor_id, 'state', sanitize_text_field($state) );
}

//Show fields in user profile settings.
add_action( 'show_user_profile', 'show_extra_profile_fields', 30 );
add_action( 'edit_user_profile', 'show_extra_profile_fields', 30 ); 
function show_extra_profile_fields( $user ) { 

	$licensed_business_name  = get_user_meta( $user->ID, 'licensed_business_name', true );
	$license_id  = get_user_meta( $user->ID, 'license_id', true );
	$state  = get_user_meta( $user->ID, 'state', true );
    ?>
	<table class="form-table">
         <tr>
			<th><?php esc_html_e( 'Licensed Business Name', 'dokan-lite' ); ?></th>
			<td>
				<input type="text" name="licensed_business_name" class="regular-text" value="<?php echo esc_attr($licensed_business_name); ?>"/>
			</td>
         </tr>
		 <tr>
			<th><?php esc_html_e( 'License ID', 'dokan-lite' ); ?></th>
			<td>
				<input type="text" name="license_id" class="regular-text" value="<?php echo esc_attr($license_id); ?>"/>
			</td>
         </tr>
		<tr>
			<th><?php esc_html_e( 'State', 'dokan-lite' ); ?></th>
			<td>
				<select class="form-control" name="state" id="state">
					<option value="">-Select State-</option>
					<?php
					$us_states = us_states_data_array();
					foreach( $us_states as $val=>$us_state ){
						echo '<option value="'.$val.'" '.selected($val, $state, true).' >'.$us_state.'</option>';
					}
					?>
				</select>
			</td>
         </tr>
	</table>
    <?php
 }

//Save fields by admin in user profile page
add_action( 'personal_options_update', 'save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_profile_fields' );
function save_extra_profile_fields( $user_id ) {

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
    update_user_meta( $user_id, 'licensed_business_name', sanitize_text_field($_POST['licensed_business_name']) );
	update_user_meta( $user_id, 'license_id', sanitize_text_field($_POST['license_id']) );
	update_user_meta( $user_id, 'state', sanitize_text_field($_POST['state']) );
}

//Hide product price and add to cart
add_filter( 'woocommerce_get_price_html', 'remove_product_price_and_add_to_cart_for_not_logged_in_users', 999, 2 );
function remove_product_price_and_add_to_cart_for_not_logged_in_users( $price, $product ) {
   	$current_user = wp_get_current_user();
	$user_state = get_user_meta( $current_user->ID, 'state', true );
	$product_state = get_post_meta($product->get_id(), 'state', true);
	
	if ( ! is_user_logged_in() ) {
		  $price = '<div><a href="' . get_permalink( wc_get_page_id( 'myaccount' ) ) . '">Please Sign Up or Login to View Prices and Place Orders</a></div>';
		  remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
		  remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		  add_filter( 'woocommerce_is_purchasable', '__return_false' );

	} elseif ( in_array( 'seller', $current_user->roles ) || in_array( 'vendor_staff', $current_user->roles ) && ! in_array( 'vendor_sales_rep', $current_user->roles ) ) {
	   if ( !isset($_GET['add_new_product']) ){
			$price = '';
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			add_filter( 'woocommerce_is_purchasable', '__return_false' );
	   }
	}elseif( in_array( 'customer', $current_user->roles ) && $user_state !== $product_state){
			$price = '';
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			add_filter( 'woocommerce_is_purchasable', '__return_false' );
	}
	
   	return $price;
}

// Redirect after registration
add_action( 'woocommerce_created_customer', 'display_popup_after_registration' );
function display_popup_after_registration( $customer_id ) {
    // Add notice
	//wc_print_notice( 'Thank you for registering!<br>Please check your inbox to complete the registration process. <span class="popup-notice-close">I understand</span>', 'success',  );
	
	if ( isset( $_POST['register'] ) ) {
        wp_safe_redirect( add_query_arg( 'registered', '1', wc_get_page_permalink( 'myaccount' ) ) );
        exit;
    }
}

//Display and hide registration notice popup
add_action('wp_footer', function(){
if ( isset( $_GET['registered'] ) ) {
?>
<div class="reg-popup" role="alert">
		Thank you for registering!<br>Please check your inbox to complete the registration process. <span class="popup-notice-close"><a href="<?php echo home_url(); ?>">I understand</a></span>
</div>
<script>
	jQuery(document).ready(function(){
		jQuery(document).on('click', '.popup-notice-close', function(){
			jQuery('.reg-popup').hide();
		});
	});
</script>
<?php
	}
});

//Customers can see stores and products from their own state
add_action( 'pre_get_posts', 'filter_woocommerce_product_query_meta' );
function filter_woocommerce_product_query_meta( $query ) {
    // Ensure this code runs only on WooCommerce queries
    if ( is_admin() || ! $query->is_main_query() || ! is_woocommerce() || !is_archive() ) {
        return;
    }

	// Get the current user's ID
	$user_id = get_current_user_id();

	// Check if the user is logged in
	if ( is_user_logged_in() && !current_user_can('administrator') ) {
		// Get the 'state' meta value for the current user
		$user_state = get_user_meta( $user_id, 'state', true );

		if ( ! empty( $user_state ) ) {
			// Define the meta query
			$meta_query[] = array(
				'key'     => 'state',
				'value'   => sanitize_text_field( $user_state ),
				'compare' => '='
			);

			// Set the meta query to the query
			$query->set( 'meta_query', $meta_query );
		}
		
		$current_user = wp_get_current_user();
		if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ){
			$parent_vendor = get_user_meta($user_id, 'parent_vendor', true);
			// Set author query
			$query->set( 'author', $parent_vendor );
		}
		
	}
}

add_filter('dokan_seller_listing_args', 'meta_query_dokan_seller_listing_args', 10, 2);
function meta_query_dokan_seller_listing_args($seller_args, $requested_data){
    
	$user_id = get_current_user_id();
	$user_state = get_user_meta( $user_id, 'state', true );
	if(is_user_logged_in() && !current_user_can('administrator') ){
		$seller_args['meta_query'][] = array(
			'key'     => 'state',
			'value'   => $user_state,
			'compare' => '=',
		);
	}

	return $seller_args;
}

//Add state to product
add_action( 'dokan_new_product_added', 'add_state_to_product', 10 );
add_action( 'dokan_product_updated', 'add_state_to_product', 10 );
function add_state_to_product($product_id){
	if ( ! dokan_is_user_seller( dokan_get_current_user_id() ) ) {
		return;
	}
	
	$user_id = get_current_user_id();
	$user_state = get_user_meta( $user_id, 'state', true );
	update_post_meta( $product_id, 'state', sanitize_text_field($user_state) );
}

add_action( 'woocommerce_process_product_meta', 'save_custom_product_meta_field' );
function save_custom_product_meta_field( $product_id ) {
	$author = $_POST['dokan_product_author_override'];
	if(isset( $author )){
		$vendor_state = get_user_meta( $author, 'state', true );
		if($vendor_state != ''){
			update_post_meta( $product_id, 'state', sanitize_text_field($vendor_state) );
		}
	}else{
		$user_id = get_current_user_id();
		$user_state = get_user_meta( $user_id, 'state', true );
		if($user_state != ''){
			update_post_meta( $product_id, 'state', sanitize_text_field($user_state) );
		}
	}
}

//Display the saved product state on the admin dashboard's product editing page.
// Add the custom meta box
function add_custom_product_state_meta_box() {
    add_meta_box(
        'product_state_meta_box',
        'Product State',
        'display_product_state_meta_box',
        'product',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'add_custom_product_state_meta_box');

// Display the custom meta box content
function display_product_state_meta_box($post) {
    // Retrieve the saved product state from post meta
    $product_state = get_post_meta($post->ID, 'state', true);
    $states = us_states_data_array();
	$product_state = $states[$product_state];
	
    // Display the product state
    echo '<label for="product_state">Product State: <strong>' . esc_attr($product_state) . '</strong></label>';
}

//Remove country and state field from store settings
add_filter('dokan_seller_address_fields', 'remove_country_and_state_field_from_store_settings', 10);
function remove_country_and_state_field_from_store_settings($fields){
	unset($fields['country']);
	unset($fields['state']);
	return $fields;
}

//Filter product by user state on every pages
function filter_woocommerce_shortcode_products_query( $query_args, $attributes, $type ) {
	$user_id = get_current_user_id();
	$user_state = get_user_meta( $user_id, 'state', true );
	// Add a meta query to the query arguments
	if(is_user_logged_in() && !current_user_can('administrator') ){
		$query_args['meta_query'][] = array(
			'key'   => 'state',
			'value' => sanitize_text_field( $user_state ),
		);
		
		$current_user = wp_get_current_user();
		if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ){
			$parent_vendor = get_user_meta($user_id, 'parent_vendor', true);
			// Set author query
			$query_args['author'] = $parent_vendor;
		}
	}
    return $query_args;
}
add_filter( 'woocommerce_shortcode_products_query', 'filter_woocommerce_shortcode_products_query', 10, 3 );

add_filter( 'woocommerce_related_products', 'custom_related_products_meta_query', 10, 3 );
function custom_related_products_meta_query( $related_posts, $product_id, $args ) {
	$user_id = get_current_user_id();
	$user_state = get_user_meta( $user_id, 'state', true );
    // Define custom meta key and value
    $custom_meta_key = 'state';
    $custom_meta_value = $user_state;
	
	if(is_user_logged_in() && !current_user_can('administrator') ) {
		if ( ! empty( $related_posts ) ) {
			// Define the meta query
			$meta_query = array(
				'key'     => $custom_meta_key,
				'value'   => $custom_meta_value,
				'compare' => '='
			);

			// Get related products with the custom meta query
			$related_args = array(
				'post_type'      => 'product',
				'posts_per_page' => $args['posts_per_page'],
				'post__in'       => $related_posts,
				'post__not_in'   => array( $product_id ),
				'meta_query'     => array( $meta_query ),
				'fields'         => 'ids',
				'orderby'        => $args['orderby'],
				'order'          => $args['order'],
			);
			
			$current_user = wp_get_current_user();
			if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ){
				$parent_vendor = get_user_meta($user_id, 'parent_vendor', true);
				// Set author query
				$related_args['author'] = $parent_vendor;
			}

			// Retrieve the related products
			$related_posts = get_posts( $related_args );
		}
	}
    return $related_posts;
}

//Add license ID in new order email
add_action( 'woocommerce_email_customer_details', 'add_license_id_in_new_order', 1, 4 );
function add_license_id_in_new_order( $order, $sent_to_admin, $plain_text, $email){
	if ( ! is_a( $order, 'WC_Order' ) ) {
		return;
	}
	
	$customer_id = $order->get_customer_id();
	$license_id = get_user_meta($customer_id, 'license_id', true);
	$business_name = get_user_meta( $customer_id, 'licensed_business_name', true );
	
	if(!empty($license_id)){
		echo '<h2>Customer Licensed Business Name</h2>';
		echo '<p>'.$business_name.'</p>';
		echo '<p>License: '.$license_id.'</p>';
	}
}

add_action( 'woocommerce_order_item_meta_start', 'attach_vendor_license_id', 11, 2 );
function attach_vendor_license_id( $item_id, $order ) {
    $product_id = $order->get_product_id();

    if ( ! $product_id ) {
        return;
    }

    $vendor_id = get_post_field( 'post_author', $product_id );
    $vendor    = dokan()->vendor->get( $vendor_id );

    if ( ! $vendor->is_vendor() ) {
        return;
    }

    printf( '<br>%s: %s', esc_html__( 'Vendor License', 'dokan-lite' ), esc_html( get_user_meta($vendor_id, 'license_id', true) ) );
}

// Remove Become a Vendor Button
function remove_become_a_vendor_button() {
    remove_action( 'woocommerce_after_my_account', [ dokan()->frontend_manager->become_a_vendor, 'render_become_a_vendor_section' ] );
}
add_action( 'wp_head', 'remove_become_a_vendor_button' );


/**
 * Vendor sales rep role 
 */
add_action('admin_init', 'add_vendor_sales_rep_role_caps', 10);
function add_vendor_sales_rep_role_caps(){
	add_role('vendor_sales_rep', 'Vendor Sales Rep');
}

/**
 * Vendor sales rep menu
 */
add_filter( 'dokan_query_var_filter', 'vendor_sales_rep_menu_doc' );
function vendor_sales_rep_menu_doc( $query_vars ) {
	$user = wp_get_current_user();
	$user_roles = $user->roles;
	if( ! in_array( 'vendor_staff', $user_roles, true ) ){
		$query_vars['Vendor Sales Rep'] = 'vendor-sales-rep';
	}
    return $query_vars;
}
add_filter( 'dokan_get_dashboard_nav', 'vendor_sales_rep_menu' );
function vendor_sales_rep_menu( $urls ) {
	$user = wp_get_current_user();
	$user_roles = $user->roles;
	if( ! in_array( 'vendor_staff', $user_roles, true ) ){
		$urls['vendor-sales-rep'] = array(
			'title' => __( 'Sales Rep', 'dokan'),
			'icon'  => '<i class="fa fa-user"></i>',
			'url'   => dokan_get_navigation_url( 'vendor-sales-rep' ),
			'pos'   => 51
		);
	}
    return $urls;
}
add_action( 'dokan_load_custom_template', 'dokan_load_template' );
function dokan_load_template( $query_vars ) {
	$user = wp_get_current_user();
	$user_roles = $user->roles;
	if( ! in_array( 'vendor_staff', $user_roles, true ) ){
		if ( isset( $query_vars['vendor-sales-rep'] ) ) {
			require_once dirname( __FILE__ ). '/vendor-sales-rep.php';
		}
	}
}

/**
 * Show parent vendor field
 */
function wpdocs_custom_user_profile_fields( $profileuser ) {
	if ( ! in_array( 'vendor_sales_rep', (array) $profileuser->roles ) ) return;
	
	$parent_vendor = get_user_meta( $profileuser->ID, 'parent_vendor', true );
	$user = get_user_by('id', $parent_vendor);
	if ($user) {
		$name = $user->first_name.' '.$user->last_name;
		$email = $user->user_email;
	}
	?>
	<table class="form-table">
		<tr>
			<th>
				<label for="user_location"><?php _e( 'Parent Vendor' ); ?></label>
			</th>
			<td>
				<input type="text" readonly value="<?php echo esc_html( $name .' ('.$email.')' ); ?>" class="regular-text" />
			</td>
		</tr>
	</table>
<?php
}
add_action( 'show_user_profile', 'wpdocs_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'wpdocs_custom_user_profile_fields' );

//Add custom checkout button for sales rep
add_action( 'woocommerce_widget_shopping_cart_before_buttons', 'add_button_mini_cart_widget', 10 );
function add_button_mini_cart_widget(){
	$current_user = wp_get_current_user();
	if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ) {
	?>
		<p class="order-for-customer-btn-wrap woocommerce-mini-cart__buttons buttons"><a href="<?php echo home_url('/sales-rep-checkout'); ?>" class="button order-for-customer-btn checkout">Order For Customer</a></p>
	<?php
	}
}

add_shortcode( 'order_for_customer_btn', 'add_button_woocommerce_cart_page', 10 );
function add_button_woocommerce_cart_page(){
	ob_start();
	$current_user = wp_get_current_user();
	if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ) {
	?>
		<p class="order-for-customer-btn-wrap buttons"><a href="<?php echo home_url('/sales-rep-checkout'); ?>" class="button order-for-customer-btn cart-page-btn">Order For Customer</a></p>
	<?php
	}
	return ob_get_clean();
}

//Hide checkout page button for sales rep
add_action('wp_head', 'hide_checkout_page_button_for_sales_rep', 99);
function hide_checkout_page_button_for_sales_rep(){
	$current_user = wp_get_current_user();
	if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ) {
	?>
	<style>
		.wc-block-cart__submit.wp-block-woocommerce-proceed-to-checkout-block .wc-block-cart__submit-container,
		p.woocommerce-mini-cart__buttons.buttons a.button.checkout:not(.order-for-customer-btn){
			display: none !important
		}
		.wc-block-cart__submit.wp-block-woocommerce-proceed-to-checkout-block {
			margin: 0 !important
		}
	</style>
	<?php
	}
}

// Prevent unauthorized users from adding restricted products to the cart
function restrict_product_add_to_cart_based_on_user($passed, $product_id, $quantity, $variation_id = '', $variations = '') {
    // Get current user
    $user_id = get_current_user_id();
	$current_user = wp_get_current_user();
	$post_author = get_post_field( 'post_author', $product_id );
	
    // Get allowed parent vendor
    $parent_vendor = get_user_meta($user_id, 'parent_vendor', true);

    // Check if current user is in the allowed users list
    if (in_array( 'vendor_sales_rep', (array) $current_user->roles ) && $parent_vendor != $post_author) {
        wc_add_notice('Sorry! This is not your vendor\'s product. You are not allowed to purchase this product.', 'error');
        return false;
    }

    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'restrict_product_add_to_cart_based_on_user', 999, 5);

//Redirect sales rep from stores page to home page
function redirect_sales_rep_stores_to_home() {
	
	$current_user = wp_get_current_user();
	if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ){
		
		$store_page = 'store-listing'; //Store listing page slug
		if( is_page($store_page) ){
			wp_safe_redirect( home_url() );
        	exit;
		}
		if( is_page('checkout') ){
			wp_safe_redirect( home_url('/sales-rep-checkout/') );
        	exit;
		}
	}
}
add_action( 'wp', 'redirect_sales_rep_stores_to_home' );

//Add vendor sales rep column in my account orders page
add_filter( 'woocommerce_account_orders_columns', 'add_vendor_sales_rep_column_in_woocommerce_account_orders', 10 );
function add_vendor_sales_rep_column_in_woocommerce_account_orders($columns){
	
	$modified_columns = array(
		'order-number'  => __( 'Order', 'woocommerce' ),
		'order-date'    => __( 'Date', 'woocommerce' ),
		'order-status'  => __( 'Status', 'woocommerce' ),
		'order-total'   => __( 'Total', 'woocommerce' ),
		'order-salesrep' => __( 'Vendor Sales Rep', 'woocommerce' ),
		'order-actions' => __( 'Actions', 'woocommerce' ),
	);
	
	return $modified_columns;
}

//Display sales rep in order details page
add_action( 'woocommerce_order_details_before_order_table', 'display_sales_rep_in_customer_order_details', 10 );
function display_sales_rep_in_customer_order_details($order){
	$_sales_rep = get_post_meta($order->get_order_number(), '_sales_rep', true);
	if($_sales_rep != ''){
		$_sales_rep_obj = get_user_by('id', $_sales_rep);
		echo '<p>Vendor Sales Rep: <strong>'.$_sales_rep_obj->display_name.'</strong></p>';
	}
}

//Display sales rep name in vendor's order details page
add_action( 'dokan_order_detail_after_order_items', 'sales_rep_dokan_order_detail_after_order_items', 10 );
function sales_rep_dokan_order_detail_after_order_items($order){
	$_sales_rep = !empty(get_post_meta($order->get_id(), '_sales_rep', true)) ? get_post_meta($order->get_id(), '_sales_rep', true) : '';
	
	if( $_sales_rep != '' ){
		$_sales_rep_obj = get_user_by('id', $_sales_rep);
		echo '<p><strong>Sales Rep:</strong> '.esc_html($_sales_rep_obj->display_name).' ('.$_sales_rep_obj->user_email.')</p>';
	}
}

//Display sales rep name in new order emails
add_action( 'woocommerce_email_customer_details', 'add_sales_rep_in_woocommerce_email_order_details', 5 );
function add_sales_rep_in_woocommerce_email_order_details($order){
	$sales_rep = !empty(get_post_meta($order->get_order_number(), '_sales_rep', true)) ? get_post_meta($order->get_order_number(), '_sales_rep', true) : '';
	
	if( $sales_rep != '' ){
		$sales_rep_obj = get_user_by('id', $sales_rep);
		echo '<h2>Vendor Sales Rep: '.esc_html($sales_rep_obj->display_name).'</h2>';
	}
}

//Add Custom Page in My Account to display vendor sales rep orders
add_filter ( 'woocommerce_account_menu_items', 'sales_rep_woocommerce_account_menu_items', 40 );
function sales_rep_woocommerce_account_menu_items( $menu_links ){
	$current_user = wp_get_current_user();
	
	if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ){
		
		unset($menu_links['orders']);

		$menu_links = array_slice( $menu_links, 0, 5, true ) 
		+ array( 'customer-orders' => 'Customer Orders' )
		+ array_slice( $menu_links, 5, NULL, true );
		
	}
	return $menu_links;

}

//register permalink endpoint
add_action( 'init', 'sales_rep_orders_add_endpoint' );
function sales_rep_orders_add_endpoint() {

	add_rewrite_endpoint( 'customer-orders', EP_PAGES );

}

//Display sales rep orders in My Account
add_action( 'woocommerce_account_customer-orders_endpoint', 'customer_orders_my_account_endpoint_content' );
function customer_orders_my_account_endpoint_content() {
	$current_user = wp_get_current_user();
	if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ){
		
		$current_user_id = get_current_user_id();

		$orders = wc_get_orders( array(
			'limit'        => -1, 
			'orderby'      => 'date',
			'order'        => 'DESC',
			'status'     => array('completed', 'processing', 'on-hold', 'cancelled', 'pending'),

		) );

		// Check if orders exist
		if ( ! empty( $orders ) ) {
		?>
		<table class="vendor-sales-rep-list">
		  <thead>
			<tr>
			  	<th>Order</th>
			  	<th>Date</th>
			  	<th>Status</th>
				<th>Total</th>
				<th>Customer</th>
			</tr>
		   </thead>
		   <tbody>
			<?php
			// Loop through the order IDs
			$not_found = 0;
			foreach ( $orders as $order ) {
				$_sales_rep = get_post_meta($order->get_id(), '_sales_rep', true);
				
				if( $_sales_rep ==  $current_user_id){
					?>
				   <tr>
					   <td>#<?php echo esc_html($order->get_id()); ?></td>
					   <td><?php echo $order->get_date_created()->date('Y-m-d H:i:s'); ?></td>
					   <td><?php echo esc_html($order->get_status()); ?></td>
					   <td><?php echo wc_price($order->get_total()); ?></td>
					   <td>
						   <?php 
							$order_customer_id = $order->get_customer_id(); 
							$business_name = !empty(get_user_meta($order_customer_id, 'licensed_business_name', true)) ? get_user_meta($order_customer_id, 'licensed_business_name', true) : '';
							echo $business_name;
						   ?>
					   </td>
				 	</tr>
	   				<?php
					$not_found++;
				}
			}
			
			if( $not_found == 0 ){
				?>
			   <p>No orders found for the specified sales rep.</p>
			   <style>
				   .vendor-sales-rep-list {
					   display: none
				   }
			   </style>
			   <?php
			}
			?>
	   		</tbody>
		</table>
	   <?php
		} else {
			echo 'No orders found for the specified sales rep.';
		}
	}
}

//Remove the restriction 'vendor staff can't purchase the own vendor's product'
add_filter( 'dokan_vendor_own_product_purchase_restriction', 'remove_dokan_vendor_own_product_purchase_restriction', 99, 2 );
function remove_dokan_vendor_own_product_purchase_restriction($is_purchasable, $product){
	$current_user = wp_get_current_user();
	/*if ( in_array( 'vendor_sales_rep', (array) $current_user->roles ) ){
		$is_purchasable = true;
		
	}elseif( in_array( 'vendor_staff', (array) $current_user->roles ) || in_array( 'seller', (array) $current_user->roles ) || in_array( 'administrator', (array) $current_user->roles ) ){
		if ( isset($_GET['order_id']) ){
			$is_purchasable = true;
		}
	}*/
	$is_purchasable = true;
	return $is_purchasable;
}

//Remove the notice of 'own product not purchasable' for the staff/sales rep.
add_filter( 'dokan_is_product_author', 'dokan_is_product_author_modified_for_sales_rep', 999, 2 );
function dokan_is_product_author_modified_for_sales_rep( $user_id, $product_id ) {
	if ( ! $product_id ) {
		return $user_id;
	}

	if ( ! is_user_logged_in() ) {
		return $user_id;
	}
	
	$vendor_id  = get_current_user_id();
	$current_user = wp_get_current_user();

	if (in_array( 'vendor_sales_rep', (array) $current_user->roles )){
		return $vendor_id;
	}

	return (int) $user_id;
}

//Dokan sync insert order to recalculate the vendor earning
function custom_dokan_sync_insert_order( $order_id ) {
    global $wpdb;
	$order = wc_get_order( $order_id );
	//Remove the old order row from dokan_orders table to reset vendor earning
	$wpdb->delete(
		$wpdb->dokan_orders,
		[ 'order_id' => $order->get_id() ],
		[ '%d' ] 
	);
	
    if ( is_a( $order_id, 'WC_Order' ) ) {
        $order    = $order_id;
        $order_id = $order->get_id();
    } else {
        $order = wc_get_order( $order_id );
    }

    if ( ! $order || $order instanceof WC_Subscription ) {
        return;
    }

    if ( dokan()->order->is_order_already_synced( $order ) ) {
        //return;
    }
	
    if ( (int) $order->get_meta( 'has_sub_order', true ) === 1 ) {
        return;
    }

    $seller_id    = dokan_get_seller_id_by_order( $order->get_id() );
    $order_total  = $order->get_total();
    $order_status = 'wc-' . $order->get_status();

    if ( dokan_is_admin_coupon_applied( $order, $seller_id ) ) {
        $net_amount = dokan()->commission->get_earning_by_order( $order, 'seller' );
		
    } else {
        $net_amount = dokan()->commission->get_earning_by_order( $order );
    }

    //$net_amount    = apply_filters( 'dokan_order_net_amount', $net_amount, $order );
    $threshold_day = dokan_get_withdraw_threshold( $seller_id );

    dokan()->order->delete_seller_order( $order_id, $seller_id );

    $wpdb->insert(
        $wpdb->prefix . 'dokan_orders',
        [
            'order_id'     => $order_id,
            'seller_id'    => $seller_id,
            'order_total'  => $order_total,
            'net_amount'   => $net_amount,
            'order_status' => $order_status,
        ],
        [
            '%d',
            '%d',
            '%f',
            '%f',
            '%s',
        ]
    );

    $wpdb->insert(
        $wpdb->prefix . 'dokan_vendor_balance',
        [
            'vendor_id'    => $seller_id,
            'trn_id'       => $order_id,
            'trn_type'     => 'dokan_orders',
            'perticulars'  => 'New order',
            'debit'        => $net_amount,
            'credit'       => 0,
            'status'       => $order_status,
            'trn_date'     => dokan_current_datetime()->format( 'Y-m-d H:i:s' ),
            'balance_date' => dokan_current_datetime()->modify( "+ $threshold_day days" )->format( 'Y-m-d H:i:s' ),
        ],
        [
            '%d',
            '%d',
            '%s',
            '%s',
            '%f',
            '%f',
            '%s',
            '%s',
            '%s',
        ]
    );
}

//Remove ordr item from order
add_action( 'wp_ajax_remove_order_item_from_order', 'remove_order_item_from_order' );
function remove_order_item_from_order(){
	$order_id = $_POST['order_id'];
	$remove_product_id = $_POST['product_id'];
	$remove_item_id = $_POST['item_id'];
	// Load the order by ID
    $order = wc_get_order( $order_id );

    if ( ! $order ) {
        return;
    }
	if ( $remove_item_id == '' ) {
        return;
    }
	if( count($order->get_items()) <= 1 ){
		echo 'last_item';
		die();
		return;
	}
	
	// Remove an item from the order
    foreach ( $order->get_items() as $item_id => $item ) {
        $product_id = $item->get_product_id();
        
        // Check if it's the product you want to remove
        if ( $remove_item_id == $item_id ) {
			if( $item->get_variation_id() > 0 ){
				wc_update_product_stock( $item->get_variation_id(), $item->get_quantity(), 'increase' );
			}else{
				wc_update_product_stock( $product_id, $item->get_quantity(), 'increase' );
			}
			
			//Remove the item from order
            $order->remove_item( $item_id );
        }
    }
	
	// After making changes to the items, recalculate the order totals
    $order->calculate_totals();

    // Save the order to apply the changes
    $order->save();
	
	/* Reset vendor earning */
	//Sync insert ordet
	custom_dokan_sync_insert_order( $order->get_id() );
	
	die();
}

//Update item quentity
add_action( 'wp_ajax_update_order_item_qty', 'update_order_item_qty' );
function update_order_item_qty(){
	global $wpdb;
	
	$order_id = $_POST['order_id'];
	$edit_product_id = $_POST['product_id'];
	$new_qty = $_POST['new_qty'];
	$update_item_id = $_POST['item_id'];
	
	// Load the order by ID
    $order = wc_get_order( $order_id );

    if ( ! $order ) {
        return;
    }
	if ( $update_item_id == '' ) {
        return;
    }
	
    foreach ( $order->get_items() as $item_id => $item ) {
        $product_id = $item->get_product_id();
        
        // Check if it's the product you want to remove
        if ( $item_id == $update_item_id ) {
			$product = wc_get_product( $product_id );
			
			//Check if it's a variable product or simple product
			if( $item->get_variation_id() > 0 ){
				$product = wc_get_product( $item->get_variation_id() );
				
				$item_id = $item->get_variation_id();
			}else{
				$item_id = $product_id;
			}
			
			$old_qty = $item->get_quantity();
			
			$increase_decrease = '';
			
			if( $old_qty > $new_qty ){
				
				$increase_decrease = 'increase';
				$adjust_qty = $old_qty - $new_qty;
				
			}elseif( $old_qty < $new_qty ){
				
				$adjust_qty = $new_qty - $old_qty;
				
				$stock_quantity = $product->get_stock_quantity();
				
				if( $adjust_qty > 0 && $adjust_qty <= $stock_quantity ){
					$increase_decrease = 'decrease';
				}else {
					$increase_decrease = '';
					echo 'stock_not_available';
				}
			}
			
			if( $increase_decrease != '' && $product->get_manage_stock() ){
				wc_update_product_stock( $item_id, $adjust_qty, $increase_decrease );
			}
			
			//Update quantity
			if( $increase_decrease != '' ){
				$item->set_quantity( $new_qty );
				$item->set_subtotal( $product->get_price() * $new_qty ); // Subtotal for two items
				$item->set_total( $product->get_price() * $new_qty );
				$item->save();
			}
        }

    }
	
	// After making changes to the items, recalculate the order totals
    $order->calculate_totals();

    // Save the order to apply the changes
    $order->save();
	
	/* Reset vendor earning */
	//Sync insert ordet
	custom_dokan_sync_insert_order( $order->get_id() );
	
	die();
}

//Add new products to the order
function update_existing_order_with_cart_items() {
    $existing_order_id = $_POST['order_id'];
    $existing_order = wc_get_order( $existing_order_id );

    // Ensure the order exists and is valid
    if ( ! $existing_order ) {
        return;
    }

    // Clear existing items in the order if needed
    /*foreach ( $existing_order->get_items() as $item_id => $item ) {
        $existing_order->remove_item( $item_id );
    }*/

    // Add cart items to the existing order
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $product = wc_get_product( $cart_item['product_id'] );
        $item = new WC_Order_Item_Product();

        // Set product details
        $item->set_product( $product );
        $item->set_variation_id( isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : 0 );
        $item->set_quantity( $cart_item['quantity'] );
        $item->set_subtotal( $cart_item['line_subtotal'] );
        $item->set_total( $cart_item['line_total'] );

        // Add meta for variations if applicable
        if ( isset( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
            foreach ( $cart_item['variation'] as $key => $value ) {
                $item->add_meta_data( str_replace( 'attribute_', '', $key ), $value, true );
            }
        }
		
		//Update stock after adding products to the order
		if( isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] > 0 ){
			$product = wc_get_product( $cart_item['variation_id'] );
			$item_id = $cart_item['variation_id'];
		}else{
			$item_id = $cart_item['product_id'];
		}
		
		if( $product->get_manage_stock() ){
			wc_update_product_stock( $item_id, $cart_item['quantity'], 'decrease' );
		}

        // Add item to the existing order
        $existing_order->add_item( $item );
    }

    // Calculate totals and save
    $existing_order->calculate_totals();
    $existing_order->save();
	
	/* Reset vendor earning */
	//Sync insert ordet
	custom_dokan_sync_insert_order( $existing_order->get_id() );
	
    // Clear the cart after updating the order
    WC()->cart->empty_cart();

    die();
}
add_action( 'wp_ajax_add_cart_items_to_the_order', 'update_existing_order_with_cart_items' );

//Get vendor products
function get_vendor_products_dropdown( $current_user ) {
    if ( ! is_numeric( $current_user ) ) {
        return 'Invalid vendor ID or Dokan is not installed.';
    }
	
	//get products of parent vendor if staff is logged in
	$user = wp_get_current_user();
	if ( in_array( 'vendor_staff', (array) $user->roles ) ) {
		$vendor_id = !empty(get_user_meta($current_user, '_vendor_id', true)) ? get_user_meta($current_user, '_vendor_id', true) : 0;
	}else{
		$vendor_id = $current_user;
	}

    // Query for products by vendor ID
    $args = array(
        'post_type'      => 'product',
        'numberposts' => -1,
        'author'         => $vendor_id,
        'post_status'    => 'publish',
		'meta_query' => array(
			array(
				'key' => '_stock_status',
				'value' => 'instock',
				'compare' => '=',
			),
    	),
    );

    $vendor_products = get_posts( $args );

    // Begin dropdown output
    $output = '<select name="vendor_products" id="vendor_products">';
    $output .= '<option value="">Select a Product</option>';

    // Loop through vendor products and add them as options
    foreach ( $vendor_products as $vendor_product ) {
        
        $product_id = $vendor_product->ID;
        $product_title = $vendor_product->post_title;
		$product = wc_get_product( $product_id );
		$product_type = $product->get_type();
		
        $output .= '<option value="' . esc_attr( $product_id ) . '">' . esc_html( $product_title ) . '</option>';
    }

    // End dropdown output
    $output .= '</select>';

    return $output;
}

//Loading product to add to cart
function load_product_to_add_in_order(){
	$product_id = $_POST['product_id'];
	
	$product = wc_get_product( $product_id );
	$cookie_name = "edit_order_product_id";
	$cookie_value = $product_id;
	$expiry_time = time() + 200;

	setcookie($cookie_name, $cookie_value, $expiry_time, "/");

	die();
}
add_action('wp_ajax_load_product_to_add_in_order', 'load_product_to_add_in_order');

// Function to display custom cart items table
function custom_cart_items_table_with_variations() {
    if ( WC()->cart->is_empty() ) {
        return "<p>Your cart is now empty.</p>";
    }

    ob_start();
    ?>
    <table class="custom-cart-table" style="width:100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="padding: 10px; border: 1px solid #ddd;">Product</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Price</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Quantity</th>
                <th style="padding: 10px; border: 1px solid #ddd;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $product = $cart_item['data'];
                $product_name = $product->get_name();
                $product_price = wc_price( $product->get_price() );
                $product_quantity = $cart_item['quantity'];
                $product_total = wc_price( $cart_item['line_total'] );
                // Display each row
                ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        <?php echo esc_html( $product_name ); ?><br>
                        <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
						
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        <?php echo $product_price; ?>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        <?php echo esc_html( $product_quantity ); ?>
                    </td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        <?php echo $product_total; ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </tbody>
		<tfoot>
			<tr>
				<td><a style="margin-top:10px" href="#" class="add-new-items-to-order dokan-btn dokan-btn-theme" order-id="<?php echo esc_attr($_GET['order_id']); ?>">Add items to order</a></td>
			</tr>
		</tfoot>
    </table>
    <?php
    return ob_get_clean();
}

//Edit order customer email template
add_action('woocommerce_email_classes', 'wcadd_custom_email');
function wcadd_custom_email($email_classes) {
    // Include the custom email class file
    include_once 'class-wc-edit-order-customer-email.php';

    // Add the email class to WooCommerce email classes
    $email_classes['WC_Edit_Order_Customer_Email'] = new WC_Edit_Order_Customer_Email();

    return $email_classes;
}

//Send email notification to customer
add_action('wp_ajax_send_email_notification_to_customer', 'send_email_notification_to_customer');
function send_email_notification_to_customer(){
	$order = wc_get_order( $_POST['order_id'] );

    if ( $order ) {
        $order_id = $order->get_id();
		WC()->mailer()->emails['WC_Edit_Order_Customer_Email']->trigger($order_id);
    }
}

//Remove orders count on staff dashboard
add_filter('dokan_get_dashboard_nav', 'change_order_menu_dokan_dashboard_nav',9999);
function change_order_menu_dokan_dashboard_nav($menus){
	$current_user = wp_get_current_user();
	
	if( in_array( 'vendor_staff', $current_user->roles ) && in_array( 'vendor_sales_rep', $current_user->roles ) ) {
		$menus['orders']['counts'] = '';
	}
	
	return $menus;
}

//Remove order status count on staff dashboard
add_action('dokan_status_listing_item', 'remove_order_count_dokan_status_listing_item', 10);
function remove_order_count_dokan_status_listing_item(){
	$current_user = wp_get_current_user();
	
	if( in_array( 'vendor_staff', $current_user->roles ) && in_array( 'vendor_sales_rep', $current_user->roles ) ) {
		?>
		<script>
			jQuery(document).ready(function($) {
				$('.order-statuses-filter li a').each(function() {
					// Replace the text content, removing any numbers and parentheses at the end
					$(this).text($(this).text().replace(/\s*\(\d+\)\s*$/, ''));
				});
			});
		</script>
		<?php
	}
}

//Order export pdf generator
add_action('wp_ajax_nopriv_vendor_order_generate_pdf', 'vendor_order_generate_pdf');
add_action('wp_ajax_vendor_order_generate_pdf', 'vendor_order_generate_pdf');
function vendor_order_generate_pdf() {
    $order_id = $_POST['order_id'];
    //$post_id = $_GET['post'];
	
	// Load TCPDF library
	require_once('invoice/tcpdf.php');
	
	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('');
	$pdf->SetTitle('Order export');
	$pdf->SetSubject('Order export');
	//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(true);

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	
	$pdf->SetMargins(15, 15, 15); // Left, Top, Right margins
	$pdf->SetAutoPageBreak(true, 15); // Bottom margin
	
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set font
	//$pdf->SetFont('Dejavusans', '', 12);
	
	// add a page
	$pdf->AddPage();
	
	$html = pdf_order_export_html($order_id);

	// output the HTML content
	$pdf->writeHTML($html, true, false, true, false, '');
	
	// Determine the upload directory
    $upload_dir = wp_upload_dir();
    $pdf_dir = trailingslashit($upload_dir['basedir']) . 'invoices';

    // Create the PDF directory if it doesn't exist
    if (!file_exists($pdf_dir)) {
        mkdir($pdf_dir, 0755);
    }

    // Save the PDF to the server
    //$pdf_file = trailingslashit($pdf_dir) . 'invoice-'.$post_id.'.pdf';
    $pdf_file = trailingslashit($pdf_dir) . 'order-export.pdf';
    $pdf->Output($pdf_file, 'F');
	
	$pdf_file = trailingslashit(site_url()).'wp-content/uploads/invoices/order-export.pdf';
    // Provide a response to the client
    echo json_encode(array('success' => true, 'pdf_url' => $pdf_file, 'order_id' => $order_id));

    // Important: Terminate the script
    die();
	
}

//Include pdf html
require_once('pdf-order-export-template.php');
