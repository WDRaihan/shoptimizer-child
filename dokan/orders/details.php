<?php
global $woocommerce, $wpdb;

if ( ! dokan_is_seller_has_order( dokan_get_current_user_id(), $order_id ) ) {
    echo '<div class="dokan-alert dokan-alert-danger">' . esc_html__( 'Access denied! You do not have access to this customer\'s orders. If you think this is an error, please contact the store admin.', 'dokan-lite' ) . '</div>';
    return;
}

$statuses = wc_get_order_statuses();
$order    = wc_get_order( $order_id ); // phpcs:ignore
$hide_customer_info = dokan_get_option( 'hide_customer_info', 'dokan_selling', 'off' );	
$current_user = wp_get_current_user();

/*Vendor staff + sales rep user can see/edit orders of assigned customers*/
$user_id = get_current_user_id();
$get_assigned_customers = is_array(get_user_meta( $user_id, 'assigned_customers', true )) ? get_user_meta( $user_id, 'assigned_customers', true ) : array();
$customer_user_id = $order->get_user_id();

if( in_array( 'vendor_staff', $current_user->roles ) && in_array( 'vendor_sales_rep', $current_user->roles) ){
	
	if( ! in_array( $customer_user_id, $get_assigned_customers ) ){
		echo '<div class="dokan-alert dokan-alert-danger">' . esc_html__( 'Access denied! You do not have access to this customer\'s orders. If you think this is an error, please contact the store admin.', 'dokan-lite' ) . '</div>';
		return;
	}
}

