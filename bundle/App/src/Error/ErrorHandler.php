<?php

namespace App\Error;

use Throwable;
use RuntimeException;
use Psr\Http\Message\ResponseInterface;
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerAwareInterface
};
use Obullo\Http\Bundle;
use Obullo\View\ViewInterface as View;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface as EventManager;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\I18n\Translator\{
    TranslatorAwareInterface,
    TranslatorAwareTrait
};
class ErrorHandler implements ContainerAwareInterface, TranslatorAwareInterface
{
    use ContainerAwareTrait;
    use TranslatorAwareTrait;

    protected $view;
    protected $bundle;
    protected $events;
    protected $errorTemplate;
    protected $notFoundTemplate;

    /**
     * Set bundle
     * 
     * @param object $bundle bundle
     */
    public function setBundle(Bundle $bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * Set view renderer
     * 
     * @param object $view view
     */
    public function setView(View $view)
    {
        $this->view = $view;
    }

    /**
     * Set event manager
     * 
     * @param object $events event manager
     */
    public function setEvents(EventManager $events)
    {
        $this->events = $events;
    }

    /**
     * Returns to event manager
     * 
     * @return object
     */
    public function getEvents() : EventManager
    {
        return $this->events;
    }

    /**
     * Set exception handler
     */
    public function setExceptionHandler()
    {
        set_exception_handler(array($this, 'handle'));
    }

    /**
     * Set 404 template
     * 
     * @param  string $notFoundTemplate template
     * @return void
     */
    public function set404Template(string $notFoundTemplate)
    {
        $this->notFoundTemplate = $notFoundTemplate;
    }

    /**
     * Returns to 404 template
     * 
     * @return string
     */
    public function get404Template() : string
    {
        return $this->notFoundTemplate;
    }

    /**
     * Set error template
     * 
     * @param string $errorTemplate template
     */
    public function setErrorTemplate(string $errorTemplate)
    {
        $this->errorTemplate = $errorTemplate;
    }

    /**
     * Returns to error template
     * 
     * @return string
     */
    public function getErrorTemplate() : string
    {
        return $this->errorTemplate;
    }

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
     * Handle application errors
     *
     * @param mixed $error mostly exception object
     *
     * @return object response
     */
    protected function handleError(Throwable $exception)
    {        
        $events = $this->getEvents();

        $event = new Event;
        $event->setName($this->bundle->getName().'.error.handler'); // Create event for Error Listener
        $event->setParam('exception', $exception);
        $event->setTarget($this);

        $events->triggerEvent($event);

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
        $translator = $this->getTranslator();
    
        $data = array();
        $data['message'] = $message;
        $data['translator'] = $translator;
        $data['e'] = $exception;

        if ($status == '404') {
            return new HtmlResponse($this->view->render($this->get404Template(), $data), $status, $headers);
        }
        return new HtmlResponse($this->view->render($this->getErrorTemplate(), $data), $status, $headers);
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
        $level = error_reporting();
        if ($level > 0) {
            echo $response->getBody();
        }
    }
}