<?php

namespace Obullo\Error;

use Throwable;
use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Zend\EventManager\Event;

/**
 * Error handler
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class ErrorHandler implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $strategy;

    /**
     * Set response strategy
     * 
     * @param ErrorStrategyInterface $strategy strategy
     */
    public function setResponseStrategy(ErrorStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Handle
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
     * Handle application errors
     *
     * @param mixed $error mostly exception object
     *
     * @return object response
     */
    protected function handleError(Throwable $exception)
    {        
        $container = $this->getContainer();

        $event = new Event;
        $event->setName('error.handler');
        $event->setParam('exception', $exception);
        $event->setTarget($this);

        $container->get('events')
            ->triggerEvent($event);

        return $this->render(
            'An error was encountered',
            500,
            array(),
            $exception
        );           
    }

    /**
     * Render error response
     * 
     * @param  string $message body
     * @param  int    $status  http status code
     * @param  array  $headers http headers
     * 
     * @return object
     */
    public function render($message = null, $status, $headers = array(), Throwable $exception = null) : ResponseInterface
    {
        $this->strategy->setStatusCode($status);

        $message  = $this->strategy->renderErrorMessage($message, $exception);
        $response = $this->strategy->getResponseClass();

        return new $response($message, $status, $headers);
    }

    /**
     * Emit response
     *     
     * @return void
     */
    public function send(ResponseInterface $response)
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            throw new RuntimeException('Output has been emitted previously; cannot emit response');
        }
        $this->emitHeaders($response);
        $this->emitBody($response);
    }

    /**
     * Emit headers
     *
     * @return void
     */
    protected function emitHeaders($response)
    {
        $statusCode = $response->getStatusCode();
        foreach ($response->getHeaders() as $header => $values) {
            $name = $header;
            foreach ($values as $value) {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), true, $statusCode);
            }
        }
    }

    /**
     * Emit body
     * 
     * @return void
     */
    protected function emitBody($response)
    {
        $event = new Event;
        $event->setName('error.output');
        $event->setTarget($this);

        $container = $this->getContainer();
        $result = $container->get('events')
            ->triggerEvent($event)
            ->last();

        if ($result) {
            echo $response->getBody();
        }
    }
}