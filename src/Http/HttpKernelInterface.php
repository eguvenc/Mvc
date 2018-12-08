<?php

namespace Obullo\Http;

use Psr\{
    Http\Message\ServerRequestInterface as Request,
    Http\Message\ResponseInterface as Response
};
use Obullo\Http\SubRequestInterface as SubRequest;

/**
 * Http kernel
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
interface HttpKernelInterface
{
    /**
     * Handle request & http process
     * 
     * @param Request $request request
     * 
     * @return object
     */
    public function handleRequest(Request $request) : Response;

    /**
     * Handle SubRequest & hmvc process
     * 
     * @param  SubRequest $request subrequest
     * 
     * @return object
     */
    public function handleSubRequest(SubRequest $request) : Response;

    /**
     * Returns to application middlewares
     * 
     * @return array
     */
    public function getMiddlewares(): array;
}