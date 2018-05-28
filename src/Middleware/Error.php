<?php

namespace Obullo\Mvc\Middleware;

use Throwable;
use Exception;
use RuntimeException;
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface as Request,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler
};
use Obullo\Mvc\Error\ErrorStrategyInterface;
use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
class Error implements MiddlewareInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $err;
    protected $msg;
    protected $headers;
    protected $strategy;

    /**
     * Constructor
     * 
     * @param mixed  $err error
     * @param string $message optional
     * @param array  $headers optional
     */
    public function __construct($err = '', $message = null, $headers = array())
    {
        $this->err = $err;
        $this->msg = $message;
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

        if ($this->err != '') {
            $status = 500;
            if (is_numeric($this->err)) {
                $status = $this->err;
            }
            $message = $this->err;
            $message = empty($this->msg) ? $message : $this->msg;

            $response = $container->get('error')
                ->renderErrorResponse($message, $status, $this->headers);

            $result = $this->getContainer()
                ->get('events')
                ->trigger('error.response',null,$response);

            $errorResponse = $result->last();
            return $errorResponse;
        }

        return $handler->handle($request);
    }
}