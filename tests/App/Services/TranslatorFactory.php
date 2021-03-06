<?php

namespace Tests\App\Services;

use Zend\I18n\Translator\Resources;
use Zend\I18n\Translator\Translator;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class TranslatorFactory implements FactoryInterface
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
        $container->setAlias('MvcTranslator', $requestedName); // Zend components support

        $config = $container->get('loader')
            ->load(ROOT, '/tests/var/config/%s/framework.yaml', true)
            ->framework
            ->translator;
            
        $translator = new Translator;
        $translator->setLocale($config->default_locale);
        $translator->addTranslationFilePattern('PhpArray', ROOT, '/tests/var/messages/%s/messages.php');

		return $translator;
    }
}