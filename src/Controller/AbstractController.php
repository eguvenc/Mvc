<?php

namespace Obullo\Mvc\Controller;

use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerProxyTrait,
    ContainerAwareInterface
};
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract Controller
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
abstract class AbstractController implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ContainerProxyTrait;

	/**
	 * Render
	 * 
	 * @param  mixed  $nameOrModal name or view model
	 * @param  mixed  $data        optional array data
	 * @param  integer $status     http status code
	 * @param  array   $headers    http response headers
	 * 
	 * @return object
	 */
	abstract public function render($nameOrModal, $data = null, $status = 200, $headers = []) : ResponseInterface;

	/**
	 * Redirect
	 * 
	 * @param  string  $uri     uri
	 * @param  integer $status  http status code
	 * @param  array   $headers headers
	 * 
	 * @return object
	 */
	abstract public function redirect($uriOrRouteName = null, $params = []) : ResponseInterface;
}