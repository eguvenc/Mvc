<?php

namespace Obullo\Composer;

use Composer\Autoload\ClassLoader;

/**
 * Parse psr4 class map & detect bundles
 */
class BundleParser
{
    private static $loader;
    private static $bundles;

    /**
     * Constructor
     * 
     * @param ClassLoader $loader loader
     */
    public static function setLoader(ClassLoader $loader)
    {
        Self::$loader = $loader;
        Self::$bundles = Self::parsePrefixes();
    }

    /**
     * Returns to configured bundle name / path
     * 
     * @return array
     */
    public static function getConfiguredBundles() : array
    {
        return Self::$bundles;
    }

    /**
     * Parse class prefixes
     * 
     * @return array
     */
    protected static function parsePrefixes()
    {
        $bundles = array();
        foreach (Self::$loader->getPrefixesPsr4() as $key => $value) {
            if (strpos($value[0], '/bundle/') > 0 && strpos($value[0], '/src') > 0) {
                $bundles[trim($key,'\\')] = str_replace('../', '', strstr($value[0], '../'));
            }
        }
        if (empty($bundles)) {
            throw new BundleNotFoundException('There is no bundle defined in your composer.json file.');
        }
        return $bundles;
    }
}