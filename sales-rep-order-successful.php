<?php 
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/* 
Template Name: Vendor Sales Ref Order success
*/ 

get_header();

if( isset($_GET['success']) && isset($_GET['order']) ){
	if( $_GET['order'] != '' ){
		echo '<br><h2>Order Successful.</h2>
			<p>Order ID: '.$_GET['order'].'<p>
			<p>Please check email for details.</p><br>';
	}
}

get_footer();