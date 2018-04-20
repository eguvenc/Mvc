<?php

namespace Obullo\Mvc;

use Obullo\Router\{
	Router,
	Generator
};
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Url
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Url
{
	protected $router;

	/**
	 * Constructor
	 * 
	 * @param Router $router router
	 */
	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	public function create(string $url, $params = array())
	{
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme != null) {
            return $url;
        }
        $generator = new Generator($this->router->getCollection());
        return $generator->generate($url, $params);
	}

	public function redirect() : Response
	{

	}
}