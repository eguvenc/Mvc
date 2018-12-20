<?php

namespace App;

use Throwable;
use Psr\Http\Message\ResponseInterface as Response;
use Obullo\Error\AbstractErrorHandler;

class ErrorHandler extends AbstractErrorHandler
{
    /**
     * Handle exception & Emit response
     * 
     * @param  Throwable $exception error
     * 
     * @return object
     */
    public function handle(Throwable $exception)
    {
        $response = $this->handleError($exception);

        $this->send($response);
    }

    /**
     * Emit response
     *     
     * @return void
     */
    protected function send(Response $response)
    {
        $this->emitHeaders($response);
        $this->emitBody($response);
    }

    /**
     * Emit body
     * 
     * @return void
     */
    protected function emitBody($response)
    {
        $level = error_reporting();
        if ($level > 0) {
            echo $response->getBody();
        }
    }
}