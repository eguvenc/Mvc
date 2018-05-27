<?php

namespace Obullo\Mvc;

use Obullo\Mvc\Controller\AbstractController;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\{
	HtmlResponse,
	JsonResponse,
	RedirectResponse
};
/**
 * Default Controller
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Controller extends AbstractController
{
	/**
	 * Render
	 * 
	 * @param  mixed  $nameOrModal name or zend view model
	 * @param  mixed  $data        optional array data
	 * @param  integer $status     http status code
	 * @param  array   $headers    http response headers
	 * 
	 * @return object
	 */
	public function render($nameOrModal, $data = null, $status = 200, $headers = []) : ResponseInterface
	{
		$html = $this->view->render($nameOrModal, $data);

        return new HtmlResponse($html, $status, $headers);
	}

	/**
	 * Redirect
	 * 
	 * @param  string  $uri     uri
	 * @param  integer $status  http status code
	 * @param  array   $headers headers
	 * 
	 * @return object
	 */
	public function redirect($uriOrRouteName = null, $params = []) : ResponseInterface
	{
		$collection = $this->router->getCollection();
		
		if ($collection->get($uriOrRouteName)) {
			$uriOrRouteName = $this->router->url($uriOrRouteName, $params);
		}
		return new RedirectResponse($uriOrRouteName);
	}
}