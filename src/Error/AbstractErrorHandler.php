<?php

namespace Obullo\Error;

use Throwable;
use Zend\EventManager\{
    Event,
    EventManager
};
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Abstract error handler
 * 
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
abstract class AbstractErrorHandler
{
    protected $events;
    protected $bundleName;
    protected $errorTemplate;
    protected $errorTemplateName;

    /**
     * Handle exception & Emit response
     * 
     * @param  Throwable $exception error
     * 
     * @return object
     */
    abstract public function handle(Throwable $exception);

    /**
     * Constructor
     * 
     * @param string $bundleName bundle name
     * @param object $events event manager
     */
    public function __construct(string $bundleName, EventManager $events)
    {
        $this->events = $events;
        $this->bundleName = $bundleName;
    }

    /**
     * Set view renderer
     * 
     * @param object $view view
     */
    public function setErrorTemplate(ErrorTemplate $errorTemplate)
    {
        $this->errorTemplate = $errorTemplate;
    }

    /**
     * Returns to error template class
     * 
     * @return object
     */
    public function getErrorTemplate() : ErrorTemplate
    {
        return $this->errorTemplate;
    }

    /**
     * Set error template name
     * 
     * @param string $errorTemplateName name
     */
    public function setErrorTemplateName(string $errorTemplateName)
    {
        $this->errorTemplateName = $errorTemplateName;
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
    public function setExceptionHandler(string $handler)
    {
        $exp = explode('::', $handler);
        set_exception_handler(array($this, $exp[1]));
    }

    /**
     * Handle application errors
     *
     * @param mixed $error mostly exception object
     *
     * @return object response
     */
    protected function handleError(Throwable $exception) : Response
    {        
        $events = $this->getEvents();
        $event = new Event;
        $event->setName($this->bundleName.'.error.handler'); // Create event for Error Listener
        $event->setParam('exception', $exception);
        $event->setTarget($this);
        $events->triggerEvent($event);

        $errorTemplate = $this->getErrorTemplate();
        $errorTemplate->setStatusCode(500);
        $errorTemplate->setMessage('An error was encountered');
        $errorTemplate->setException($exception);

        return $errorTemplate->renderView($this->errorTemplateName);
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
}