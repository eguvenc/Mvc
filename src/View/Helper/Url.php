<?php

namespace Obullo\View\Helper;

/**
 * Url helper
 */
class Url
{
	protected $router;

	/**
	 * Constructor
	 * 
	 * @param ContainerInterface $container container
	 */
	public function setRouter($router)
	{
		$this->router = $router;
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