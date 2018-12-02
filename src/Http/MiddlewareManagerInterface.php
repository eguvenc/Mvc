<?php

namespace Obullo\Http;

/**
 * Middleware manager interface
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
interface MiddlewareManagerInterface
{
    /**
     * Add middleware
     * 
     * @param string $name middleware
     */
    public function add(string $name);

    /**
     * Set arguments
     * 
     * @param  string $name argument name
     * @param  mixed $arg arguments
     * @return object
     */
    public function addArguments(array $args);

    /**
     * Include methods
     * 
     * @param string $name method
     * @return object
     */
    public function addMethod(string $name);

    /**
     * Exclude methods
     * 
     * @param string $name method
     * @return object
     */
    public function removeMethod(string $name);
}