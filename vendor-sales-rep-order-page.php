<?php 
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/* 
Template Name: Vendor Sales Ref Order page
*/ 

$current_user = wp_get_current_user();
if ( !in_array( 'vendor_sales_rep', (array) $current_user->roles ) ) return;

get_header();
?>
<?php if( !WC()->cart->is_empty() ) { ?>
<!-- List of added to cart items -->
<table class="vendor-sales-rep-list">
	<thead>
		<tr>
			<th>Name</th>
			<th>Price</th>
			<th>Quantity</th>
			<th>Subtotal</th>
		</tr>
	</thead>
	<tbody>
<?php
// Loop over $cart items
$added_to_Cart_items = [];
foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	$product = $cart_item['data'];
	$product_id = $cart_item['product_id'];
	$quantity = $cart_item['quantity'];
   	$variation_id = $cart_item['variation_id'];
	if($variation_id > 0){
		$added_to_Cart_items[$variation_id] = $quantity;
	}else{
		$added_to_Cart_items[$product_id] = $quantity;
	}
	?>
		<tr>
			<td>
				<?php echo esc_html(get_the_title($product_id)); ?>
				<?php
				if($variation_id > 0){
					$attributes = $product->get_attributes();
					foreach($attributes as $attribute_name => $attribute_value){
						echo '<br><span><strong>'.$attribute_name.':</strong> '.$attribute_value.'</span>';
					}
				}
				?>
			</td>
			<td><?php echo WC()->cart->get_product_price( $product ); ?></td>
			<td><?php echo esc_html($quantity); ?></td>
			<td><?php echo WC()->cart->get_product_subtotal( $product, $quantity ); ?></td>
		</tr>
	<?php
}
?>
	</tbody>
	<tfooter>
		<tr>
			<th></th>
			<th></th>
			<th>Total</th>
			<th><?php echo WC()->cart->get_total(); ?></th>
		</tr>
	</tfooter>
</table>
<?php
/* Create order by vendor sales rep */

