<?php

namespace Obullo\Mvc;

use Obullo\Mvc\Container\{
    ContainerAwareTrait,
    ContainerProxyTrait,
    ContainerAwareInterface
};
/**
 * Controller
 *
 * @copyright Obullo
 * @license   http://opensource.org/licenses/MIT MIT license
 */
class Controller implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ContainerProxyTrait;
}