if ( $order->get_status() == 'processing' && isset($_GET['edit_order']) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'dokan_view_order' ) ) {
	
	//Update order items
	if( isset( $_POST['edit_order'] ) ){
		$order    = wc_get_order( $order_id );
		//Remove old order items
		foreach ( $order->get_items() as $item_id => $item ) {
			$product_id = $item->get_product_id();

			$order->remove_item( $item_id );
		}
		
		//Add new items
		$product_ids = $_POST['product_id'];
		//print_r($product_ids);
		if(is_array( $product_ids )){
			$product_ids = array_unique($product_ids);
			
			foreach( $product_ids as $product_id ){
				//Add a new item to the order
				$product_to_add = wc_get_product( $product_id );
				$product_qty = $_POST['product_'.$product_id.'_qty'];
				$product_qty = array_sum($product_qty);
				
				if ( $product_to_add ) {
					$item = new WC_Order_Item_Product();
					$item->set_product( $product_to_add );
					$item->set_quantity( $product_qty ); // Set the quantity to be added
					$item->set_subtotal( $product_to_add->get_price() * $product_qty ); // Subtotal for two items
					$item->set_total( $product_to_add->get_price() * $product_qty ); // Total for two items

					// Add the new item to the order
					$order->add_item( $item );
					//wc_update_product_stock($product_id, $product_qty);
				}
			}
			//wc_maybe_reduce_stock_levels( $order_id );
			wc_reduce_stock_levels( $order_id );
		}
		
		
		// After making changes to the items, recalculate the order totals
		$order->calculate_totals();
		// Save the order to apply the changes
		$order->save();
	}
	
		
	?>
	<?php
	echo '<a class="dokan-btn" href="'
		. esc_url( wp_nonce_url( add_query_arg( [ 'order_id' => $order->get_id() ], dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) )
		. '"><strong>← Order Details</strong></a>';
	?>
	<form method="post" action="">
	<div style="background: #ddd;" class="dokan-panel-heading"><strong>Order#<?php echo esc_attr($_GET['order_id']); ?></strong> → Edit Order Items</div>
	<table class="dokan-table order-items">
		<thead>
		<tr style="background-color: #f2f2f2;">
			<th style="padding: 10px; border: 1px solid #ddd;">Item</th>
			<th style="padding: 10px; border: 1px solid #ddd;">Price</th>
			<th style="padding: 10px; border: 1px solid #ddd;">Order Qty</th>
			<th style="padding: 10px; border: 1px solid #ddd;">Total</th>
			<th style="padding: 10px; border: 1px solid #ddd;">Stock</th>
			<th style="padding: 10px; border: 1px solid #ddd;">Edit Qty</th>
			<th style="padding: 10px; border: 1px solid #ddd;">Action</th>
		</tr>
		</thead>
		<tbody id="order_items_list">
		<?php
		// List order items
		$order_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', array( 'line_item', 'fee' ) ) );
		$total_items = count($order_items);
	
		foreach ( $order_items as $item_id => $item ) {
			if( $item['type'] == 'line_item' ) {
				$_product = $item->get_product();
				?>
				<tr class="item <?php echo ! empty( $class ) ? esc_attr( $class ) : ''; ?>" data-order_item_id="<?php echo esc_attr( $item_id ); ?>">
					<td style="padding: 10px; border: 1px solid #ddd;" class="name" style="">
						<?php if ( $_product ) : ?>
							<a target="_blank" href="<?php echo esc_url( get_permalink( $_product->get_id() ) ); ?>">
								<?php echo esc_html( $item['name'] ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( $item['name'] ); ?>
						<?php endif; ?>
						
						<?php if( $variation_meta = $item->get_formatted_meta_data() ) : ?>
							<div class="item-variations">
							<?php foreach ( $variation_meta as $meta_id => $meta ) : ?>
								<p class="order-product-variation" style="color: gray">
									<span style="color: #444"><?php echo $meta->display_key . ':'; ?></span>
									<?php echo wp_kses_post( force_balance_tags( $meta->display_value ) ); ?>
								</p>
							<?php endforeach; ?>
							</div>
						<?php endif; ?>

					<?php do_action( 'woocommerce_before_order_itemmeta', $item_id, $item, $_product ) ?>
						
						<small>
							<?php
							if ( $_product && $_product->get_sku() ) {
								echo '<br>' . esc_html( $_product->get_sku() );
							}
							?>
						</small>

					</td>
					<td style="padding: 10px; border: 1px solid #ddd;">
						<?php
							if ( isset( $item['line_total'] ) ) {
								if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] !== $item['line_total'] ) {
									echo '<del>' . wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) ) . '</del> ';
								}
								echo wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => dokan_get_prop( $order, 'get_order_currency', 'get_currency' ) ) );
							}
						?>
					</td>

					<?php do_action( 'woocommerce_admin_order_item_values', $_product, $item, absint( $item_id ) ); ?>
					
					<td style="padding: 10px; border: 1px solid #ddd;">
						<?php echo esc_attr( $item['qty'] ); ?>
					</td>
					<td style="padding: 10px; border: 1px solid #ddd;" class="line_cost" style="">
						<?php
						if ( isset( $item['line_total'] ) ) {
							if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] !== $item['line_total'] ) {
								echo wp_kses_post( '<del>' . wc_price( $item['line_subtotal'] ) . '</del> ' );
							}

							echo wp_kses_post( wc_price( $item['line_total'], [ 'currency' => $order->get_currency() ] ) );
						}
						?>
					</td>
					<td style="padding: 10px; border: 1px solid #ddd;">
						<?php
						$product = wc_get_product( $_product->get_id() );
						$stock = '';
						$stock_quantity = 0;
						// check if stock is managed on a product level
						if( $product->get_manage_stock() ) {
							$stock_quantity = $product->get_stock_quantity();
							if( $stock_quantity > 0 ){
								$stock = "In stock: $stock_quantity";
							}else{
								$stock = "Out of stock";
							}
						} else {

							$stock_status = $product->get_stock_status();
							if( 'instock' === $stock_status ) {
								$stock = 'In stock';
								$stock_quantity = 99;
							}
							if( 'outofstock' === $stock_status ) {
								$stock = 'Out of stock';
								$stock_quantity = 0;
							}
							if( 'onbackorder' === $stock_status ) {
								$stock = 'On back order';
								$stock_quantity = 99;
							}
						}
						?>
						<?php echo esc_html($stock); ?>
					</td>
					<td style="padding: 10px; border: 1px solid #ddd;">
						<?php
						$product = wc_get_product( $_product->get_id() );
						$stock = '';
						$stock_quantity = 0;
						// check if stock is managed on a product level
						if( $product->get_manage_stock() ) {
							$stock_quantity = $product->get_stock_quantity();
							if( $stock_quantity > 0 ){
								$stock = "In stock: $stock_quantity";
							}else{
								$stock = "Out of stock";
							}
						} else {

							$stock_status = $product->get_stock_status();
							if( 'instock' === $stock_status ) {
								$stock = 'In stock';
								$stock_quantity = 99;
							}
							if( 'outofstock' === $stock_status ) {
								$stock = 'Out of stock';
								$stock_quantity = 0;
							}
							if( 'onbackorder' === $stock_status ) {
								$stock = 'On back order';
								$stock_quantity = 99;
							}
						}
						?>
						<div class="edit-qty-wrap">
							<input type="hidden" name="product_id" value="<?php echo esc_attr( $item['product_id'] ); ?>">
							<input style="width:60px" min="1" max="<?php //echo esc_attr($stock_quantity); ?>" type="number" name="product_qty" value="<?php echo esc_attr( $item['qty'] ); ?>">
							<a href="#" class="update-line-item button custom-design" item-id="<?php echo esc_attr($item_id); ?>" order-id="<?php echo esc_attr($_GET['order_id']); ?>">Update item</a>
						</div>
					</td>
					<td style="padding: 10px; border: 1px solid #ddd;">
						<?php if( $total_items > 1 ){ ?>
						<a class="remove-item dokan-btn-theme custom-design button" order-id="<?php echo esc_attr($_GET['order_id']); ?>" item-id="<?php echo esc_attr($item_id); ?>" product-id="<?php echo esc_attr( $item['product_id'] ); ?>" href="#">Remove</a>
						<?php }else{
							?>
						<a class="dokan-btn-theme custom-design disabled button" href="#">Remove</a>
						<?php
						} 
						?>
					</td>
				</tr>
			<?php
			}
		}
		?>
		</tbody>
		
		<!--<div>
			<input type="submit" name="edit_order" value="Edit Order Items">
		</div> -->
	</table>
	</form>

	<div class="edit-order-page-btns">
	<?php
	echo '<a class="button custom-design" href="'
		. esc_url( wp_nonce_url( add_query_arg( [ 'order_id' => $order->get_id(), 'add_new_product'=>'add' ], dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) )
		. '"><strong>Add new products to this order</strong></a>';
	?>
	<a href="#" order-id="<?php echo esc_attr($_GET['order_id']); ?>" class="send-email-to-customer button custom-design dokan-btn-theme">Send Email Notification to Customer</a>
	</div>
<script>
jQuery(document).ready(function(){
	//Remove an order item from the existing order
	jQuery('.remove-item').on('click', function(e){
		e.preventDefault();
		var product_id = jQuery(this).attr('product-id');
		var item_id = jQuery(this).attr('item-id');
		var order_id = jQuery(this).attr('order-id');
		if( product_id != '' && order_id != '' ){
		jQuery.ajax({
			type: 'post',
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			data: {
				action: 'remove_order_item_from_order',
				product_id: product_id,
				order_id: order_id,
				item_id: item_id,
			},
			success: function(response){
				if( response == 'last_item' ){
					alert('Cannot remove the last line item. Please cancel the order.');
				}else{
					window.location.reload();
				}
			},
			error: function(){
				console.log('Error');
			}
		});
		}
	});
	
	//Update qty
	jQuery('.update-line-item').on('click', function(e){
		e.preventDefault();
		var order_id = jQuery(this).attr('order-id');
		var product_id = jQuery(this).parent('.edit-qty-wrap').find('input[name="product_id"]').val();
		var new_qty = jQuery(this).parent('.edit-qty-wrap').find('input[name="product_qty"]').val();
		var item_id = jQuery(this).attr('item-id');
		
		if( new_qty > 0 ){
		jQuery.ajax({
			type: 'post',
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			data: {
				action: 'update_order_item_qty',
				order_id: order_id,
				new_qty: new_qty,
				product_id: product_id,
				item_id: item_id,
			},
			success: function(response){
				var fullUrl = window.location.href;
				if( response == 'stock_not_available' ){
					alert('Sorry! The quantity is invalid. Please check the quantity.');
				}else{
					alert('Order is saved.');
					window.location.href = fullUrl;
				}
			},
			error: function(){
				console.log('Error');
			}
		});
		}else{
			alert('Minimum quantity allowed is 1');
		}
	});
	
	//send-email-to-customer
	jQuery('.send-email-to-customer').on('click', function(e){
		e.preventDefault();
		var order_id = jQuery(this).attr('order-id');

		if( order_id != '' ){
		jQuery.ajax({
			type: 'post',
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			data: {
				action: 'send_email_notification_to_customer',
				order_id: order_id,
			},
			success: function(response){
				alert('Email notification has been sent successfully.');
			},
			error: function(){
				console.log('Error');
			}
		});
		}
	});
});
</script>

<?php
}elseif( $order->get_status() == 'processing' && isset($_GET['add_new_product']) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'dokan_view_order' ) ){
?>
<div>
	<h5>
		<a class="dokan-btn edit-order-page-url" href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'order_id' => $order->get_id(), 'edit_order'=>'edit' ], dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) ); ?>">Back to edit order</a>
	</h5>
	
	<h4>Your cart</h4>
	<?php 
	echo custom_cart_items_table_with_variations();
	echo '<hr>';
	
	echo '<h4>Select a product to add to cart</h4>';
	echo get_vendor_products_dropdown( get_current_user_id() );
	
	$cookie_name = "edit_order_product_id";
	if (isset($_COOKIE[$cookie_name])) {
		echo '<hr>';
		echo '<h4>Select product, variant, and quantity below</h4>';
		$variable_product_id = $_COOKIE[$cookie_name];
		echo do_shortcode('[product_page id="'.$variable_product_id.'"]');
	}
	?>
