<?php

namespace Obullo\Http;

use Obullo\Container\{
    ContainerAwareTrait,
    ContainerProxyTrait,
    ContainerAwareInterface
};
use Psr\Http\{
    Message\ResponseInterface as Response,
    Message\ServerRequestInterface as Request
};
/**
 * Http Controller
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Controller implements HttpControllerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Handler psr7 response
     * 
     * @param  Bundle  $bundle  object
     * @param  Request $request request
     * @param  Kernel  $kernel  http kernel
     * @return void
     */
    public function handlePsr7Response($bundle, Request $request, Kernel $kernel) : Response
    {
    	$bundle = null;
		$router = $kernel->getRouter();
		$route  = $router->getMatchedRoute();
		$response = $kernel->dispatch($request, $route->getArguments());
		return $response;
	}

	/**
	 * Render view
	 * 
	 * @param  mixed  $nameOrModal name or zend view model
	 * @param  mixed  $data        optional array data
	 * 
	 * @return object
	 */
	public function renderView($nameOrModal, $data = null)
	{
		$html = $this->getContainer()->get('view');

		return $html->render($nameOrModal, $data);
	}

	/**
	 * Render url
	 * 
	 * @param  string  $uri     uri
	 * @param  integer $status  http status code
	 * @param  array   $headers headers
	 * 
	 * @return string uri or route name
	 */
	public function url($uriOrRouteName = null, $params = [])
	{
		$router = $this->getContainer()->get('router');
		$collection = $router->getCollection();
		
		if ($collection->get($uriOrRouteName)) {
			$uriOrRouteName = $router->url($uriOrRouteName, $params);
		}
		return $uriOrRouteName;
	}
}