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

        $config = $container
            ->get('loader')
            ->loadConfigFile('errors.yaml');

        if ($this->err != '') {
            $status = $this->err;
            if (isset($config[$this->err.'_error'])) {
                $error = $config[$this->err.'_error'];
            } else {
                $status = 500;
                $error = [
                    'title' => $config['app_error']['title'],
                    'message' => $this->err
                ];
            }
            $error['message'] = empty($this->msg) ? $error['message'] : $this->msg;

            $response = $container->get('error')
                ->renderErrorResponse($error['title'], $error['message'], $status, $this->headers);

            $result = $this->getContainer()
                ->get('events')
                ->trigger('error.response',null,$response);

            $errorResponse = $result->last();
            return $errorResponse;
        }

        return $handler->handle($request);
    }
}