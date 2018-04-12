<?php

namespace Tests\App\Controller;

use Obullo\Mvc\Controller;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\RequestInterface as Request;

class DefaultController extends Controller
{
    public function index(Request $request) : Response
    {
        return new HtmlResponse('Hello World !');
    }

    public function test(Request $request) : Response
    {
    	$locale = $request->getAttribute('locale');

    	return new HtmlResponse($locale);
    }
}