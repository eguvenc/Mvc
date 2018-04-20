<?php

namespace Obullo\Mvc\View;

use Psr\Http\Message\ResponseInterface as Response;

interface ViewInterface
{
	/**
     * Render template as html response
     * 
     * @param  string  $filename template name
     * @param  array   $data     template data
     * @param  integer $status   response status
     * @param  array   $headers  response headers
     * 
     * @return HtmlResponse
     */
    public function render(string $filename, $data = array(), $status = 200, array $headers = []) : Response;

    /**
     * Render view as string
     * 
     * @param  string $filename template name
     * @param  array  $data     template data
     * 
     * @return string
     */
    public function renderView(string $filename, $data = array()) : string;
}