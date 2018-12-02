<?php

namespace Obullo\Config;

use Zend\Config\Factory as ConfigFactory;
use Zend\ConfigAggregator\GlobTrait;

/**
 * Glob a set of any configuration files supported by Zend\Config\Factory as
 * configuration providers.
 */
class BundleConfigProvider
{
    use GlobTrait;

    /** @var string */
    private $pattern;

    /**
     * @param string $pattern Glob pattern.
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Provide configuration.
     *
     * Globs the given files, and passes the result to ConfigFactory::fromFiles
     * for purposes of returning merged configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        $files  = $this->glob($this->pattern);
        $config = ConfigFactory::fromFiles($files);

        // Normalize route rules
        
        foreach ($config as $key => $value) {
            if (is_array($value) && isset($value['path']) && isset($value['handler'])) {
                $config['routes'][$key] = $value;
                unset($config[$key]);
            }
        }
        return $config;
    }
}