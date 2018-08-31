<?php

namespace Obullo\Mvc\Http;

use Psr\Http\{
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface as RequestHandler,
    Message\ResponseInterface,
    Message\ServerRequestInterface as Request
};
use Interop\Container\ContainerInterface;
use Obullo\Mvc\Http\Kernel;
use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use App\Middleware\Error;

/**
 * SendResponse middleware
 *
 * @copyright 2018 Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class SendResponse implements MiddlewareInterface,ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $kernel;

    /**
     * Constructor
     * 
     * @param Application $application application
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
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

        $router = $container->get('router');
        $events = $container->get('events');

        $response = null;
        if ($router->hasMatch()) {
            $response = $this->kernel->dispatch($request);
            if ($response instanceof ResponseInterface) {
                return $response;
            }
        }
        return $handler->process(new Error('404'));
    }
}