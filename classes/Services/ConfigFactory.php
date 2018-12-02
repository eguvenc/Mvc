<?php

namespace Services;

use Zend\ServiceManager\ServiceManager;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Zend\Config\Config;
use Zend\Config\Factory;
use Zend\Config\Reader\Yaml as YamlReader;
use Zend\ConfigAggregator\{
    ArrayProvider,
    ConfigAggregator,
    PhpFileProvider,
    ZendConfigProvider
};
use Obullo\Config\BundleConfigProvider;
use Obullo\Config\Processor\Env as EnvProcessor;
use Zend\Config\Processor\Constant as ConstantProcessor;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ConfigFactory implements FactoryInterface
{
    /**
     * Create an object
     * 
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        Factory::registerReader('yaml', new YamlReader([SymfonyYaml::class, 'parse']));
        Factory::setReaderPluginManager($container);

        $env = getenv('APP_ENV');
        $aggregator = new ConfigAggregator(
            [
                new ArrayProvider([ConfigAggregator::ENABLE_CACHE => ($env == 'dev') ? false : true ]),
                new ZendConfigProvider(ROOT.'/config/'.$env.'/{,*.}{yaml,php}'),
                new BundleConfigProvider(ROOT.'/bundle/App/config/{,*.}{yaml,php}'),
                new BundleConfigProvider(ROOT.'/bundle/View/config/{,*.}{yaml,php}'),
            ],
            ROOT.'/var/cache/config.php'
        );
        $config = $aggregator->getMergedConfig();
        $config = new Config($config, true);

        $envProcessor = new EnvProcessor;
        $envProcessor->process($config);

        $constantProcessor = new ConstantProcessor;
        $constantProcessor->process($config);
        
        return $config;
    }
}