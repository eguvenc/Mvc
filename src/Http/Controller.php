<?php

namespace Obullo\Http;

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
class Controller implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ContainerProxyTrait;

	/**
	 * Render view
	 * 
	 * @param  mixed  $nameOrModal name or zend view model
	 * @param  mixed  $data        optional array data
	 * 
	 * @return object
	 */
	public function render($nameOrModal, $data = null)
	{
		return $this->view->render($nameOrModal, $data);
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
		$collection = $this->router->getCollection();
		
		if ($collection->get($uriOrRouteName)) {
			$uriOrRouteName = $this->router->url($uriOrRouteName, $params);
		}
		return $uriOrRouteName;
	}
}