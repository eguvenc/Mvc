<?php

namespace Obullo\Http;

/**
 * Bundle controller
 */
class Bundle
{
    /**
     * Bundle name
     * 
     * @var string
     */
    protected $bundle;

    /**
     * Set bundle namespace
     * 
     * @param string $namespace current bundle __namespace__
     */
    public function __construct(string $namespace)
    {
        list($bundle) = explode('\\', $namespace);
        $this->bundle = $bundle;
    }

    /**
     * Returns to bundle name
     * 
     * @return string
     */
    public function getName() : string
    {
        return $this->bundle;
    }
}