<?php

namespace Obullo\Mvc\Error;

use Throwable;

/**
 * Error strategy interface
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
interface ErrorStrategyInterface
{
    /**
     * Set status
     * 
     * @param int $status http status code
     */
    public function setStatusCode($status);

    /**
     * Returns to status
     * 
     * @return int
     */
    public function getStatusCode();

    /**
     * Render error message
     * 
     * @param  string    $message   message
     * @param  Throwable $exception exception
     * 
     * @return string
     */
    public function renderErrorMessage(string $message, Throwable $exception = null);

    /**
     * Returns to response class
     * 
     * @return string
     */
    public function getResponseClass() : string;
}