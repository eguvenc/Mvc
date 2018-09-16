<?php

namespace Obullo\View\Helper;

use Obullo\View\Escaper\AbstractHelper;

/**
 * Helper for escaping values
 */
class EscapeUrl extends AbstractHelper
{
    /**
     * Escape a value for current escaping strategy
     *
     * @param  string $value
     * @return string
     */
    protected function escape($value)
    {
        return $this->getEscaper()->escapeUrl($value);
    }
}