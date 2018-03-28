<?php

use Obullo\Mvc\Config\Cache\FileHandler;

class FileHandlerTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
       $this->cache = new FileHandler('/tests/var/cache/config');
    }

    public function testHas()
    {
        $filename = ROOT.'/tests/Resources/app.yaml';
        $this->cache->write($filename, array('test' => 123456));
        $this->assertTrue($this->cache->has($filename));
    }
}