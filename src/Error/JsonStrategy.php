<?php

namespace Obullo\Mvc\Error;

use Throwable;
use Zend\I18n\Translator\{
    TranslatorAwareInterface,
    TranslatorAwareTrait
};
/**
 * Json Strategy
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class JsonStrategy implements ErrorStrategyInterface, TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    protected $status = 500;
    protected $enableExceptions = false;
    protected $enableExceptionTrace = false;

    /**
     * Enable exceptions
     * 
     * @param  bool|boolean $bool bool
     * @return void
     */
    public function enableExceptions(bool $bool = true)
    {
        $this->enableExceptions = $bool;
    }

    /**
     * Check exceptions
     * 
     * @return boolean
     */
    public function isExceptionsEnabled() : bool
    {
        return $this->enableExceptions;
    }

    /**
     * Enable exception trace
     * 
     * @param  bool|boolean $bool bool
     * @return void
     */
    public function enableExceptionTrace(bool $bool = true)
    {
        $this->enableExceptionTrace = $bool;
    }

    /**
     * Check exception trace
     * 
     * @return boolean
     */
    public function isExceptionTraceEnabled() : bool
    {
        return $this->enableExceptionTrace;
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
     * @return array
     */
    public function renderErrorMessage(string $message, Throwable $exception = null) : array
    {
        $translator = $this->getTranslator();
        
        $data = array();
        $message = is_null($exception) ? $message : $exception->getMessage();

        $data['error']['message'] = $translator->translate($message);

        if (is_object($exception) && $this->isExceptionsEnabled()) {
            $data['exception'] = [
                'type' => get_class($exception),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
            if ($this->isExceptionTraceEnabled()) {
                $data['exception']['trace'] = explode("\n", $exception->getTraceAsString());
            }
        }
        return $data;
    }

    /**
     * Returns to response class
     * 
     * @return string
     */
    public function getResponseClass() : string
    {
        return '\Zend\Diactoros\Response\JsonResponse';
    }
}