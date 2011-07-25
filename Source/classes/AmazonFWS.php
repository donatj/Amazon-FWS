<?php

require_once( 'AmazonWS.php' );

class AmazonFWS extends AmazonWS {
	
	protected 
		$uri        = '/Orders/2011-01-01';
	
	/**
	* @param string $aws_access_key_id AWS Access Key ID
	* @param string $aws_seller_id Merchant ID
	* @param string $aws_marketplace_id Marketplace ID
	* @param string $aws_secret_access_key Secret Key
	* @return AmazonFWS
	*/
	function __construct($aws_access_key_id, $aws_seller_id, $aws_marketplace_id, $aws_secret_access_key) {
		
		$this->aws_access_key_id = $aws_access_key_id;
		$this->aws_seller_id = $aws_seller_id;
		$this->aws_marketplace_id = $aws_marketplace_id;
		$this->aws_secret_access_key = $aws_secret_access_key;
		
	}
	
	
	/**
	* Calls the ListOrders action.
	* @see https://images-na.ssl-images-amazon.com/images/G/01/mwsportal/doc/en_US/orders/MWSOrdersApiReference._V170791601_.pdf#page=7
	*/
	public function ListOrders( $opts ) {
		$opts = $this->merge( $opts, array('Action' => 'ListOrders') );
		$doc  = $this->make_request($opts, $xml);
		
		if( $doc ) {
			$data = array();
			$data['Response'] = $doc->ListOrdersResult->Orders;
			if( $doc->ListOrdersResult->NextToken ) {
				$data['NextToken'] = $doc->ListOrdersResult->NextToken;
			}
			print_r( $data );
		}
		
	}
	
	public function ListOrderItems( $AmazonOrderId, $opts = array() ) {
		$opts = $this->merge( $opts, array( 'AmazonOrderId' => $AmazonOrderId, 'Action' => 'ListOrderItems') );
		$this->make_request($opts);
	}
	
	public function GetOrder( $AmazonOrderIds, $opts = array() ) {
		$_params = array( 'Action' => 'GetOrder');
		
		if( is_array( $AmazonOrderIds ) ) {
			$i = 1;
			foreach( $AmazonOrderIds as $id ) {
				$_params['AmazonOrderId.Id.' . ($i++) ] = $id;
			}
		}else{
			$_params['AmazonOrderId.Id.1'] = $AmazonOrderIds;
		}
		
		$opts = $this->merge( $opts, $_params );
		//print_r( $opts ); die();
		$this->make_request($opts);
	}
	
}

