<?php

namespace Obullo\Http;

trait BundleAwareTrait
{
    /**
     * Bundle name
     * 
     * @var string
     */
    protected $bundle;

    /**
     * Set bundle object
     * 
     * @param Bundle $bundle object
     */
    public function setBundle(Bundle $bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * Returns to bundle name
     * 
     * @return string
     */
    public function getBundle() : Bundle
    {
        return $this->bundle;
    }
}