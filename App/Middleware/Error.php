<?php

namespace App\Middleware;

use Throwable;
use Exception;
use RuntimeException;
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface as Request,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler
};
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
class Error implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $code;
    protected $message;
    protected $headers;
    
    /**
     * Constructor
     * 
     * @param integer $status
     * @param string  $message optional
     * @param array   $headers optional
     */
    public function __construct($code, $message = null, $headers = array())
    {
        $this->code = $code;
        $this->message = empty($message) ? $code : $message;
        $this->headers = $headers;
    }

    /**
     * Process request
     *
     * @param ServerRequestInterface  $request  request
     * @param RequestHandlerInterface $handler
     *
     * @return object ResponseInterface
     */
    public function process(Request $request, RequestHandler $handler) : ResponseInterface
    {
        $container = $this->getContainer();

        $response = $container->get('error')
            ->render($this->message, $this->code, $this->headers);

        return $response;
    }
}