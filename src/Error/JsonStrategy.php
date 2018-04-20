<?php

namespace Obullo\Mvc\Error;

use Throwable;

/**
 * Json Strategy
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class JsonStrategy implements ErrorStrategyInterface
{
    protected $status = 500;
    protected $enableExceptions = false;

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
     * @return array
     */
    public function renderErrorMessage(string $title, string $message, Throwable $exception = null) : array
    {
        $data = array();
        $data['error']['title'] = $title;
        $data['error']['message'] = is_null($exception) ? $message : $exception->getMessage();

        if (is_object($exception) && $this->isExceptionsEnabled()) {
            $data['exception'] = [
                'type' => get_class($exception),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
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