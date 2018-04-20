<?php

namespace Services;

use Obullo\Mvc\View\PlatesPhp;
use League\Plates\Engine;
use League\Plates\Extension\Asset;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ViewFactory implements FactoryInterface
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
        $engine = new Engine(ROOT.'/'.APP.'/View');
        $engine->setFileExtension('phtml');
        $engine->addFolder('templates', ROOT.'/templates');
        $engine->loadExtension(new Asset('/public/'.strtolower(APP).'/assets/', true));

        $template = new PlatesPhp($engine);
        $template->setContainer($container);

        return $template;
    }
}