<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function pdf_order_export_html($order_id){
	ob_start();
	$order = wc_get_order( $order_id );
	$customer_id = $order->get_customer_id();
	$vendor_id = get_post_meta($order->get_id(), '_dokan_vendor_id', true);
		
	$t_time    = $order->get_date_created();
	$time_diff = time() - $t_time->getTimestamp();
	
	// get human-readable time
	$h_time = $time_diff > 0 && $time_diff < 24 * 60 * 60
		// translators: 1)  human-readable date
		? sprintf( __( '%s ago', 'dokan-lite' ), human_time_diff( $t_time->getTimestamp(), time() ) )
		: dokan_format_date( $t_time->getTimestamp() );

	// fix t_time
	$t_time = dokan_format_date( $t_time->getTimestamp() );
	?>
	<style>
		table.main-table {
			border: 1px solid #000;
			border-collapse: collapse;
			width: 100%;
			max-width: 100%;
			padding: 15px 10px;
		}
		th, td {
			border: 1px solid #000;
			text-align: left;
		}
		th {
			background-color: #f2f2f2;
		}
		table p {
			display: none
		}
		
		.header-table {
			width: 100%;
			border: none;
		}
		.header-table td {
			padding: 5px;
			vertical-align: top;
			border: none;
		}
		.bold {
			font-weight: bold;
		}
		h1{
			font-size: 26px;
			line-height: 40px
		}
	</style>
	<h1>Customer Order List</h1>
	<br>
	<table class="header-table">
		<tr>
			<td width="12%" class="bold">Buyer</td>
			<td width="48%"><?php echo esc_html(get_user_meta($customer_id, 'licensed_business_name', true)); ?><br>
				State: <?php echo esc_html(get_user_meta($customer_id, 'state', true)); ?>, License ID: <?php echo esc_html(get_user_meta($customer_id, 'license_id', true)); ?>
				<br>
			</td>
			<td width="15%" class="bold" style="text-align: left;">Order date <br><br>Order ID</td>
			<td width="25%" style="text-align: right;"><?php echo esc_html($t_time); ?> <br><br><?php echo esc_html($order->get_id()); ?></td>
		</tr>
		<tr>
			<td width="12%"  class="bold">Seller</td>
			<td width="48%" ><?php echo esc_html(get_user_meta($vendor_id, 'licensed_business_name', true)); ?><br>
				State: <?php echo esc_html(get_user_meta($vendor_id, 'state', true)); ?>, License ID: <?php echo esc_html(get_user_meta($vendor_id, 'license_id', true)); ?>
			</td>
			<td width="15%" class="bold" style="text-align: right;"></td>
			<td width="25%" style="text-align: right;"></td>
		</tr>
	</table>
	<br><br>
	<table class="main-table">
		<thead>
			<tr>
				<th width="6%" style="text-align: center"><b>#</b></th>
				<th width="25%"><b>Product</b></th>
				<th width="23%"><b>Variant</b></th>
				<th width="18%" style="text-align: center"><b>Qty Ordered</b></th>
				<th width="28%" style="text-align: center"><b>Qty Filled</b></th>
			</tr>
		</thead>
		<tbody>
		<?php
		// List order items
		$order_items = $order->get_items(array( 'line_item', 'fee' ) );
		$number = 1;
		foreach ( $order_items as $item_id => $item ) {
		?>
		<tr>
			<td width="6%" style="text-align: center"><?php echo $number; ?></td>
			<td width="25%"><?php echo esc_html($item->get_name()); ?></td>
			<td width="23%">
			<?php if( $variation_meta = $item->get_formatted_meta_data() ) : ?>
				<?php foreach ( $variation_meta as $meta_id => $meta ) : ?>
				<?php echo ucfirst(strip_tags($meta->display_key)).': '; ?>
				<?php echo ucfirst(strip_tags($meta->display_value)); ?>
				<?php endforeach; ?>
			<?php endif; ?>
			</td>
			<td width="18%" style="text-align: center"><?php echo esc_html($item->get_quantity()); ?></td>
			<td width="28%" style="text-align: center"><?php echo ' '; ?></td>
		</tr>
		<?php
		$number++;
		}
		?>
		</tbody>
	</table>
	<br>
	<p style="line-height:24px">Printed on: <?php echo date('M d, Y'); ?> <br>Generated with <img src="<?php echo get_stylesheet_directory_uri(); ?>/invoice/examples/images/heart.png" width="12px" height="12px" alt="(Love)" /> by OverageMart.com | Streamline your business!</p>
	<?php
	return ob_get_clean();
}

add_action('dokan_order_content_after', function(){
	?>
	<script>
	function downloadFile(data) {
		// Create a temporary anchor element
		var link = document.createElement('a');
		link.href = data.pdf_url;
		link.download = 'order-'+data.order_id;

		// Trigger the click event to download the file
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}
		
	jQuery(document).ready(function($) {
		jQuery('.generate-pdf').on('click', function(e) {
			e.preventDefault();
			var order_id = jQuery(this).attr('order-id');

			// Send an AJAX request to generate the PDF
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				data: {
					action: 'vendor_order_generate_pdf',
					order_id: order_id
				},
				success: function(response) {
					// Handle the PDF generation success or failure
					var data = JSON.parse(response);
					//console.log(response);
					if (data.success) {
						//window.open(data.pdf_url, '_blank');
						downloadFile(data);
					} else {
						alert('PDF generation failed.');
					}
				}
			});
		});
	});
	</script>
<?php
}, 10);