<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Services\Geo;

/**
 *
 * @author sworion
 */
interface Geo {
	
	public function getGeoDataByAddressParts($city='', $street='', $house='', $params=[]);
	public function getGeoDataByAddress($string, $params=[]);
	public function getGeoDataByGeoPoint($lat, $lon, $params=[]);
	
}
