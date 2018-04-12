<?php

namespace Obullo\Mvc\Config\Loader;

use Obullo\Mvc\Config\{
    LoaderInterface,
    Cache\FileHandler,
    Reader\YamlReader
};
/**
 * Yaml config loader
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class YamlLoader implements LoaderInterface
{
    /**
     * Constructor
     *
     * System cache handler is file, it cache first ".yaml" file at application level.
     * We able to cache other files using memory handlers.
     * 
     * @param string $path path
     */
    public function __construct(FileHandler $fileHandler)
    {
        \Zend\Config\Factory::registerReader('yaml', new YamlReader($fileHandler));
    }

    /**
     * Load static files
     * 
     * @param  string  $filename filename
     * @param  boolean $object   returns to zend config object
     * 
     * @return array|object
     */
    public function load(string $filename, $object = false)
    {
        $path = str_replace('%env%', getenv('APP_ENV'), $filename);

        return \Zend\Config\Factory::fromFile(ROOT.$path, $object);
    }
}