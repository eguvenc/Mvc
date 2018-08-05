<?php

namespace Tests\App\Services;

use Zend\ServiceManager\ServiceManager;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Zend\Config\Config;
use Zend\Config\Factory;
use Zend\Config\Processor;
use Zend\Config\Reader\Yaml as YamlReader;

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

        $loader = new ConfigLoader(
            array('config_cache_enabled' => false),
            ROOT.'/tests/var/cache/config.php'
        );
        $loader->setEnv(getenv('APP_ENV'));
        $loader->addProcessor(new EnvProcessor);
        $loader->addProcessor(new ConstantProcessor);

        $loader->load(ROOT, '/tests/var/config/%s/framework.yaml');
        return $loader;
    }
}