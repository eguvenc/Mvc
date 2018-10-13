<?php

namespace Services;

use Zend\ServiceManager\ServiceManager;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Zend\Config\Config;
use Zend\Config\Factory;
use Zend\Config\Processor;
use Zend\Config\Reader\Yaml as YamlReader;

use Zend\ConfigAggregator\ArrayProvider;
use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;
use Zend\ConfigAggregator\ZendConfigProvider;

use Obullo\Config\ConfigLoader;
use Obullo\Config\Processor\Env as EnvProcessor;
use Zend\Config\Processor\Constant as ConstantProcessor;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class LoaderFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @link http://config.obullo.com/ documentation of config
     * 
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $container->setService('yaml', new YamlReader([SymfonyYaml::class, 'parse']));

        Factory::registerReader('yaml', $container->get('yaml'));
        Factory::setReaderPluginManager($container);

        $env = getenv('APP_ENV');

        $aggregator = new ConfigAggregator(
            [
                new ArrayProvider([ConfigAggregator::ENABLE_CACHE => ($env == 'dev') ? false : true ]),
                new ZendConfigProvider(ROOT.'/config/autoload/{,*.}{json,yaml,php}'),
            ],
            ROOT.'/var/cache/config.php'
        );
        $config = $aggregator->getMergedConfig();
        $container->setService('config', new Config($config, true));  // Create global config object

        $loader = new ConfigLoader(
            $config,
            ROOT.'/var/cache/config.php'
        );
        $loader->setEnv($env);
        $loader->addProcessor(new EnvProcessor);
        $loader->addProcessor(new ConstantProcessor);

        $loader->load(ROOT, '/config/%s/framework.yaml');
        
        return $loader;
    }
}