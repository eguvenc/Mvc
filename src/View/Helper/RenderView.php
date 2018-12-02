<?php

namespace Obullo\View\Helper;

use Psr\Container\ContainerInterface;

/**
 * Render view helper
 */
class RenderView
{
	protected $container;

	/**
	 * Constructor
	 * 
	 * @param ContainerInterface $container container
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Invoke 
	 * 
	 * @param  string name
	 * @param  array $params array
	 * 
	 * @return response
	 */
    public function __invoke(string $name, $data = [])
    {
        $html = $this->container->get('html');

        return $html->render($name, $data);
    }
}