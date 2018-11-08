<?php

namespace Services;

use App;
use Obullo\View\Helper\{
    Url,
    Translator,
    EscapeUrl,
    EscapeHtml,
    EscapeHtmlAttr
};
use Obullo\View\PlatesPhp;
use League\Plates\Engine;
use Zend\I18n\View\Helper\Translate;
use League\Plates\Extension\Asset;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ViewPlatesFactory implements FactoryInterface
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
        $engine->loadExtension(new Asset(ROOT.'/public/', false));

        // -------------------------------------------------------------------
        // View helpers
        // -------------------------------------------------------------------
        //
        $engine->registerFunction('url', (new Url)->setRouter($container->get('router')));
        $engine->registerFunction('translate', (new Translate)->setTranslator($container->get('translator')));
        $engine->registerFunction('escapeHtml', new EscapeHtml);
        $engine->registerFunction('escapeHtmlAttr', new EscapeHtmlAttr);
        $engine->registerFunction('escapeUrl', new EscapeUrl);

        $template = new PlatesPhp($engine);
        $template->setContainer($container);

        return $template;
    }
}