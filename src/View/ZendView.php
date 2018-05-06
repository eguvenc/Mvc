<?php

namespace Obullo\Mvc\View;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\RendererInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Zend view
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class ZendView implements ViewInterface
{
    /**
     * Zend renderer
     * 
     * @var object
     */
    protected $engine;

    /**
     * Constructor
     * 
     * @param Engine $engine plates engine
     */
    public function __construct(RendererInterface $engine)
    {
    	$this->engine = $engine;
    }

    /**
     * Render view as string
     * 
     * @param  mixed $nameOrModel template name
     * @param  array  $data     template data
     * 
     * @return string
     */
    public function render($nameOrModel, $data = null) : string
    {
        if (is_string($nameOrModel)) {
            $model = new ViewModel($data);
            $model->setTemplate($nameOrModel);
            return $this->engine->render($model);
        }
		return $this->engine->render($nameOrModel, $data);
    }

    /**
     * Returns to template engine
     * 
     * @return object
     */
    public function getEngine() : RendererInterface
    {
        return $this->engine;
    }
}