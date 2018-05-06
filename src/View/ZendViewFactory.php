<?php

namespace Services;

use Obullo\Mvc\View\ZendView;
use Zend\View\Model\ViewModel;
use Zend\View\HelperPluginManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Obullo\Mvc\View\Helpers\Url;

class ViewZendFactory implements FactoryInterface
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
        $config = array(
            'view_helper_config' => [
                'asset' => [
                    'resource_map' => [
                        'css/bootstrap.css' => '/assets/css/bootstrap.css',
                        'css/welcome.css' => '/assets/css/welcome.css',
                        'images/logo.png' => '/assets/images/logo.png',
                    ],
                ],
            ]
        );
        $container->setService('config', $config);

        $renderer = new PhpRenderer();
        $pluginManager = new HelperPluginManager($container);
        $pluginManager->setRenderer($renderer);

        $model = new ViewModel;
        $model->setTemplate('layout.html');

        $plugin  = $pluginManager->get('viewmodel');
        $plugin->setRoot($model);

        $renderer->setHelperPluginManager($pluginManager);

        $resolver = new Resolver\AggregateResolver();
        
        $renderer->getHelperPluginManager()
            ->setService('url', new Url($container));

        $renderer->setResolver($resolver);

        $map = new Resolver\TemplateMapResolver([
            'layout'      => ROOT.'/'.APP.'/View/layout.phtml',
            // 'index/index' => ROOT.'/'.APP.'/View/welcome.phtml',
        ]);
        $stack = new Resolver\TemplatePathStack;
        $stack->addPath(ROOT.'/'.APP.'/View');
        $stack->addPath(ROOT.'/templates');

        // Attach resolvers to the aggregate:
        $resolver
            ->attach($map)    // this will be consulted first, and is the fastest lookup
            ->attach($stack)  // filesystem-based lookup
            ->attach(new Resolver\RelativeFallbackResolver($map)) // allow short template names
            ->attach(new Resolver\RelativeFallbackResolver($stack));

        $template = new ZendView($renderer);
        return $template;
    }
}