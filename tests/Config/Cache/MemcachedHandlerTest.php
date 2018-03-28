<?php

use Obullo\Mvc\Config\Cache\MemcachedHandler;

class MemcachedHandlerTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->filename = ROOT.'/tests/Resources/app.yaml';

        $memcached = new Memcached;
        $memcached->addServer('127.0.0.1', 11211);
        $memcached->setOption(Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_PHP);

        $this->cache = new MemcachedHandler($memcached);
    }

    public function testHas()
    {
        $this->cache->write($this->filename, array('test' => 123456));
        $this->assertTrue($this->cache->has($this->filename));
    }

    public function testRead()
    {
        $this->cache->write($this->filename, array('int' => 6789, 'str' => 'foo'));
        $data = $this->cache->read($this->filename);

        $this->assertEquals($data['int'], 6789);
        $this->assertEquals($data['str'], 'foo');
        $this->assertArrayNotHasKey('__mtime__', $data);
    }

    public function testWrite()
    {
        $this->cache->write(
            $this->filename,
            [
                'app' => [
                    'session' => [
                        'name' => 'sessions'
                    ]
                ]
            ]
        );
        $data = $this->cache->read($this->filename);
        $this->assertEquals($data['app']['session']['name'], 'sessions');
    }
}