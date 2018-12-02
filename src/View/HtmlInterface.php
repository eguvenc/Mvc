<?php

namespace Obullo\View;

use Psr\Http\Message\ResponseInterface as Response;

interface HtmlInterface
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