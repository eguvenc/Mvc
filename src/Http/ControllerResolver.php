<?php

namespace Obullo\Http;

use Psr\Container\ContainerInterface;
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Obullo\Router\Router;

/**
 * Controller resolver
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class ControllerResolver
{
    use ContainerAwareTrait;

    protected $name;
    protected $class;
    protected $router;
    protected $method;
    protected $classInstance;
    protected $isCallable = false;

    /**
     * Set a container.
     *
     * @param  \Psr\Container\ContainerInterface $container
     * @return $this
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * Constructor
     * 
     * @param Router $router router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Resolve handler
     * 
     * @return void
     */
    public function resolve(string $handler)
    {
        $container = $this->getContainer();
        $explodeMethod = explode('::', $handler);
        $this->class  = $explodeMethod[0];
        $this->method = $explodeMethod[1];
        $explode  = explode('\\', $this->class);
        $this->name = (string)$explode[0];

        $this->classInstance = $container->build($this->class, ['method' => $this->method]);
    }
    
    /**
     * Returns to first namespace e.g. 'App'.
     * 
     * @return string
     */
    public function getBundleName() : string
    {
        return $this->name;
    }

    /**
     * Returns to resolved class name
     * 
     * @return string
     */
    public function getClassName() : string
    {
        return $this->class;
    }

    /**
     * Returns to resolved class method
     * 
     * @return string
     */
    public function getClassMethod() : string
    {
        return $this->method;
    }

    /**
     * Returns to resolved controller instance
     * 
     * @return object
     */
    public function getClassInstance()
    {
        return $this->classInstance;
    }

    /**
     * Returns to router
     * 
     * @return object
     */
    public function getRouter() : Router
    {
        return $this->router;
    }
 }