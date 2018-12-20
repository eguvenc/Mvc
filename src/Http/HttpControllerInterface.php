<?php

namespace Obullo\Http;

/**
 * Http controller
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
interface HttpControllerInterface
{
	/**
	 * Render view
	 * 
	 * @param  mixed  $nameOrModal name or zend view model
	 * @param  mixed  $data        optional array data
	 * 
	 * @return object
	 */
	public function renderView($nameOrModal, $data = null);

	/**
	 * Render url
	 * 
	 * @param  string  $uri     uri
	 * @param  integer $status  http status code
	 * @param  array   $headers headers
	 * 
	 * @return string uri or route name
	 */
	public function url($uriOrRouteName = null, $params = []);
}