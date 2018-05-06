<?php

namespace Obullo\Mvc\View\Helpers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Url
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Url
{
	protected $container;

	/**
	 * Constructor
	 * 
	 * @param ContainerInterface $container container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
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
        return $this->container->get('router')
        	->url($url, $params);
    }
}