if( isset($_POST['create_order']) && !empty(array_filter($added_to_Cart_items)) ){
	
	if ( isset( $_REQUEST['vendor_sales_rep_order_nonce_field'] ) && wp_verify_nonce( $_REQUEST['vendor_sales_rep_order_nonce_field'], 'vendor_sales_rep_order_nonce_field' ) ) :
	
	$sales_rep_id = get_current_user_id();
	$get_assigned_customers = is_array(get_user_meta( $sales_rep_id, 'assigned_customers', true )) ? get_user_meta( $sales_rep_id, 'assigned_customers', true ) : array();
	$selected_customer = $_POST['order_customer'];
	$parent_vendor = get_user_meta( $sales_rep_id, 'parent_vendor', true );
	
	if( $selected_customer != '' && in_array($selected_customer, $get_assigned_customers) ){
		$selected_customer_obj = get_user_by('id', $selected_customer);
		
		// Initialize WooCommerce order
		$order = wc_create_order();

		$products = $added_to_Cart_items;
		
		$order->set_customer_id($selected_customer_obj->ID);
		
		// Add products to the order
		foreach ($products as $product_id => $quantity) {
			$product = wc_get_product($product_id);
			if ($product) {
				$order->add_product($product, $quantity);
			}
		}
		
		$billing_phone = !empty(get_user_meta($selected_customer_obj->ID, 'billing_phone', true)) ? get_user_meta($selected_customer_obj->ID, 'billing_phone', true) : '';
		$first_name = !empty($selected_customer_obj->first_name) ? $selected_customer_obj->first_name : '';
		$last_name = !empty($selected_customer_obj->last_name) ? $selected_customer_obj->last_name : '';
		$user_email = !empty($selected_customer_obj->user_email) ? $selected_customer_obj->user_email : '';
		
		//Billing address
		$address_1 = !empty(get_user_meta( $selected_customer_obj->ID, 'billing_address_1', true )) ? get_user_meta( $selected_customer_obj->ID, 'billing_address_1', true ) : ''; 
		$address_2 = !empty(get_user_meta( $selected_customer_obj->ID, 'billing_address_2', true )) ? get_user_meta( $selected_customer_obj->ID, 'billing_address_2', true ) : '';
		$city = !empty(get_user_meta( $selected_customer_obj->ID, 'billing_city', true )) ? get_user_meta( $selected_customer_obj->ID, 'billing_city', true ) : '';
		$state = !empty(get_user_meta( $selected_customer_obj->ID, 'billing_state', true )) ? get_user_meta( $selected_customer_obj->ID, 'billing_state', true ) : '';
		$postcode = !empty(get_user_meta( $selected_customer_obj->ID, 'billing_postcode', true )) ? get_user_meta( $selected_customer_obj->ID, 'billing_postcode', true ) : '';
		$billing_country = !empty(get_user_meta( $selected_customer_obj->ID, 'billing_country', true )) ? get_user_meta( $selected_customer_obj->ID, 'billing_country', true ) : '';
		
		// Set billing and shipping address
		$address = array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'company'    => '',
			'email'      => $user_email,
			'phone'      => $billing_phone,
			'address_1'  => $address_1,
			'address_2'  => $address_2,
			'city'       => $city,
			'state'      => $state,
			'postcode'   => $postcode,
			'country'    => $billing_country,
		);

		$order->set_address($address, 'billing');
		$order->set_address($address, 'shipping');

		// Set payment method and status
		$order->set_payment_method('cod');
		$order->set_payment_method_title('Cash On Delivery');
		
		// Calculate totals
		$order->calculate_totals();

		// Set order status
		$order->update_status('processing'); // Possible statuses: 'pending', 'processing', 'completed', etc.

		// Reduce stock for each item in the order
		wc_reduce_stock_levels($order->get_id());
		
		update_post_meta($order->get_id(), '_sales_rep', $sales_rep_id);
		update_post_meta($order->get_id(), '_dokan_vendor_id', $parent_vendor);

		// Send notification email to the vendor sales rep
		$sales_rep_obj = get_user_by('id', $sales_rep_id);
		$to = $sales_rep_obj->user_email;
		$subject = 'Your '.get_bloginfo('name').' order has been received!';
		$message = 'Hi '.$sales_rep_obj->first_name.'<br>';
		$message .= 'Just to let you know — we\'ve received your order #'.$order->get_id();
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		wp_mail( $to, $subject, $message, $headers, array( '' ) );
		
		$order_id = $order->get_id();
		
		//Maybe split orders
		dokan()->order->maybe_split_orders( $order_id );
		// insert on dokan sync table
		dokan_sync_insert_order( $order_id );
		
		if($order_id) {
			//Empty cart
			WC()->cart->empty_cart();
			
			// Send notification email to the customer and admin
			//WC()->mailer()->emails['WC_Email_Customer_Processing_Order']->trigger($order_id);
			
			wp_redirect(home_url('sales-rep-order-confirmation/?success=true&order='.$order_id.''));
    		exit();
		}
	}
	endif;
}

?>
<!-- User selection form -->
<form action="" method="post" class="sales-rep-create-order-form">
	<label for="order_customer">Create order for customer: </label>
	<select name="order_customer" id="order_customer" required>
		<option value="">-Select customer-</option>
		<?php
		$user_id = get_current_user_id();
		$get_assigned_customers = is_array(get_user_meta( $user_id, 'assigned_customers', true )) ? get_user_meta( $user_id, 'assigned_customers', true ) : array();
		if(!empty(array_filter($get_assigned_customers))){
			foreach($get_assigned_customers as $customer){
				$business_name = !empty(get_user_meta($customer, 'licensed_business_name', true)) ? get_user_meta($customer, 'licensed_business_name', true) : '';
				echo '<option value="'.$customer.'">'.$business_name.' (ID: '.$customer.')</option>';
			}
		}
		?>
	</select>
	<input type="hidden" id="vendor_sales_rep_order_nonce_field" name="vendor_sales_rep_order_nonce_field" value="<?php echo wp_create_nonce('vendor_sales_rep_order_nonce_field') ?>">
	<input type="submit" name="create_order" class="btn" value="Create order" />
</form>

<?php  
}else{
	echo '<h2 style="text-align:center;margin-bottom:50px;margin-top:50px">Your cart is currently empty!</h2>';
}
?>

<?php get_footer(); ?>