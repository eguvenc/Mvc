<?php

namespace View\Controller;

use Obullo\Http\{
	Controller,
	SubRequestInterface as SubRequest
};
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ViewController extends Controller
{
    public function __construct(Request $request, SubRequest $subRequest = null)
    {
        $this->request = $request;
        $this->subRequest = $subRequest;
    }

    public function header() : Response
    {
        return new HtmlResponse($this->renderView('_HeaderNavbar.phtml'));
    }

    public function footer() : Response
    {
    	return new HtmlResponse('<footer class="footer">
            <div class="container">
              <p>&nbsp;&nbsp;</p>
            </div>
        </footer>');
    }
}