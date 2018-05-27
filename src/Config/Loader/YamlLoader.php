<?php

namespace Obullo\Mvc\Config\Loader;

use Obullo\Mvc\Config\{
    LoaderInterface,
    Reader\YamlReader,
    Cache\CacheInterface as CacheHandler
};
use Zend\Config\Factory;

/**
 * Yaml config loader
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class YamlLoader implements LoaderInterface
{
    /**
     * Constructor
     * 
     * @param CacheHandler $cacheHandler cache handler
     */
    public function __construct(CacheHandler $cacheHandler)
    {
        Factory::registerReader('yaml', new YamlReader($cacheHandler));
    }

    /**
     * Load configuration files
     * 
     * @param  string  $filename filename
     * @param  boolean $object   returns to zend config object
     * 
     * @return array|object
     */
    public function load(string $filename, $object = false)
    {
        $file = str_replace('%s', getenv('APP_ENV'), $filename);

        return Factory::fromFile(ROOT.'/'.ltrim($file, '/'), $object);
    }

    /**
     * Load config file
     * 
     * @param  string  $filename filename
     * @param  boolean $object   returns to zend config object
     * 
     * @return mixed
     */
    public function loadConfigFile(string $filename, $object = false)
    {
        return $this->load('/config/'.$filename, $object);
    }

    /**
     * Load environment config file
     * 
     * @param  string  $filename filename
     * @param  boolean $object   returns to zend config object
     * 
     * @return mixed
     */
    public function loadEnvConfigFile(string $filename, $object = false)
    {
        return $this->load('/config/%s/'.$filename, $object);
    }
}