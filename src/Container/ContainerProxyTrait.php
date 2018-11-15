<?php

namespace Obullo\Container;

use Obullo\Container\Exception\DefinedServiceException;

trait ContainerProxyTrait
{
    /**
     * Container proxy:
     * Provides access to container variables from everywhere
     * 
     * @param string $key key
     *
     * @return null|object
     */
    public function __get(string $key)
    {
        if ($this->container->has($key)) {
            return $this->container->get($key);
        }
        return;
    }

    /**
     * We prevent to override container variables
     *
     * @param string $key string
     * @param string $val mixed
     *
     * @return void
     */
    public function __set(string $key, $val)
    {
        if ($this->container->has($key)) {
            throw new DefinedServiceException(
                sprintf(
                    'The key "%s" is already defined in the container.',
                    $key
                )
            );
        }
        $this->{$key} = $val;
    }
}