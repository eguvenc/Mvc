<?php

use Obullo\Mvc\Config\{
    Loader\YamlLoader,
    Reader\YamlReader,
    Cache\FileHandler
};

class YamlLoaderTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->filename = ROOT.'/tests/var/config/app.yaml';
        $this->loader = new YamlLoader('/tests/var/cache/config/');
    }

    public function testLoad()
    {
    	$data = $this->loader->load('/tests/var/config/app.yaml');

    	$this->assertArrayHasKey('cookie', $data['app']);
    	$this->assertArrayHasKey('domain', $data['app']['cookie']);
    	$this->assertArrayHasKey('path', $data['app']['cookie']);
    	$this->assertArrayHasKey('secure', $data['app']['cookie']);
    	$this->assertArrayHasKey('httpOnly', $data['app']['cookie']);
    	$this->assertArrayHasKey('expire', $data['app']['cookie']);
    	$this->assertArrayHasKey('name', $data['app']['session']);
    	$this->assertEquals('sessions', $data['app']['session']['name']);
    }
}
