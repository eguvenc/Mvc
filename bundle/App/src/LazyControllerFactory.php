<?php

namespace App;

use ReflectionClass;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class LazyControllerFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param Container $container
     * @param $name
     * @param $requestedName
     *
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return strstr($requestedName, __NAMESPACE__.'\Controller') !== false;
    }

    /**
     * These aliases work to substitute class names with Service Manager types that are buried in framework
     * 
     * @var array
     */
    protected $aliases = [
        'Obullo\Router\Router' => 'router',
        'Psr\Http\Message\RequestInterface' => 'request',
        'Obullo\Http\SubRequestInterface' => 'subRequest',
        'Zend\Form\FormElementManager' => 'formElement',
        'Zend\Validator\ValidatorPluginManager' => 'validatorManager',
        'Zend\I18n\Translator\Translator' => 'translator',
    ];

    /**
     * Create service with name
     *
     * @param Container $container
     * @param $requestedName
     *
     * @return mixed
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $class = new ReflectionClass($requestedName);

        $injectedParameters = array();
        if ($constructor = $class->getConstructor()) {
            if ($params = $constructor->getParameters()) {
                foreach($params as $param) {
                    if ($param->getClass()) {
                        $name = $param->getClass()->getName();
                        if (array_key_exists($name, $this->aliases)) {
                            $name = $this->aliases[$name];
                        }
                        if ($container->has($name)) {
                            $injectedParameters[] = $container->get($name);
                        }
                    }
                }
            }
        }
        return new $requestedName(...$injectedParameters);
    }
}