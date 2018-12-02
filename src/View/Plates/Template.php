<?php

namespace Obullo\View\Plates;

use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use League\Plates\Template\Template as PlatesTemplate;

/**
 * Plates template engine - http://platesphp.com/
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Template extends PlatesTemplate implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Returns to instance of helper class
     * 
     * @param  string $name helper name
     * @return object
     */
    public function plugin($name)
    {
        $engine   = $this->getEngine();
        $function = $engine->getFunction($name);

        return $function->getCallback();
    }

    /**
     * Returns to plates Engine class
     * 
     * @return object
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Container proxy:
     * 
     * Provides access to container variables from template files
     *
     * @param string $key key
     *
     * @return object Controller
     */
    public function __get(string $key)
    {
        return $this->getContainer()->get($key);
    }
}