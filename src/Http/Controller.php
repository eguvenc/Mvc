<?php

namespace Obullo\Http;

use Obullo\Container\{
    ContainerAwareTrait,
    ContainerProxyTrait,
    ContainerAwareInterface
};
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
class Controller implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ContainerProxyTrait;

	/**
	 * Render view
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
	 * Remder html directly
	 * 
	 * @param  string  $html    html code
	 * @param  integer $status  http status code
	 * @param  array   $headers http response headers
	 * 
	 * @return object
	 */
	public function renderHtml(string $html, $status = 200, $headers = []) : ResponseInterface
	{
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

	/**
	 * Create a JSON response with the given data.
	 * 
	 * @param  array  $data             data
	 * @param  integer $status          http status code
	 * @param  array   $headers         http headers
	 * @param  integer $encodingOptions JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES
	 * 
	 * @return object
	 */
	public function json($data, $status = 200, array $headers = [], $encodingOptions = 79)
	{
		return new JsonResponse($data, $status, $headers, $encodingOptions);
	}
}