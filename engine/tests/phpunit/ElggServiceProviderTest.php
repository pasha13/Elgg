<?php

class ElggServiceProviderTest extends PHPUnit_Framework_TestCase {

	public function testSharedMetadataCache() {
		$mgr = $this->getMock('Elgg_AutoloadManager', array(), array(), '', false);

		$sp = new Elgg_ServiceProvider($mgr);

		$svcClasses = array(
			'metadataCache' => 'ElggVolatileMetadataCache',
			'autoloadManager' => 'Elgg_AutoloadManager',
			'db' => 'ElggDatabase',
			'hooks' => 'ElggPluginHookService',
			'logger' => 'ElggLogger',
		);

		foreach ($svcClasses as $key => $class) {
			$obj1 = $sp->{$key};
			$obj2 = $sp->{$key};
			$this->assertInstanceOf($class, $obj1);
			$this->assertSame($obj1, $obj2);
		}
	}
}
