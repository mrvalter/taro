<?php
namespace Kernel\Tests;

use PHPUnit\Framework\TestCase;
use Kernel\Services\ServiceContainerReal;
use Kernel\Services\Config;


class SomeService {}

class SomeService2 {
	private $service;
	public function __construct(SomeService3 $service3)
	{
		$this->service = $service3;
	}
	public function getService():SomeService3
	{
		return $this->service;
	}
}
class SomeService3 {}

class ServicesTest extends TestCase{
	
	
	public function testInitEmptyService()
	{
		$service = new ServiceContainerReal();
		$this->assertInstanceOf('Kernel\\Services\\ServiceContainerReal', $service);
		return $service;
	}
	
	/**
	 * 
	 * @depends testInitEmptyService
	 */
	public function testAddAndReadService(ServiceContainerReal $service)
	{
		$someService = new SomeService();		
		$service->addService('some', $someService);
		$this->assertInstanceOf('Kernel\\Tests\\SomeService', $service->get('some'));
	}
	
	/**
	 * 
	 * @depends testInitEmptyService 
	 * @expectedException \ServiceNotFoundException
	 */
	public function testGetNonExistsService(ServiceContainerReal $service)
	{
				
		$service->get('notExistsService');
	}
	
	/**
	 * 
	 * @return ServiceContainerReal
	 */
	public function testInitServiceWConfig()
	{
		$config = new Config([			
			'service' => [
				'class' => 'Kernel\\Tests\\SomeService'
			],
			'service2' => [
				'class'  => 'Kernel\\Tests\\SomeService2',
				'params' => ['@service3']
			],
			
			'service3' => [
				'class' => 'Kernel\\Tests\\SomeService3'
			],
			'service4' => [
				'class' => 'Kernel\\Tests\\SomeService',
				'params' => ['@service5']
			],
			
			'service5' => [
				'class' => 'Kernel\\Tests\\SomeService',
				'params' => ['@service6']
			],
			
			'service6' => [
				'class' => 'Kernel\\Tests\\SomeService',
				'params' => ['@service5']
			],
			
		]);
		
		$service = new ServiceContainerReal($config);
		$this->assertInstanceOf('Kernel\\Services\\ServiceContainerReal', $service);
		return $service;
	}
	
	/**
	 * 
	 * @depends testInitServiceWConfig
	 */
	public function testGetServiceFromConfig(ServiceContainerReal $service)
	{		
				
		$this->assertInstanceOf('Kernel\\Tests\\SomeService', $service->get('service'));
	}
		
	/**
	 * 
	 * @depends testInitServiceWConfig
	 */
	public function testGetServiceFromConfigWServiceParams(ServiceContainerReal $service)
	{		
		$object = $service->get('service2');
		$this->assertInstanceOf('Kernel\\Tests\\SomeService2', $object);
		$this->assertInstanceOf('Kernel\\Tests\\SomeService3', $object->getService());
	}
	
	
	/**
	 * @expectedException \ServiceCycleException
	 * @depends testInitServiceWConfig
	 */
	public function testGetServiceCyclic(ServiceContainerReal $service)
	{		
		
		$service->get('service4');				
	}
	
	
	/**
	 * 
	 * @depends testInitServiceWConfig
	 */
	public function testGetSameObjects(ServiceContainerReal $service)
	{		
		$object = $service->get('service2');
		$object2 = $service->get('service2');
		$this->assertEquals(true, $object===$object2);
		
	}
	
	
	
						
	
}
