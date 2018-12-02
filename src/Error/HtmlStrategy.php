<?php

namespace Obullo\Error;

use Throwable;
use Obullo\View\HtmlInterface;
use Zend\I18n\Translator\{
    TranslatorAwareInterface,
    TranslatorAwareTrait
};
/**
 * Html Strategy
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class HtmlStrategy implements ErrorStrategyInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    protected $html;
    protected $status;

    /**
     * Constructor
     * 
     * @param ViewInterface $view template engine
     */
    public function __construct(HtmlInterface $html)
    {
        $this->html = $html;
    }

    /**
     * Set status
     * 
     * @param int $status http status code
     */
    public function setStatusCode($status)
    {
        $this->status = $status;
    }

    /**
     * Returns to status
     * 
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Render error message
     * 
     * @param  string    $message   message
     * @param  Throwable $exception exception
     * 
     * @return string
     */
    public function renderErrorMessage(string $message, Throwable $exception = null) : string
    {
        $translator = $this->getTranslator();
    
        $data = array();
        $data['message'] = $message;
        $data['translator'] = $translator;
        $data['e'] = $exception;
        if ($this->getStatusCode() == '404') {
            return $this->html->render('View::_404.phtml', $data);
        }
        return $this->html->render('View::_Error.phtml', $data);
    }

    /**
     * Returns to response class
     * 
     * @return string
     */
    public function getResponseClass() : string
    {
        return '\Zend\Diactoros\Response\HtmlResponse';
    }
}