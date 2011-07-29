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
	public function ListOrders( $opts, $parentElm = 'ListOrdersResult' ) {
		$opts = $this->merge( array('Action' => 'ListOrders'), $opts );
		$doc  = $this->make_request($opts);
		
		if( $doc ) {
			return $this->_structure_document_response( $doc, $parentElm, 'Orders' ) ;
		}
		return false;
	}
	
	public function ListOrdersByNextToken( $next_token ) {
		return $this->ListOrders(array('Action' => 'ListOrdersByNextToken', 'NextToken' => $next_token), 'ListOrdersByNextTokenResult');
	}
	
	
	/**
	* Calls the ListOrders action.
	* @see https://images-na.ssl-images-amazon.com/images/G/01/mwsportal/doc/en_US/orders/MWSOrdersApiReference._V170791601_.pdf#page=22
	*/
	public function ListOrderItems( $AmazonOrderId, $opts = array(), $parentElm = '' ) {
		if( $AmazonOrderId !== false ) { $opts['AmazonOrderId'] = $AmazonOrderId; }
		$opts = $this->merge( array( 'Action' => 'ListOrderItems'), $opts );
		$doc = $this->make_request($opts);
		
		if( $doc ) {
			return $this->_structure_document_response( $doc, 'ListOrderItemsResult', 'OrderItems' ) ;
		}
		return false;
	}
	
	public function ListOrderItemsByNextToken( $next_token ) {
		return $this->ListOrderItems(false, array('Action' => 'ListOrderItemsByNextToken', 'NextToken' => $next_token), 'ListOrderItemsByNextTokenResult');
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
		$doc = $this->make_request($opts, $xml);
		
		if( $doc ) {
			return $this->_structure_document_response( $doc, 'GetOrderResult', 'Orders' );
		}
		return false;
	}
	
	private function _structure_document_response( $doc, $parentElm, $grouping_elm ) {
		$data = array();
		$data['Response'] = $doc->$parentElm->$grouping_elm;
		if( $doc->$parentElm->NextToken ) { $data['NextToken'] = (string)$doc->$parentElm->NextToken; }
		return $data;
	}
	
}

