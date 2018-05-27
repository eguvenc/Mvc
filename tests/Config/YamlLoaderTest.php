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
        $this->filename = ROOT.'/tests/var/config/framework.yaml';
        $fileHandler = new FileHandler('/tests/var/cache/config/');
        $this->loader = new YamlLoader($fileHandler);
    }

    public function testLoad()
    {
    	$data = $this->loader->load('/tests/var/config/framework.yaml')['framework'];

    	$this->assertArrayHasKey('cookie', $data);
    	$this->assertArrayHasKey('domain', $data['cookie']);
    	$this->assertArrayHasKey('path', $data['cookie']);
    	$this->assertArrayHasKey('secure', $data['cookie']);
    	$this->assertArrayHasKey('httpOnly', $data['cookie']);
    	$this->assertArrayHasKey('expire', $data['cookie']);
    	$this->assertArrayHasKey('name', $data['session']);
    	$this->assertEquals('sessions', $data['session']['name']);
    }

    public function testLoadEnvConfigFile()
    {
        $data = $this->loader->load('/tests/var/config/%s/framework.yaml')['framework'];

        $this->assertArrayHasKey('cookie', $data);
        $this->assertArrayHasKey('domain', $data['cookie']);
        $this->assertArrayHasKey('path', $data['cookie']);
        $this->assertArrayHasKey('secure', $data['cookie']);
        $this->assertArrayHasKey('httpOnly', $data['cookie']);
        $this->assertArrayHasKey('expire', $data['cookie']);
        $this->assertArrayHasKey('name', $data['session']);
        $this->assertEquals('sessions', $data['session']['name']);
    }
}