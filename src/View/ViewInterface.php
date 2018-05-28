<?php

namespace Obullo\Mvc\View;

use Psr\Http\Message\ResponseInterface as Response;

interface ViewInterface
{
    /**
     * Render view as string
     * 
     * @param  string $filename template name
     * @param  array  $data     template data
     * 
     * @return string
     */
    public function render($nameOrModel, $data = null) : string;
}