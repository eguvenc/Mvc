<?php

namespace Obullo\Mvc\Error;

use Throwable;
use Obullo\Mvc\View\ViewInterface;

/**
 * Html Strategy
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class HtmlStrategy implements ErrorStrategyInterface
{
    protected $view;
    protected $status;

    /**
     * Constructor
     * 
     * @param ViewInterface $view template engine
     */
    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
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
     * @param  string    $title     title
     * @param  string    $message   message
     * @param  Throwable $exception exception
     * 
     * @return string
     */
    public function renderErrorMessage(string $title, string $message, Throwable $exception = null) : string
    {
        $data = array();
        $data['title'] = $title;
        $data['message'] = $message;
        $data['e'] = $exception;
        if ($this->getStatusCode() == '404') {
            return $this->view->renderView('templates::error/404', $data);
        }
        return $this->view->renderView('templates::error/error', $data);
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