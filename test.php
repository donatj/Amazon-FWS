<?php
error_reporting(E_ALL);

if( file_exists('Local/configure.php') ) {
	include('Local/configure.php');
}else{
	include('includes/configure.php');
}


require('Source/classes/AmazonFWS.php');

$afws = new AmazonFWS( AWS_ACCESS_KEY_ID, AWS_SELLER_ID, AWS_MARKETPLACE_ID, AWS_SECRET_ACCESS_KEY );
$threedaysago = strtotime('5 days ago');
//$afws->ListOrders(array('CreatedAfter' => J_AmazonWS::date_format($threedaysago) ));
//$afws->GetOrder(array('104-0016898-0766635'));
$afws->GetOrder(array('104-0016898-0766635', '103-6094405-3751416'));
