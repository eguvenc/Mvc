<?php

namespace Obullo\Http;

use Obullo\Http\RequestAwareTrait;
use Obullo\Container\ContainerAwareTrait;

/**
 * Http bundle
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class HttpBundle
{
    use RequestAwareTrait;
    use ContainerAwareTrait;
}