</div>
<script>
jQuery(document).ready(function(){
	//Display product to add to cart
	jQuery('#vendor_products').on('change', function(e){
		e.preventDefault();
		var product_id = jQuery('#vendor_products').val();
		if( product_id != '' ){
		jQuery.ajax({
			type: 'post',
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			data: {
				action: 'load_product_to_add_in_order',
				product_id: product_id,
			},
			success: function(response){
				window.location.reload();
			},
			error: function(){
				console.log('Error');
			}
		});
		}
	});

	//Add new items to the order
	jQuery('.add-new-items-to-order').on('click', function(e){
		e.preventDefault();
		var order_id = jQuery(this).attr('order-id');
		if( order_id != '' ){
		jQuery.ajax({
			type: 'post',
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			data: {
				action: 'add_cart_items_to_the_order',
				order_id: order_id,
			},
			success: function(response){
				var fullUrl = jQuery('.edit-order-page-url').attr('href');
				alert('New items added to the order.');
				window.location.href = fullUrl;
			},
			error: function(){
				console.log('Error');
			}
		});
		}
	});
	
	
});
</script>
<?php
}else{
?>
<div class="dokan-clearfix dokan-order-details-wrap">
    <div class="dokan-w8 dokan-order-left-content">

        <div class="dokan-clearfix">
            <div class="" style="width:100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php printf( esc_html__( 'Order', 'dokan-lite' ) . '#%d', esc_attr( $order->get_id() ) ); ?></strong> &rarr; <?php esc_html_e( 'Order Items', 'dokan-lite' ); ?></div>
                    <div class="dokan-panel-body" id="woocommerce-order-items">
                        <?php
                        if ( function_exists( 'dokan_render_order_table_items' ) ) {
                            dokan_render_order_table_items( $order_id );
                        } else {
                            ?>
                            <table class="dokan-table order-items">
                                <thead>
                                <tr>
                                    <th class="item" colspan="2"><?php esc_html_e( 'Item', 'dokan-lite' ); ?></th>

                                    <?php do_action( 'woocommerce_admin_order_item_headers', $order ); ?>
                                    <th>Price</th>
                                    <th class="quantity"><?php esc_html_e( 'Qty', 'dokan-lite' ); ?></th>

                                    <th class="line_cost"><?php esc_html_e( 'Totals', 'dokan-lite' ); ?></th>
                                </tr>
                                </thead>
                                <tbody id="order_items_list">
                                <?php
                                // List order items
                                $order_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', array( 'line_item', 'fee' ) ) );

                                foreach ( $order_items as $item_id => $item ) {
                                    switch ( $item['type'] ) {
                                        case 'line_item':
                                            $_product = $item->get_product();
                                            dokan_get_template_part(
                                                'orders/order-item-html', '', array(
                                                    'order' => $order,
                                                    'item_id' => $item_id,
                                                    '_product' => $_product,
                                                    'item'     => $item,
                                                )
                                            );
                                            break;
                                        case 'fee':
                                            dokan_get_template_part(
                                                'orders/order-fee-html', '', array(
                                                    'item_id' => $item_id,
                                                )
                                            );
                                            break;
                                    }

                                    do_action( 'woocommerce_order_item_' . $item['type'] . '_html', $item_id, $item, $order );
                                }
                                ?>
                                </tbody>

                                <tfoot>
                                <?php
                                if ( $totals = $order->get_order_item_totals() ) { // phpcs:ignore
                                    foreach ( $totals as $total ) {
                                        ?>
                                        <tr>
                                            <th colspan="3"><?php echo wp_kses_data( $total['label'] ); ?></th>
                                            <td colspan="2" class="value"><?php echo wp_kses_post( $total['value'] ); ?></td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                                </tfoot>

                            </table>

                            <?php
                            $coupons = $order->get_items( 'coupon' );

                            if ( $coupons ) {
                                ?>
                                <table class="dokan-table order-items">
                                    <tr>
                                        <th><?php esc_html_e( 'Coupons', 'dokan-lite' ); ?></th>
                                        <td>
                                            <ul class="list-inline">
                                                <?php
                                                foreach ( $coupons as $item_id => $item ) {
                                                    $post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $item['name'] ) ); // phpcs:ignore
                                                    $link    = dokan_get_coupon_edit_url( $post_id ); // phpcs:ignore

                                                    echo '<li><a data-html="true" class="tips code" title="' . esc_attr( wc_price( $item['discount_amount'] ) ) . '" href="' . esc_url( $link ) . '"><span>' . esc_html( $item['name'] ) . '</span></a></li>';
                                                }
                                                ?>
                                            </ul>
                                        </td>
                                    </tr>
                                </table>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
				<?php
	  			if( $order->get_status() == 'processing' ){
					echo '<a class="button edit-order custom-design dokan-btn-theme" style="margin-bottom:10px" href="'
					. esc_url( wp_nonce_url( add_query_arg( [ 'order_id' => $order->get_id(), 'edit_order'=>'edit' ], dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' ) )
					. '"><strong>Edit Order</strong></a>';
				}
				?>
            </div>

            <?php do_action( 'dokan_order_detail_after_order_items', $order ); ?>
			
			<div class="dokan-left dokan-order-billing-address">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Customer Licensed Business Name', 'dokan-lite' ); ?></strong></div>
                    <div class="dokan-panel-body">
                        <?php 
						$customer_id = $order->get_customer_id();
						$license_id = get_user_meta($customer_id, 'license_id', true);
						$business_name = get_user_meta( $customer_id, 'licensed_business_name', true );

						if(!empty($license_id)){
							echo '<p>'.$business_name.'</p>';
							echo '<p>License: '.$license_id.'</p>';
						}
						?>
                    </div>
                </div>
            </div>
			
            <div class="dokan-left dokan-order-billing-address">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Billing Address', 'dokan-lite' ); ?></strong></div>
                    <div class="dokan-panel-body">
                        <?php
                        if ( $order->get_formatted_billing_address() ) {
                            echo wp_kses_post( $order->get_formatted_billing_address() );
                        } else {
                            esc_html_e( 'No billing address set.', 'dokan-lite' );
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="dokan-left dokan-order-shipping-address">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Shipping Address', 'dokan-lite' ); ?></strong></div>
                    <div class="dokan-panel-body">
                        <?php
                        if ( $order->get_formatted_shipping_address() ) {
                            echo wp_kses_post( $order->get_formatted_shipping_address() );
                        } else {
                            esc_html_e( 'No shipping address set.', 'dokan-lite' );
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="clear"></div>

            <div class="" style="width: 100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Downloadable Product Permission', 'dokan-lite' ); ?></strong></div>
                    <div class="dokan-panel-body">
                        <?php
                        dokan_get_template_part( 'orders/downloadable', '', array( 'order' => $order ) );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dokan-w4 dokan-order-right-content">
        <div class="dokan-clearfix">
            <div class="" style="width:100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php esc_html_e( 'General Details', 'dokan-lite' ); ?></strong></div>
                    <div class="dokan-panel-body general-details">
                        <ul class="list-unstyled order-status">
                            <li>
                                <span><?php esc_html_e( 'Order Status:', 'dokan-lite' ); ?></span>
                                <label class="dokan-label dokan-label-<?php echo esc_attr( dokan_get_order_status_class( $order->get_status() ) ); ?>"><?php echo esc_html( dokan_get_order_status_translated( $order->get_status() ) ); ?></label>

                                <?php if ( current_user_can( 'dokan_manage_order' ) && dokan_get_option( 'order_status_change', 'dokan_selling', 'on' ) === 'on' && $order->get_status() !== 'cancelled' && $order->get_status() !== 'refunded' ) { ?>
                                    <a href="#" class="dokan-edit-status"><small><?php esc_html_e( '&nbsp; Edit', 'dokan-lite' ); ?></small></a>
                                <?php } ?>
                            </li>
                            <?php if ( current_user_can( 'dokan_manage_order' ) && dokan_get_option( 'order_status_change', 'dokan_selling', 'on' ) === 'on' && $order->get_status() !== 'cancelled' && $order->get_status() !== 'refunded' ) : ?>
                                <li class="dokan-hide">
                                    <form id="dokan-order-status-form" action="" method="post">

                                        <select id="order_status" name="order_status" class="form-control">
                                            <?php
                                            foreach ( $statuses as $status => $label ) { // phpcs:ignore
                                                echo '<option value="' . esc_attr( $status ) . '" ' . selected( $status, 'wc-' . $order->get_status(), false ) . '>' . esc_html( $label ) . '</option>';
                                            }
                                            ?>
                                        </select>

                                        <input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
                                        <input type="hidden" name="action" value="dokan_change_status">
                                        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'dokan_change_status' ) ); ?>">
                                        <input type="submit" class="dokan-btn dokan-btn-success dokan-btn-sm" name="dokan_change_status" value="<?php esc_attr_e( 'Update', 'dokan-lite' ); ?>">

                                        <a href="#" class="dokan-btn dokan-btn-default dokan-btn-sm dokan-cancel-status"><?php esc_html_e( 'Cancel', 'dokan-lite' ); ?></a>
                                    </form>
                                </li>
                            <?php endif ?>

                            <li>
                                <span><?php esc_html_e( 'Order Date:', 'dokan-lite' ); ?></span>
                                <?php echo esc_html( dokan_get_date_created( $order ) ); ?>
                            </li>
                            <li class="earning-from-order">
                                <span><?php esc_html_e( 'Earning From Order:', 'dokan-lite' ); ?></span>
                                <?php echo wp_kses_post( wc_price( dokan()->commission->get_earning_by_order( $order ) ) ); ?>
                            </li>
                        </ul>
                        <?php if ( 'off' === $hide_customer_info && ( $order->get_formatted_billing_address() || $order->get_formatted_shipping_address() ) ) : ?>
                            <ul class="list-unstyled customer-details">
                                <li>
                                    <span><?php esc_html_e( 'Customer:', 'dokan-lite' ); ?></span>
                                    <?php 
	  								$user_id   = $order->get_user_id();
					    			echo esc_html(get_user_meta( $user_id, 'licensed_business_name', true )); //echo esc_html( $order->get_formatted_billing_full_name() ); ?><br>
                                </li>
                                <li>
                                    <span><?php esc_html_e( 'Email:', 'dokan-lite' ); ?></span>
                                    <?php echo esc_html( $order->get_billing_email() ); ?>
                                </li>
                                <li>
                                    <span><?php esc_html_e( 'Phone:', 'dokan-lite' ); ?></span>
                                    <?php echo esc_html( $order->get_billing_phone() ); ?>
                                </li>
                                <li>
                                    <span><?php esc_html_e( 'Customer IP:', 'dokan-lite' ); ?></span>
                                    <a href="<?php echo esc_url( 'https://tools.keycdn.com/geo?host=' . $order->get_customer_ip_address() ); ?>" target="_blank">
                                        <?php echo esc_html( $order->get_customer_ip_address() ); ?>
                                    </a>
                                </li>

                                <?php do_action( 'dokan_order_details_after_customer_info', $order ); ?>
                            </ul>
                        <?php endif; ?>
                        <?php
                        if ( get_option( 'woocommerce_enable_order_comments' ) !== 'no' ) {
                            $customer_note = $order->get_customer_note();
                            if ( ! empty( $customer_note ) ) {
                                ?>
                                <div class="alert alert-success customer-note">
                                    <strong><?php esc_html_e( 'Customer Note:', 'dokan-lite' ); ?></strong><br>
                                    <?php echo wp_kses_post( $customer_note ); ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php do_action( 'dokan_order_detail_after_order_general_details', $order ); ?>

            <div class="" style="width:100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php esc_html_e( 'Order Notes', 'dokan-lite' ); ?></strong></div>
                    <div class="dokan-panel-body" id="dokan-order-notes">
                        <?php
                        $args = [
                            'post_id' => $order_id,
                            'approve' => 'approve',
                            'type'    => 'order_note',
                            'status'  => 1,
                        ];

                        remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
                        $notes = get_comments( $args );

                        echo '<ul class="order_notes list-unstyled">';

                        if ( $notes ) {
                            foreach ( $notes as $note ) {
                                $note_classes = get_comment_meta( $note->comment_ID, 'is_customer_note', true ) ? array( 'customer-note', 'note' ) : array( 'note' );

                                ?>
                                <li rel="<?php echo esc_attr( absint( $note->comment_ID ) ); ?>" class="<?php echo esc_attr( implode( ' ', $note_classes ) ); ?>">
                                    <div class="note_content">
                                        <?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
                                    </div>
                                    <p class="meta">
                                        <?php
                                        // translators: 1) human-readable date
                                        printf( esc_html__( 'added %s ago', 'dokan-lite' ), esc_textarea( human_time_diff( dokan_current_datetime()->setTimezone( new DateTimeZone( 'UTC' ) )->modify( $note->comment_date_gmt )->getTimestamp(), time() ) ) );
                                        ?>
                                        <?php if ( current_user_can( 'dokan_manage_order_note' ) ) : ?>
                                            <a href="#" class="delete_note"><?php esc_html_e( 'Delete note', 'dokan-lite' ); ?></a>
                                        <?php endif ?>
                                    </p>
                                </li>
                                <?php
                            }
                        } else {
                            echo '<li>' . esc_html__( 'There are no notes for this order yet.', 'dokan-lite' ) . '</li>';
                        }

                        echo '</ul>';

                        add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
                        ?>
                        <div class="add_note">
                            <?php if ( current_user_can( 'dokan_manage_order_note' ) ) : ?>
                                <h4><?php esc_html_e( 'Add note', 'dokan-lite' ); ?></h4>
                                <form class="dokan-form-inline" id="add-order-note" role="form" method="post">
                                    <p>
                                        <textarea type="text" id="add-note-content" name="note" class="form-control" cols="19" rows="3"></textarea>
                                    </p>
                                    <div class="clearfix">
                                        <div class="order_note_type dokan-form-group">
                                            <select name="note_type" id="order_note_type" class="dokan-form-control">
                                                <option value="customer"><?php esc_html_e( 'Customer note', 'dokan-lite' ); ?></option>
                                                <option value=""><?php esc_html_e( 'Private note', 'dokan-lite' ); ?></option>
                                            </select>
                                        </div>

                                        <input type="hidden" name="security" value="<?php echo esc_attr( wp_create_nonce( 'add-order-note' ) ); ?>">
                                        <input type="hidden" name="delete-note-security" id="delete-note-security" value="<?php echo esc_attr( wp_create_nonce( 'delete-order-note' ) ); ?>">
                                        <input type="hidden" name="post_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
                                        <input type="hidden" name="action" value="dokan_add_order_note">
                                        <input type="submit" name="add_order_note" class="add_note btn btn-sm btn-theme dokan-btn-theme" value="<?php esc_attr_e( 'Add Note', 'dokan-lite' ); ?>">
                                    </div>
                                </form>
                            <?php endif; ?>

                            <?php if ( ! dokan()->is_pro_exists() || 'on' !== dokan_get_option( 'enabled', 'dokan_shipping_status_setting' ) ) : ?>
                                <div class="clearfix dokan-form-group" style="margin-top: 10px;">
                                    <!-- Trigger the modal with a button -->
                                    <input type="button" id="dokan-add-tracking-number" name="add_tracking_number" class="dokan-btn dokan-btn-success" value="<?php esc_attr_e( 'Tracking Number', 'dokan-lite' ); ?>">

                                    <form id="add-shipping-tracking-form" method="post" class="dokan-hide" style="margin-top: 10px;">
                                        <div class="dokan-form-group">
                                            <label class="dokan-control-label"><?php esc_html_e( 'Shipping Provider Name / URL', 'dokan-lite' ); ?></label>
                                            <input type="text" name="shipping_provider" id="shipping_provider" class="dokan-form-control" value="">
                                        </div>

                                        <div class="dokan-form-group">
                                            <label class="dokan-control-label"><?php esc_html_e( 'Tracking Number', 'dokan-lite' ); ?></label>
                                            <input type="text" name="tracking_number" id="tracking_number" class="dokan-form-control" value="">
                                        </div>

                                        <div class="dokan-form-group">
                                            <label class="dokan-control-label"><?php esc_html_e( 'Date Shipped', 'dokan-lite' ); ?></label>
                                            <input type="text" name="shipped_date" id="shipped-date" class="dokan-form-control" value="" placeholder="<?php echo esc_attr( get_option( 'date_format' ) ); ?>">
                                        </div>
                                        <input type="hidden" name="security" id="security" value="<?php echo esc_attr( wp_create_nonce( 'add-shipping-tracking-info' ) ); ?>">
                                        <input type="hidden" name="post_id" id="post-id" value="<?php echo esc_attr( $order->get_id() ); ?>">
                                        <input type="hidden" name="action" id="action" value="dokan_add_shipping_tracking_info">

                                        <div class="dokan-form-group">
                                            <input id="add-tracking-details" type="button" class="btn btn-primary" value="<?php esc_attr_e( 'Add Tracking Details', 'dokan-lite' ); ?>">
                                            <button type="button" class="btn btn-default" id="dokan-cancel-tracking-note"><?php esc_html_e( 'Close', 'dokan-lite' ); ?></button>
                                        </div>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div> <!-- .add_note -->
                    </div> <!-- .dokan-panel-body -->
                </div> <!-- .dokan-panel -->
            </div>

            <?php do_action( 'dokan_order_detail_after_order_notes', $order ); ?>

        </div> <!-- .row -->
    </div> <!-- .col-md-4 -->
</div>

<?php
}
?>