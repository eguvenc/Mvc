<?php

namespace Obullo\Http;

use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use ReflectionClass;
use Obullo\Container\Exception\UndefinedServiceException;

/**
 * Argument resolver
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class ArgumentResolver implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $reflection;
    protected $arguments = array();

    /**
     * Clear variables
     * 
     * @return void
     */
    public function clear()
    {
        $this->arguments = array();
        $this->reflection = null;
    }

    /**
     * Set reflection class
     * 
     * @param ReflectionClass $reflection reflection
     */
    public function setReflectionClass(ReflectionClass $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * Method parameters
     * 
     * @param array $args parameters
     */
    public function setArguments(array $params)
    {
        $this->arguments = $params;
    }

    /**
     * Resolve method parameters
     * 
     * @param  string $method method
     * @return array
     */
    public function resolve(string $method) : array
    {
        $injectedParameters = array();
        $parameters = $this->reflection->getMethod($method)->getParameters();
        
        foreach ($parameters as $param) {
            $name = $param->getName();
            $interface = $param->getClass();
            if ($interface) {
                if ($this->container->has($name)) {
                    $classInstance = $this->container->get($name);
                    $interfaceClass = $interface->getName();
                    if ($classInstance instanceof $interfaceClass) {
                        $injectedParameters[] = $classInstance;
                    }
                } else {
                    throw new UndefinedServiceException(
                        sprintf(
                            'The "%s" parameter of the "%s->%s()" is not defined in your container.',
                            $name,
                            $this->reflection->getName(),
                            $method
                        )
                    );
                }
            }
            if ($interface == null && isset($this->arguments[$name])) {
                $injectedParameters[] = $this->arguments[$name];
            }
        }
        return $injectedParameters;
    }
}