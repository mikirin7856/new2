<?php
class imacPWS_webservice
{			
	public function imacPWS_getProducts($url_tienda,$desencriptar_pass,$productos_base)
	{				
		$url = $url_tienda.'/api/products/?output_format=JSON&display=full&filter[id]=['.$productos_base.']';		
		$args = array(
	    'headers' => array(
	        'Authorization' => 'Basic '.base64_encode( $desencriptar_pass.':' )
	    )
		);	
		$response = wp_remote_get( $url, $args );	
		//SI da error: cURL error 60: SSL certificate problem: unable to get local issuer certificate
		//add_filter('https_ssl_verify', '__return_false');
		//echo "<pre>";
		//var_dump($response);	
		$body = wp_remote_retrieve_body($response);		
		$array = json_decode($body, true);												
		return $array;
	}	
	
	public function imacPWS_getCategoria($url_tienda,$desencriptar_pass,$categoria_id)
	{				
		$url = $url_tienda.'/api/categories/'.$categoria_id.'?output_format=JSON';
		$args = array(
	    'headers' => array(
	        'Authorization' => 'Basic '.base64_encode( $desencriptar_pass.':' )
	    )
		);
		$response = wp_remote_get( $url, $args );		
		$body = wp_remote_retrieve_body($response);		
		$array = json_decode($body, true);				
		return $array;
	}	
	
	public function imacPWS_getTax($url_tienda,$desencriptar_pass,$tax_id)
	{					
		$url1 = $url_tienda.'/api/tax_rules/'.$tax_id.'?output_format=JSON';		
		$args = array(
	    'headers' => array(
	        'Authorization' => 'Basic '.base64_encode( $desencriptar_pass.':' )
	    )
		);
		$response1 = wp_remote_get( $url1, $args );		
		$body1 = wp_remote_retrieve_body($response1);		
		$array1 = json_decode($body1, true);				
		$tax_id_f=$array1['tax_rule']['id_tax'];		
		if ($tax_id_f=='')$tax_id_f=$tax_id;

		$url = $url_tienda.'/api/taxes/'.$tax_id_f.'?output_format=JSON';				
		$response = wp_remote_get( $url, $args );		
		$body = wp_remote_retrieve_body($response);		
		$array = json_decode($body, true);				
		return $array['tax']['rate'];		
	}

	public function imacPWS_getTax2($url_tienda,$desencriptar_pass,$tax_id)
	{							
		$args = array(
	    'headers' => array(
	        'Authorization' => 'Basic '.base64_encode( $desencriptar_pass.':' )
	    )
		);		

		$url = $url_tienda.'/api/taxes/'.$tax_id.'?output_format=JSON';				
		$response = wp_remote_get( $url, $args );		
		$body = wp_remote_retrieve_body($response);		
		$array = json_decode($body, true);				
		return $array['tax']['rate'];		
	}	
	
	public function imacPWS_getStock($url_tienda,$desencriptar_pass,$stock_id)
	{		
		$url = $url_tienda.'/api/stock_availables/'.$stock_id.'?output_format=JSON';			
		$args = array(
	    'headers' => array(
	        'Authorization' => 'Basic '.base64_encode( $desencriptar_pass.':' )
	    )
		);
		$response = wp_remote_get( $url, $args );		
		$body = wp_remote_retrieve_body($response);		
		$array = json_decode($body, true);				
		return $array['stock_available']['quantity'];		
	}
	
	public function imacPWS_getOffer($url_tienda,$desencriptar_pass,$product_id)
	{				
		$url = $url_tienda.'/api/specific_prices/?output_format=JSON&display=full&filter[id_product]=['.$product_id.']';				
		$args = array(
	    'headers' => array(
	        'Authorization' => 'Basic '.base64_encode( $desencriptar_pass.':' )
	    )
		);
		$response = wp_remote_get( $url, $args );		
		$body = wp_remote_retrieve_body($response);		
		$array = json_decode($body, true);	
					
		if (isset($array['specific_prices'][0]['reduction_type'])){
			$hoy=date("Y-m-d H:i:s");			
			$from=$array['specific_prices'][0]['from'];
			$to=$array['specific_prices'][0]['to'];
			if ($hoy > $from){
				if ($to!='0000-00-00 00:00:00'){
					if ($hoy < $to){
						$valor[0]=$array['specific_prices'][0]['reduction_type'];
						$valor[1]=$array['specific_prices'][0]['reduction'];
						$valor[2]=$to;
						return $valor;	
					}else{
						return '';
					}
				}else{
					$valor[0]=$array['specific_prices'][0]['reduction_type'];
					$valor[1]=$array['specific_prices'][0]['reduction'];
					$valor[2]='';
					return $valor;	
				}
			}else{
				return '';
			}
		}else{
			return '';
		}
		
	}	
	
	public function imacPWS_getLanguage($url_tienda,$desencriptar_pass,$id_idioma)
	{				
		$url = $url_tienda.'/api/languages/'.$id_idioma.'/?output_format=JSON';				
		$args = array(
	    'headers' => array(
	        'Authorization' => 'Basic '.base64_encode( $desencriptar_pass.':' )
	    )
		);
		$response = wp_remote_get( $url, $args );		
		$body = wp_remote_retrieve_body($response);		
		$array = json_decode($body, true);					
		$valor=$array['language']['iso_code'];		
		return $valor;	
	}			
	
	public function imacPWS_getImage($url_tienda,$desencriptar_pass,$id_product,$id_image)
	{											
		$url = $url_tienda.'/api/images/products/'.$id_product.'/'.$id_image.'/home_default';			
		$args = array(
	    'headers' => array(
	        'Authorization' => 'Basic '.base64_encode( $desencriptar_pass.':' )
	    )
		);		
		$response = wp_remote_get( $url, $args );		
		$body = wp_remote_retrieve_body($response);			
		$imdata=base64_encode($body);		
		return $imdata;		
	}		
	
}