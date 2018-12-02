<?php

namespace Obullo\Http;

use Zend\Diactoros\ServerRequest;
use Obullo\Http\Exception\{
	InvalidArgumentException,
	RuntimeException
};
/**
 * Sub request
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class SubRequest extends ServerRequest implements SubRequestInterface
{
}