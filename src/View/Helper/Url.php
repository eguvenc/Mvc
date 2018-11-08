<?php

namespace Obullo\View\Helper;

use Obullo\Router\Router;

/**
 * Url helper
 */
class Url
{
	protected $router;

	/**
	 * Router
	 * 
	 * @param Router $router object
	 * 
	 * @return object self
	 */
	public function setRouter(Router $router)
	{
		$this->router = $router;

		return $this;
	}

	/**
	 * Invoke 
	 * 
	 * @param  string $url    string
	 * @param  array  $params array
	 * 
	 * @return mixed
	 */
    public function __invoke(string $url, $params = [])
    {
    	$scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme != null) {
            return $url;
        }
        return $this->router->url($url, $params);
    }
}