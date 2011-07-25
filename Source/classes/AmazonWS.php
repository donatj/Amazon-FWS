<?php
	
abstract class AmazonWS {
	
	const ERR_BAD_XML = 100, ERR_THROTTLE = 150, ERR_OTHER = 1000;
	
	protected $aws_access_key_id, $aws_seller_id, $aws_marketplace_id, $aws_secret_access_key;
	public $last_request, $last_response;
	
	protected 
		$host       = 'mws.amazonservices.com',
		$method     = 'GET',
		$version    = '2011-01-01',
		$sigmethod  = 'HmacSHA256',
		$sigversion = '2';
		
	const ISO_DATE = "Y-m-d\TH:i:s\Z";
	
	function sign( $query ) {
		$string_to_sign = $this->method."\n".$this->host."\n".$this->uri."\n".$query;
		$signature = base64_encode(hash_hmac("sha256", $string_to_sign, AWS_SECRET_ACCESS_KEY, True));
		$signature = str_replace("%7E", "~", rawurlencode($signature));
		
		return $signature;
	}
	
	function param_c14n( $params ) {
		ksort( $params );
		foreach ($params as $param => $value) {
			$param = str_replace("%7E", "~", rawurlencode($param));
			$value = str_replace("%7E", "~", rawurlencode($value));
			$canonicalized_params[$param] = $value;
		}
		
		return $canonicalized_params;
		
	}
	
	function build_request( $extra_params = array() ) {
		
		$params = Array(
			"AWSAccessKeyId"     => $this->aws_access_key_id,
			"SellerId"           => $this->aws_seller_id,
			"SignatureVersion"   => $this->sigversion,
			"Timestamp"          => gmdate(self::ISO_DATE),
			"Version"            => $this->version,
			"SignatureMethod"    => $this->sigmethod,
			"MarketplaceId.Id.1" => $this->aws_marketplace_id,
		);
		
		$params = $this->merge( $params, $extra_params );
		
		return $params;
		
	}
	
	protected function merge( $arr1, $arr2 ) {
		foreach( $arr2 as $key => $value ) {
			$arr1[$key] = $value;
		}
		return $arr1;
	}
	
	/**
	* Makes the request and parses it for errors
	* 
	* @param mixed $opts
	* @param mixed $xml
	* @return SimpleXMLElement|bool
	*/
	protected function make_request( $opts, &$xml = false ) {
		$url = $this->build_request_url( $opts );		
		$xml = $this->exec_request( $url );
		
		if( stripos( $xml, '<?xml' ) === false ) { 
			throw new Exception('Amazon WS Response Not Well Formed XML', self::ERR_BAD_XML);
			return false;
		}
		$doc = new SimpleXMLElement( $xml );
		
		if( $error = $doc->Error ) {			
			throw new Exception( $error->Type . ' - ' . $error->Code . ': ' . $error->Message, stripos($error->Code, 'Throttled') !== false ? self::ERR_THROTTLE : self::ERR_OTHER );
			return false;
		}
		
		return $doc;
		
	}
	
	protected function build_request_url( $opts ) {
	
		$req = $this->build_request($opts);
		$req = $this->param_c14n( $req );

		//http_build_query would be optimal but AmazonWS didn't like it
		$query = '';
		foreach( $req as $key => $value ) { $query .= $key . '=' . $value . '&'; }
		$query = trim($query, '&');
		
		$signature = $this->sign($query);
		
		$url = "https://". $this->host . $this->uri ."?".$query."&Signature=".$signature;
		return $url;
		
	}
	
	private function exec_request( $url ) {
		$ch   = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$data = curl_exec($ch);

		curl_close($ch);
		
		$this->last_request  = $url;
		$this->last_response = $data;
		
		return $data;
	}

	static function date_format( $timestamp ) {
		return gmdate( self::ISO_DATE, $timestamp);
	}
	
}