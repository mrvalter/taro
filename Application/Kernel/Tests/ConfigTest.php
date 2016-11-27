<?php
namespace Kernel\Tests;
use PHPUnit\Framework\TestCase;
use Kernel\Services\Config;

class ConfigTest extends TestCase{
	
	
	/**
	* @covers \Kernel\Services\Config::__construct
	* @uses \Kernel\Services\Config
	*/
	public function testInitEmptyConfig()
	{
		$config = new Config();
		$this->assertInstanceOf('Kernel\\Services\\Config', $config);
		return $config;
	}
	
	public function testInitConfig()
	{
		$array = [
			'name'    => 'option',
			'nameTag' => '%tag%\\option',
			'array'   => [
				'%tag%\\option2', 
				'option2'
			],
			'array2'=>[
				'key1'=>'value2',
				'key2'=>'%tag2%-yoomy'
			]						
		];
		
		$tags = [
			'%tag%'=>'limpopo',
			'%tag2%'=>'topotopo',			
		];
		
		$config = new Config($array, 'dev', $tags);
		return $config;
	}
	
	/**
	 * 
	 * @depends testInitConfig
	 */
	public function testConfigNonExistValue(Config $config)
	{
		
		$this->assertEquals(null, $config->getValue('nonexistValue'));
	}
	
	/**
	 * 
	 * @depends testInitConfig
	 */
	public function testConfigEnviromentValue(Config $config)
	{
		
		$this->assertEquals('dev', $config->getEnviroment());
	}
	
	/**
	 * 
	 * @depends testInitConfig
	 */
	public function testConfigValue(Config $config)
	{
		
		$this->assertEquals('option', $config->getValue('name'));
		$this->assertEquals('option2', $config->getValue('array')[1]);
		$this->assertEquals('value2', $config->getValue('array2', 'key1'));
	}
	
	/**
	 * 
	 * @depends testInitConfig
	 */
	public function testInitConfigTagValue(Config $config)
	{
						
		$this->assertEquals('limpopo\\option', $config->getValue('nameTag'));
		$this->assertEquals('topotopo-yoomy', $config->getValue('array2', 'key2'));
	}
	
	/**
	 * 
	 * @depends testInitConfig
	 */
	public function testInitConfigGet(Config $config)
	{
		$paramConfig = $config->get('name');
		$this->assertInstanceOf('Kernel\\Services\\Config', $paramConfig);
		$this->assertEquals('option', $paramConfig->getValue()[0]);
	}
	
	
}
