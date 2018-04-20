<?php

namespace Obullo\Mvc\Error;

use Throwable;
use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Obullo\Mvc\Error\ErrorStrategyInterface;
use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
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
		$config = $this->getContainer()
			->get('loader')
			->loadConfigFile('errors.yaml');

		$response = $this->handleError($config, $exception);
		
		$this->emitErrorResponse($response);
	}

    /**
     * Handle application errors
     *
     * @param mixed $error mostly exception object
     *
     * @return object response
     */
    protected function handleError(array $config, Throwable $exception)
    {        
        $this->getContainer()
        	->get('events')
            ->trigger('error.handler',null,$exception);

        return $this->renderErrorResponse(
            $config['app_error']['title'],
            $config['app_error']['message'],
            500,
            array(),
            $exception
        );
    }

    /**
     * Emit response
     * 
     * @param  ResponseInterface $response psr7 response
     * 
     * @return void
     */
    public function emitErrorResponse(ResponseInterface $response)
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
        echo $response->getBody();
    }

    /**
     * Render error response
     * 
     * @param  string $title   title
     * @param  string $message body
     * @param  int    $status  http status code
     * @param  array  $headers http headers
     * 
     * @return object
     */
    public function renderErrorResponse(string $title, $message = null, $status, $headers = array(), Throwable $exception = null) : ResponseInterface
    {
        $this->strategy->setStatusCode($status);

        $message  = $this->strategy->renderErrorMessage($title, $message, $exception);
        $response = $this->strategy->getResponseClass();

        $errorResponse = new $response($message, $status, $headers);

        $result = $this->getContainer()
                ->get('events')
                ->trigger('error.response',null,$errorResponse);

        return $result->last();
    }
}