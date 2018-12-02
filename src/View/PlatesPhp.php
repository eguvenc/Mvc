<?php

namespace Obullo\View;

use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use League\Plates\Engine;
use Obullo\View\Plates\Template;

/**
 * Plates template engine - http://platesphp.com/
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class PlatesPhp implements ContainerAwareInterface, HtmlInterface
{
    use ContainerAwareTrait;

    /**
     * Plates engine
     * 
     * @var object
     */
    protected $engine;

    /**
     * Template
     * 
     * @var object
     */
    protected $template;

    /**
     * Constructor
     * 
     * @param Engine $engine plates engine
     */
    public function __construct(Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Render view as string
     * 
     * @param  mixed $filename template name
     * @param  array  $data     template data
     * 
     * @return string
     */
    public function render($filename, $data = null) : string
    {
        $this->template = new Template($this->engine, $filename);
        $this->template->setContainer($this->getContainer());

        return $this->template->render((array)$data);
    }

    /**
     * Returns to template engine
     * 
     * @return object
     */
    public function getEngine() : Engine
    {
        return $this->engine;
    }
}