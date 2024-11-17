<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 *  Dokan Dashboard Template
 *
 *  Dokan Main Dahsboard template for Fron-end
 *
 *  @since 2.4
 *
 *  @package dokan
 */
?>
<div class="dokan-dashboard-wrap">
    <?php
        /**
         *  dokan_dashboard_content_before hook
         *
         *  @hooked get_dashboard_side_navigation
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_before' );
    ?>

    <div class="dokan-dashboard-content">

        <?php
            /**
             *  dokan_dashboard_content_before hook
             *
             *  @hooked show_seller_dashboard_notice
             *
             *  @since 2.4
             */
            do_action( 'dokan_help_content_inside_before' );
        ?>
		<?php 
		$vendor_sales_ref_view = $_GET['view'];
		
		/* Sales rep page headers */
		if(isset($vendor_sales_ref_view) && $vendor_sales_ref_view == 'add_sales_rep') {
		?>
		<!--Add new sales rep-->
		<header class="dokan-dashboard-header">
            <span class="left-header-content">
                <h1 class="entry-title">
                    Add New Sales Rep
                </h1>
            </span>
            <div class="dokan-clearfix"></div>
        </header>
		<?php
		}elseif(isset($vendor_sales_ref_view) && $vendor_sales_ref_view == 'edit'){
		?>
		<!--Sales rep List-->
		<header class="dokan-dashboard-header">
            <span class="left-header-content">
                <h1 class="entry-title">
                   	Edit Sales Rep
                </h1>
            </span>
            <div class="dokan-clearfix"></div>
        </header>
		<?php
		}elseif(isset($vendor_sales_ref_view) && $vendor_sales_ref_view == 'permission'){
		?>
		<!--Sales rep List-->
		<header class="dokan-dashboard-header">
            <span class="left-header-content">
                <h1 class="entry-title">
                   	Manage Permission
                </h1>
            </span>
            <div class="dokan-clearfix"></div>
        </header>
		<?php
		}else{
		?>
		<!--Sales rep List-->
		<header class="dokan-dashboard-header">
            <span class="left-header-content">
                <h1 class="entry-title">
                   	Sales Rep
                    <span class="left-header-content dokan-right">
				<a href="<?php echo dokan_get_navigation_url( 'vendor-sales-rep' ); ?>?view=add_sales_rep" class="dokan-btn dokan-btn-theme dokan-right"><i class="fas fa-user">&nbsp;</i> Add new Sales Rep</a>
			</span>
                </h1>
            </span>
            <div class="dokan-clearfix"></div>
        </header>
		<?php } ?>
		
		<!-- Delete User -->
		<?php
		if(isset($vendor_sales_ref_view) && $vendor_sales_ref_view == 'delete'){
			if( isset($_GET['confirm']) && $_GET['confirm'] == 'yes' && isset($_GET['user']) ){
				$delete_user = $_GET['user'];
				$parent_vendor = get_user_meta($delete_user, 'parent_vendor', true);
				if($parent_vendor == get_current_user_id()){
					$redirectTo = dokan_get_navigation_url( 'vendor-sales-rep' ).'?message=user-removed';
					
					$user = get_userdata( $delete_user );
					// Get all the user roles as an array.
					$user_roles = $user->roles;
					if( in_array( 'vendor_staff', $user_roles, true ) ){
						$vendor_staff = new WP_User( $delete_user );
						$vendor_staff->remove_role( 'vendor_sales_rep' );
						update_user_meta( $delete_user, 'parent_vendor', '' );
						
						wp_redirect($redirectTo);
						exit();
					}else{
						if(wp_delete_user($delete_user)){
							wp_redirect($redirectTo);
							exit();
						}
					}
					
				}else{
					$redirectTo = dokan_get_navigation_url( 'vendor-sales-rep' ).'?message=fake-user';
					wp_redirect($redirectTo);
					exit();
				}
			}
		}
		?>
        <article class="vendor-sales-rep-area">
        	
			<?php 
			//Add sales rep
			if(isset($vendor_sales_ref_view) && $vendor_sales_ref_view == 'add_sales_rep') {
				//Create new sales rep
				if(isset($_POST['sales_rep_creation'])){
				if ( isset( $_REQUEST['vendor_sales_rep_nonce_field'] ) && wp_verify_nonce( $_REQUEST['vendor_sales_rep_nonce_field'], 'vendor_sales_rep_nonce_field' ) ) :
					
					if( !empty(trim($_POST['user_name'])) ) :
					
					$password = wp_generate_password( 8, true );
					
					$user_id = wp_insert_user(
						// here we provide all the user data as an array
						array(
							'user_login' => sanitize_text_field($_POST['user_name']), 
							'user_email' => sanitize_text_field($_POST['email']),
							'first_name' => sanitize_text_field($_POST['first_name']),
							'last_name' => sanitize_text_field($_POST['last_name']),
							'user_pass' => $password,
							'role' => 'vendor_sales_rep',
						)
					);

					if( is_wp_error( $user_id ) ) {
						// we can not create that user for some reason, let's display a message
						echo '<div class="dokan-alert dokan-alert-danger"><button type="button" class="dokan-close" data-dismiss="alert">×</button><strong>'.$user_id->get_error_message().'</strong></div>';
					} else {
						// everything is great
						$vendor_id = get_current_user_id();
						$vendor_state = get_user_meta($vendor_id, 'state', true);
						$vendor_obj = get_user_by('id', $vendor_id);
						
						//Update user data
						update_user_meta( $user_id, 'billing_phone', sanitize_text_field($_POST['phone']) );
						update_user_meta( $user_id, 'parent_vendor', $vendor_id );
						update_user_meta( $user_id, 'state', sanitize_text_field($vendor_state) );
						
						//Send email to new sales rep
						$to = $_POST['email'];
						$subject = 'Vendor sales rep login details';
						$message = 'Dear '.$_POST['first_name'].'<br>';
						$message .= 'Welcome to OverageMart as the Sales Rep of '.$vendor_obj->display_name.'. Here are your login details: <br><br>';
						$message .= 'Username: '.$_POST['user_name'].'<br>'.'Email: '.$_POST['email'].'<br>'.'Password: '.$password.'<br>';
						$message .= 'Login URL: '.get_permalink( get_option('woocommerce_myaccount_page_id') ).'<br><br>';
						$message .= 'To change the password, visit the following address: <br>'.wc_lostpassword_url();
						$headers = array( 'Content-Type: text/html; charset=UTF-8' );
						wp_mail( $to, $subject, $message, $headers, array( '' ) );
						
						//Display success message
						echo '<div class="dokan-alert dokan-alert-success"><button type="button" class="dokan-close" data-dismiss="alert">×</button><strong>Sales rep has been created successfully.</strong> <a class="btn dokan-btn-" href="'.dokan_get_navigation_url( 'vendor-sales-rep' ).'?view=permission&user='.$user_id.'">Manage Permission</a></div>';
					}
					
					endif;
					
				endif;
				}
				
				//Add a sales rep from an existing staffs
				if(isset($_POST['assign_sales_rep'])):
				if ( isset( $_REQUEST['vendor_staff_to_sales_rep_nonce_field'] ) && wp_verify_nonce( $_REQUEST['vendor_staff_to_sales_rep_nonce_field'], 'vendor_staff_to_sales_rep_nonce_field' ) ) {
					
					if( !empty(trim($_POST['vendor_staff'])) ) :
					
					$current_vendor = get_current_user_id();
					
					$vendor_staff_id = sanitize_text_field($_POST['vendor_staff']);
					$staff_parent_vendor = get_user_meta( $vendor_staff_id, '_vendor_id', true );
					
					if( $staff_parent_vendor == $current_vendor ){
						// Get the user object.
						$user = get_userdata( $vendor_staff_id );
						// Get all the user roles as an array.
						$user_roles = $user->roles;
						
						if ( in_array( 'vendor_staff', $user_roles, true ) && !in_array( 'vendor_sales_rep', $user_roles, true ) ) {
							//Add sales rep role to this staff
							$vendor_staff = new WP_User( $vendor_staff_id );
							$vendor_staff->add_role( 'vendor_sales_rep' );
							
							//Save additional meta fields for this sales rep
							$vendor_state = get_user_meta($current_vendor, 'state', true);
							update_user_meta( $vendor_staff_id, 'parent_vendor', sanitize_text_field($current_vendor) );
							update_user_meta( $vendor_staff_id, 'state', sanitize_text_field($vendor_state) );
							
							//Send email to new sales rep
							$business_name = !empty(get_user_meta($current_vendor, 'licensed_business_name', true)) ? get_user_meta($current_vendor, 'licensed_business_name', true) : '';
							$to = $vendor_staff->user_email;
							$subject = 'Vendor sales rep role assigned!';
							$message = 'Dear '.$vendor_staff->first_name.',<br><br>';
							$message .= 'You have been added as a Vendor Sales Rep for '.$business_name.'. <br><br>';
							$message .= 'Please login with your current username and password.';
							$headers = array( 'Content-Type: text/html; charset=UTF-8' );
							wp_mail( $to, $subject, $message, $headers, array( '' ) );
							
							//Display success message
							echo '<div class="dokan-alert dokan-alert-success"><button type="button" class="dokan-close" data-dismiss="alert">×</button><strong>Sales rep from an existing staff has been created successfully.</strong></div>';
						}else {
							//Display error message
							echo '<div class="dokan-alert dokan-alert-danger"><button type="button" class="dokan-close" data-dismiss="alert">×</button><strong>The sales rep role has already been assigned to this staff.</strong></div>';
						}

					}
					
					endif;
					
				}
				endif;
			?>
			
			<!--Add sales rep form-->
			
			<!-- Add a sales rep from an existing staffs -->
			<form method="post" action="">
				<h4>Add Sales Rep from your staff:</h4>
				<select name="vendor_staff" required>
					<option value="">-Select Staff-</option>
					<?php
					$current_vendor = get_current_user_id();
					$staff_query = new WP_User_Query( 
						array( 
							'role' => 'vendor_staff',
							'role__not_in' => 'vendor_sales_rep',
							'meta_key' => '_vendor_id', 
							'meta_value' => $current_vendor,
							'orderby' => 'registered',
							'order'   => 'DESC',
						)
					);
				
					if ( ! empty( $staff_query->get_results() ) ) {
						foreach( $staff_query->get_results() as $staff_obj ){
							echo '<option value="'.esc_attr($staff_obj->ID).'">'.$staff_obj->display_name.'</option>';
						}
					}
					?>
				</select>
				<input type="submit" class="dokan-btn dokan-btn-danger dokan-btn-theme" name="assign_sales_rep" value="Assign Sales Rep Role" style="margin-top: 10px" />
				<input type="hidden" id="vendor_staff_to_sales_rep_nonce_field" name="vendor_staff_to_sales_rep_nonce_field" value="<?php echo wp_create_nonce('vendor_staff_to_sales_rep_nonce_field') ?>">
			</form>
			
			<hr>
			<div style="text-align:center">Or</div>
			<hr>
			
			<!--Add new sales rep form-->
			<h4>Add a new sales rep:</h4>
			<form method="post" action="" class="dokan-form-horizontal vendor-sales-rep register">
				<input type="hidden" value="0" name="sales_rep_id">
				<input type="hidden" value="<?php echo get_current_user_id(); ?>" name="vendor_id">

				<input type="hidden" id="vendor_sales_rep_nonce_field" name="vendor_sales_rep_nonce_field" value="<?php echo wp_create_nonce('vendor_sales_rep_nonce_field') ?>">
				<div class="dokan-form-group">
					<label class="dokan-w3 dokan-control-label" for="title">First Name<span class="required"> *</span></label>
					<div class="dokan-w5 dokan-text-left">
							<input id="first_name" name="first_name" required="" value="" placeholder="First Name" class="dokan-form-control input-md" type="text">
						</div>
					</div>

					<div class="dokan-form-group">
						<label class="dokan-w3 dokan-control-label" for="title">Last Name<span class="required"> *</span></label>
						<div class="dokan-w5 dokan-text-left">
							<input id="last_name" name="last_name" required="" value="" placeholder="Last Name" class="dokan-form-control input-md" type="text">
						</div>
					</div>

					<div class="dokan-form-group">
						<label class="dokan-w3 dokan-control-label" for="user_name">User Name<span class="required"> *</span></label>
						<div class="dokan-w5 dokan-text-left">
							<input id="user_name" name="user_name" required="" value="" placeholder="User name" class="dokan-form-control input-md" type="text">
						</div>
					</div>

					<div class="dokan-form-group">
						<label class="dokan-w3 dokan-control-label" for="title">Email Address<span class="required"> *</span></label>
						<div class="dokan-w5 dokan-text-left">
							<input id="email" name="email" required="" value="" placeholder="Email" class="dokan-form-control input-md" type="email">
						</div>
					</div>

					<div class="dokan-form-group">
						<label class="dokan-w3 dokan-control-label" for="title">Phone Number</label>
						<div class="dokan-w5 dokan-text-left">
							<input id="phone" name="phone" value="" placeholder="Phone" class="dokan-form-control input-md" type="text">
						</div>
					</div>

					<div class="dokan-form-group">
						<div class="dokan-w5 dokan-text-left" style="margin-left:25%">
							<input type="submit" id="" name="sales_rep_creation" value="Create Sales Rep" class="dokan-btn dokan-btn-danger dokan-btn-theme">
						</div>
					</div>
				</form>
			<?php
				
			//Delete user
			}elseif(isset($vendor_sales_ref_view) && $vendor_sales_ref_view == 'delete'){
			?>
				<div class="sales-rep-delete-confirmation-wrap">
					<?php 
					$delete_user_id = $_GET['user']; 
					$user_obj = get_user_by('id', $delete_user_id);
					if ($user_obj) {
					$name = $user_obj->first_name.' '.$user_obj->last_name.' ('.$user_obj->user_email.')';
					?>
					<div class="sales-rep-name"><span><strong>Delete vendor sales rep: </strong><?php echo esc_html($name); ?></span></div>
					<p>Are you sure?</p>
					<div class="delete-action-btns">
						<a class="btn dokan-btn delete-btn" href="<?php echo dokan_get_navigation_url( 'vendor-sales-rep' ); ?>?view=delete&user=<?php echo esc_attr($_GET['user']); ?>&confirm=yes">Delete</a> <a  class="btn dokan-btn cancel-btn" href="<?php echo dokan_get_navigation_url( 'vendor-sales-rep' ); ?>">Cancel</a>
					</div>
					<?php } ?>
				</div>
			<?php
				
			//Edit user
			}elseif(isset($vendor_sales_ref_view) && $vendor_sales_ref_view == 'edit'){
				$edit_user_id = $_GET['user'];
				$user_obj = get_user_by('id', $edit_user_id);
				
				if(isset($_POST['sales_rep_update'])){
				if ( isset( $_REQUEST['vendor_sales_rep_edit_nonce_field'] ) && wp_verify_nonce( $_REQUEST['vendor_sales_rep_edit_nonce_field'], 'vendor_sales_rep_edit_nonce_field' ) ) :
					
					$parent_vendor = get_user_meta($edit_user_id, 'parent_vendor', true);
					if($parent_vendor == get_current_user_id()):
					
					$user_data = wp_update_user(
						// here we provide all the user data as an array
						array(
							'ID' => intval($edit_user_id), 
							'first_name' => sanitize_text_field($_POST['first_name']),
							'last_name' => sanitize_text_field($_POST['last_name']),
							'display_name' => sanitize_text_field($_POST['first_name'].' '.$_POST['last_name']),
						)
					);

					if( is_wp_error( $user_data ) ) {
						// we can not create that user for some reason, let's display a message
						echo '<div class="dokan-alert dokan-alert-danger"><button type="button" class="dokan-close" data-dismiss="alert">×</button><strong>'.$user_id->get_error_message().'</strong></div>';
					} else {
						// everything is great
						update_user_meta( $edit_user_id, 'billing_phone', sanitize_text_field($_POST['phone']) );
						echo '<div class="dokan-alert dokan-alert-success"><button type="button" class="dokan-close" data-dismiss="alert">×</button><strong>User has been updated successfully.</strong></div>';
					}
					
					endif;
					
				endif;
				}
			?>
			<form method="post" action="" class="dokan-form-horizontal vendor-sales-rep register">
				<input type="hidden" value="0" name="sales_rep_id">
				<input type="hidden" value="<?php echo get_current_user_id(); ?>" name="vendor_id">

				<input type="hidden" id="vendor_sales_rep_edit_nonce_field" name="vendor_sales_rep_edit_nonce_field" value="<?php echo wp_create_nonce('vendor_sales_rep_edit_nonce_field'); ?>">
				
				<div class="dokan-form-group">
					<label class="dokan-w3 dokan-control-label" for="title">First Name<span class="required"> *</span></label>
					<div class="dokan-w5 dokan-text-left">
						<input id="first_name" name="first_name" required="" value="<?php echo esc_html($user_obj->first_name); ?>" placeholder="First Name" class="dokan-form-control input-md" type="text">
					</div>
				</div>

				<div class="dokan-form-group">
					<label class="dokan-w3 dokan-control-label" for="title">Last Name<span class="required"> *</span></label>
					<div class="dokan-w5 dokan-text-left">
						<input id="last_name" name="last_name" required="" value="<?php echo esc_html($user_obj->last_name); ?>" placeholder="Last Name" class="dokan-form-control input-md" type="text">
					</div>
				</div>

				<div class="dokan-form-group">
					<label class="dokan-w3 dokan-control-label" for="user_name">User Name<span class="required"> *</span></label>
					<div class="dokan-w5 dokan-text-left">
						<input id="" name="" value="<?php echo esc_html($user_obj->user_login); ?>" readonly placeholder="User name" class="dokan-form-control input-md" type="text">
					</div>
				</div>

				<div class="dokan-form-group">
					<label class="dokan-w3 dokan-control-label" for="title">Email Address<span class="required"> *</span></label>
					<div class="dokan-w5 dokan-text-left">
						<input id="email" name="" readonly value="<?php echo esc_html($user_obj->user_email); ?>" placeholder="Email" class="dokan-form-control input-md" type="email">
					</div>
				</div>

				<div class="dokan-form-group">
					<label class="dokan-w3 dokan-control-label" for="title">Phone Number</label>
					<div class="dokan-w5 dokan-text-left">
						<input id="phone" name="phone" value="<?php echo esc_html(get_user_meta($user_obj->ID, 'billing_phone', true)); ?>" placeholder="Phone" class="dokan-form-control input-md" type="text">
					</div>
				</div>

				<div class="dokan-form-group">
					<div class="dokan-w5 dokan-text-left" style="margin-left:25%">
						<input type="submit" id="" name="sales_rep_update" value="Update Sales Rep" class="dokan-btn dokan-btn-danger dokan-btn-theme">
					</div>
				</div>
			</form>
			<?php
				
			//Manage permission
			} elseif(isset($vendor_sales_ref_view) && $vendor_sales_ref_view == 'permission'){
				$user_id = $_GET['user'];
				$user_obj = get_user_by('id', $user_id);
				
				$parent_vendor = get_user_meta($user_id, 'parent_vendor', true);
				if($parent_vendor == get_current_user_id()){
					
					if(isset($_POST['sales_rep_permission'])){
						if ( isset( $_REQUEST['vendor_sales_rep_permission_nonce_field'] ) && wp_verify_nonce( $_REQUEST['vendor_sales_rep_permission_nonce_field'], 'vendor_sales_rep_permission_nonce_field' ) ) {
							$assign_customer = !empty($_POST['assign_customer']) ? $_POST['assign_customer'] : array();
							if($user_id != '' && is_array($assign_customer)){
								
								$assign_customer = array_map('intval', $assign_customer);
								update_user_meta( $user_id, 'assigned_customers', $assign_customer );
								echo '<div class="dokan-alert dokan-alert-success"><button type="button" class="dokan-close" data-dismiss="alert">×</button><strong>User permission updated</strong></div>';
								
							}
						}
					}
				?>
					<h3>Sales Rep: <?php echo $user_obj->display_name; ?></h3>
					<form action="" method="post" class="dokan-form-horizontal vendor-sales-rep">
						<div class="dokan-form-group">
							<label class="dokan-w3 dokan-control-label" for="assign_customer">Assign Customers</label>
							<div class="dokan-w5 dokan-text-left">
								<select id="assign_customer" name="assign_customer[]" class="dokan-select2" multiple>
									<?php
									$vendor_id = get_current_user_id();
									$vendor_state = get_user_meta($vendor_id, 'state', true);
					
									$get_assigned_customers = is_array(get_user_meta( $user_id, 'assigned_customers', true )) ? get_user_meta( $user_id, 'assigned_customers', true ) : array();
					
									$customers = new WP_User_Query(
										array( 
											'role' => 'customer', 
											'meta_key' => 'state', 
											'meta_value' => $vendor_state,
										)
									);
									if ( ! empty( $customers->get_results() ) ) {
										foreach ( $customers->get_results() as $customer ){
											if(in_array($customer->ID, $get_assigned_customers)){
												$selected = 'selected="selected"';
											}else{
												$selected = '';
											}
											$business_name = !empty(get_user_meta($customer->ID, 'licensed_business_name', true)) ? get_user_meta($customer->ID, 'licensed_business_name', true) : '';
											echo '<option '.$selected.' value="'.$customer->ID.'">'.$business_name.' (ID: '.$customer->ID.')</option>';
										}
									}
									?>
								</select>
							</div>
						</div>
						<input type="hidden" id="vendor_sales_rep_permission_nonce_field" name="vendor_sales_rep_permission_nonce_field" value="<?php echo wp_create_nonce('vendor_sales_rep_permission_nonce_field'); ?>">
						<div class="dokan-form-group">
							<div class="dokan-w5 dokan-text-left" style="margin-left:25%">
								<input type="submit" id="" name="sales_rep_permission" value="Update" class="dokan-btn dokan-btn-danger dokan-btn-theme">
							</div>
						</div>
					</form>
				<?php
				}
			} else {
				/* List of sales rep */
				if( isset($_GET['message']) && $_GET['message'] == 'user-removed' ){
					echo '<div class="dokan-alert dokan-alert-success"><button type="button" class="dokan-close" data-dismiss="alert">×</button><strong>Vendor sales rep deleted successfully</strong></div>';
				}
				if( isset($_GET['message']) && $_GET['message'] == 'fake-user' ){
					echo '<div class="dokan-alert dokan-alert-danger"><button type="button" class="dokan-close" data-dismiss="alert">×</button><strong>You do not have permission to access the page you are trying to reach.</strong></div>';
				}
				?>
				<!--Sales rep list-->
				<?php
				$current_vendor = get_current_user_id();
				$user_query = new WP_User_Query( 
					array( 
						'role' => 'vendor_sales_rep', 
						'meta_key' => 'parent_vendor', 
						'meta_value' => $current_vendor,
						'orderby' => 'registered',
						'order'   => 'DESC',
					)
				);
				?>
				<?php if ( ! empty( $user_query->get_results() ) ) { ?>
				<table class="vendor-sales-rep-list">
					<thead>
						<tr>
							<th>Name</th>
							<th>Email</th>
							<th>Phone</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						// User Loop
						foreach ( $user_query->get_results() as $user ) {
							?>
							<tr>
								<td><?php echo $user->display_name; echo !empty(get_user_meta($user->ID, '_vendor_id', true)) ? ' <span style="color:blue">(Staff)</span>' : ''; ?></td>
								<td><?php echo $user->user_email ?></td>
								<td><?php echo get_user_meta($user->ID, 'billing_phone', true); ?></td>
								<td class="user-action">
									<?php
									// Get all the user roles as an array.
									$user_roles = $user->roles;
									?>
									<span>
										<?php if( ! in_array( 'vendor_staff', $user_roles, true ) ): ?>
										<a class="action-link" href="<?php echo dokan_get_navigation_url( 'vendor-sales-rep' ); ?>?view=edit&user=<?php echo esc_attr($user->ID); ?>">Edit</a>
										<?php endif; ?>
										<a class="action-link" href="<?php echo dokan_get_navigation_url( 'vendor-sales-rep' ); ?>?view=delete&user=<?php echo esc_attr($user->ID); ?>">Delete</a>
										<a class="action-link" href="<?php echo dokan_get_navigation_url( 'vendor-sales-rep' ); ?>?view=permission&user=<?php echo esc_attr($user->ID); ?>">Permission</a>
									</span>	
								</td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			<?php 
				} else {
					echo 'No vendor sales rep found.';
				}
			}
			?>
			
        </article><!-- .dashboard-content-area -->

         <?php
            /**
             *  dokan_dashboard_content_inside_after hook
             *
             *  @since 2.4
             */
            do_action( 'dokan_dashboard_content_inside_after' );
        ?>


    </div><!-- .dokan-dashboard-content -->

    <?php
        /**
         *  dokan_dashboard_content_after hook
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_after' );
    ?>

</div><!-- .dokan-dashboard-wrap -->
