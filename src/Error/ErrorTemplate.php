<?php

namespace Obullo\Error;

use Throwable;
use Obullo\View\ViewInterface as View;
use Psr\Http\Message\ResponseInterface as Response;
use Zend\I18n\Translator\Translator;
use Zend\Diactoros\Response\HtmlResponse;

class ErrorTemplate
{
	protected $view;
	protected $code;
	protected $message;
	protected $exception;
	protected $translator;
	protected $headers = array();

    /**
     * Set translator 
     * 
     * @return object
     */
    public function setTranslator(Translator $translator)
    {
    	$this->translator = $translator;
    }

    /**
     * Returns to translator 
     * 
     * @return object
     */
    public function getTranslator() : Translator
    {
    	return $this->translator;
    }

    /**
     * Set view object
     * 
     * @param View $view object
     */
    public function setView(View $view)
    {
    	$this->view = $view;
    }

    /**
     * Set http status code
     * 
     * @param int $code status code
     */
	public function setStatusCode($code)
	{
		$this->code = $code;
	}

    /**
     * Returns to status code
     * 
     * @return int
     */
	public function getStatusCode()
	{
		return $this->code;
	}

    /**
     * Set error message
     * 
     * @param string $message message
     */
	public function setMessage($message)
	{
		$this->message = $message;
	}

    /**
     * Returns to error message
     * 
     * @return string
     */
	public function getMessage()
	{
		return $this->message;
	}

    /**
     * Set http headers
     * 
     * @param array $headers headers
     */
	public function setHeaders($headers = array())
	{
		$this->headers = $headers;
	}

    /**
     * Returns to headers
     * 
     * @return array
     */
	public function getHeaders() : array
	{
		return $this->headers;
	}

    /**
     * Set exception
     * 
     * @param Throwable $exception object
     */
    public function setException(Throwable $exception)
    {
    	$this->exception = $exception;
    }

    /**
     * Returns to exception object
     * 
     * @return object
     */
    public function getException()
    {
		return $this->exception;
    }

    /**
     * Render error template
     * 
     * @param  string $template template
     * 
     * @return object
     */
    public function renderView($template) : Response
    {
        $data = array();
        $data['message'] = $this->getMessage();
        $data['translator'] = $this->getTranslator();
        $data['e'] = $this->getException();
        $html = $this->view->render($template, $data);
        
        return new HtmlResponse($html, $this->getStatusCode(), $this->getHeaders());
    }
}