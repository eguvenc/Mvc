<?php

namespace Service;

use Obullo\View\Helper\{
    Url as UrlHelper,
    EscapeUrl as EscapeUrlHelper,
    EscapeHtml as EscapeHtmlHelper,
    RenderView as RenderViewHelper,
    RenderSubView as RenderSubViewHelper,
    EscapeHtmlAttr as EscapeHtmlAttrHelper
};
use Obullo\View\PlatesPhp;
use League\Plates\Engine;
use Zend\I18n\View\Helper\Translate as TranslateHelper;
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
        $engine = new Engine(ROOT.'/bundle/View'); // Default folder
        $engine->setFileExtension(null);
        
        $engine->addFolder('App', ROOT.'/bundle/App/src/View');
        $engine->addFolder('View', ROOT.'/bundle/View');

        $engine->loadExtension(new Asset(ROOT.'/public/', false));

        // -------------------------------------------------------------------
        // View helpers
        // -------------------------------------------------------------------
        // 
        $url = new UrlHelper;
        $url->setRouter($container->get('router'));

        $renderView = new RenderViewHelper;
        $renderView->setContainer($container);

        $renderSubView = new RenderSubViewHelper;
        $renderSubView->setContainer($container);

        $translate = new TranslateHelper;
        $translate->setTranslator($container->get('translator'));

        $engine->registerFunction('url', $url);
        $engine->registerFunction('renderView', $renderView);
        $engine->registerFunction('renderSubView', $renderSubView);
        $engine->registerFunction('translate', $translate);
        $engine->registerFunction('escapeHtml', new EscapeHtmlHelper);
        $engine->registerFunction('escapeHtmlAttr', new EscapeHtmlAttrHelper);
        $engine->registerFunction('escapeUrl', new EscapeUrlHelper);

        $template = new PlatesPhp($engine);
        $template->setContainer($container);

        return $template;
    }
}