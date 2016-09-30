<?php

/**
 * @autor Fedyakin Alexander
 * @copyright (c) 2015, Materia Medica Group
 */
namespace Services\Geo;

/**
 * @category MED CRM
 */
class Nominatim implements Geo {
	
	private $template_url;
	private $search_url;
	private $reverse_search_url;
	
	private $user;
	private $password;
	
	private $params;
	
	private $errors = [];
	
	public function __construct($template_url, $search_url, $reverse_search_url,  $user, $password)
	{
		$this->template_url = $template_url;
		$this->search_url = $search_url;
		$this->reverse_search_url = $reverse_search_url;
		$this->user = $user;
		$this->password = $password;
		
		$this->params['format'] = 'json';
		$this->params['addressdetails'] = 1;
		$this->params['accept-language'] = 'ru';
		$this->params['polygon_svg'] = 1;
		$this->params['namedetails'] = 1;
		$this->params['extratags'] = 1;
		
		
	}	
	
	/**
	 * 
	 * @param string $city
	 * @param string $street
	 * @param string $house
	 * @return array
	 */
	public function getGeoDataByAddressParts($city='', $street='', $house='', $params=[])
	{				
		$params['city']   = $city;
		$params['street'] = "$house $street";								
		return $this->getResponce($this->search_url, $params);
	}
	
	/**
	 * 
	 * @param string $string
	 * @return array
	 */
	public function getGeoDataByAddress($string, $params=[])
	{		
		$params['q'] = $string;
		return $this->getResponce($this->search_url, $params);
	}
	
	/**
	 * 
	 * @param string $lat
	 * @param string $lon
	 * @return array
	 */
	public function getGeoDataByGeoPoint($lat, $lon, $params=[])
	{
		$params['lat'] = $lat;
		$params['lon'] = $lon;
		$params['extratags'] = 1;
		$params['limit'] = 3;
		return $this->getResponce($this->reverse_search_url, $params);		
	}
	
	public function getGeoDataByGeoPointVariants($lat, $lon, $params=[])
	{		
		$pParams['format'] = 'json';
		$pParams['addressdetails'] = 1;
		$pParams['osm_type']= 'W';
		$pParams['namedetails']= '1';
		$data = $this->getGeoDataByGeoPoint($lat, $lon, $pParams);		

        
		$address = $data->address;
		$parts = [];
		if($data && isset($data->address->house_number) && isset($data->address->city)){
			$parts = $this->getGeoDataByAddressParts($address->city, $address->road, $address->house_number, $params);
			if(isset($parts[0])){
				foreach($parts as $i=>$part){
					if($part->place_id == $data->place_id){
						$data = null;
					}
				}
			}			
		}
		
		if($data !== null){
			array_unshift($parts, $data);
		}
		
		return $parts;
	}
				
	private function getResponce($url, $params)
	{
		$array_pol = ['polygon_geojson', 'polygon_kml',	'polygon_svg', 'polygon_text'];
		$arr_intersect = array_intersect($array_pol, $params);
		if(!empty($arr_intersect)){
			$this_params = array_diff($this->params, $array_pol);
		}else{
			$this_params = $this->params;
		}
		$params = array_merge($this_params, $params);		
		$url .= '?'.http_build_query($params);    
        $options = array(
            'http'=>array(
              'method'=>"GET",
              'header'=>"Accept-language: en\r\n" .
                        "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                        "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad 
            )
        );

        $context = stream_context_create($options);

		$data = file_get_contents($url, false, $context);
        
		if($params['format']=='json'){
			$oJsoneResp = json_decode($data);
			if(is_object($oJsoneResp)){
				$oJsoneResp = $this->parseObject($oJsoneResp);
			}elseif(is_array($oJsoneResp) && isset($oJsoneResp[0]) && is_object($oJsoneResp[0])){
				foreach($oJsoneResp as $object){
					$this->parseObject($object);
				}
			}
			return $oJsoneResp;
		}				
		
		return $data;
	}
	
	public function getCityTags()
	{
		return [
			'city', 
			'town', 
			'village', 
			'hamlet', 
			'isolated_dwelling', 
			'locality', 
			'allotments', 
			'suburb',
			'quarter',
			'neighbourhood',
			'island',
			'islet'
		];		
	}
	
	private function parseObject($object)
	{		
		
		
		if(!is_object($object)){
			return $object;
		}
			
		
		
		if(!isset($object->address))
			return $object;
		
		
		var_dump($object->address);
		$address = (array)$object->address;
		
		$cityTags = $this->getCityTags();
		foreach($cityTags as $tag){
			if(isset($address[$tag])){
				$address['city'] = $address[$tag];
				break;
			}
		}
		
		$object->address = (object)($address);
		
		//var_dump($object->address);
		//die();
		return $object;
		
		
		
		
		
	}
	
	
}
