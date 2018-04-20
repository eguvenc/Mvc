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
     * Load files
     * 
     * @param  string  $filename filename
     * @param  boolean $object   returns to zend config object
     * 
     * @return array|object
     */
    public function load(string $filename, $object = false)
    {
        $path = str_replace('%s', getenv('APP_ENV'), $filename);

        return Factory::fromFile(ROOT.'/'.ltrim($path, '/'), $object);
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
        return Factory::fromFile(ROOT.'/config/'.ltrim($filename, '/'), $object);
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
        return Factory::fromFile(ROOT.'/config/'.getenv('APP_ENV').'/'.ltrim($filename, '/'), $object);
    }
}