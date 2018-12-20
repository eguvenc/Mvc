<?php

namespace Obullo\View\Helper;

use Psr\Container\ContainerInterface;
use Obullo\Http\{
    Kernel,
	Bundle,
    SubRequest,
    ControllerResolver
};
/**
 * Render sub view helper
 */
class RenderSubView
{
	protected $container;

	/**
	 * Constructor
	 * 
	 * @param ContainerInterface $container container
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Invoke 
	 * 
	 * @param  mixed $handler
	 * @param  array $params array
	 * 
	 * @return response
	 */
    public function __invoke(string $handler, $params = [])
    {
    	$request = new SubRequest();
        $request = $request->withMethod('GET');
        $request = $request->withAttribute('handler', $handler);
        $request = $request->withAttribute('params', $params);

        $router = $this->container->get('router');
        $events = $this->container->get('events');

        $kernel = new Kernel($events, $router, new ControllerResolver($this->container));
        $response = $kernel->handleSubRequest($request);
        return $response->getBody();
    }
}