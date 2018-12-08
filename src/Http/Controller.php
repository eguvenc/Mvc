<?php

namespace Obullo\Http;

use Obullo\Http\HttpControllerInterface;
use Obullo\Container\{
    ContainerAwareTrait,
    ContainerProxyTrait,
    ContainerAwareInterface
};
use Psr\Http\Message\ResponseInterface;

/**
 * Default Controller
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Controller implements HttpControllerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public $middlewareManager;

    /**
     * Returns to new middleware manager
     * 
     * @return object
     */
    public function getMiddlewareManager()
    {
    	$this->middlewareManager = new MiddlewareManager($this);
    	return $this->middlewareManager;
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