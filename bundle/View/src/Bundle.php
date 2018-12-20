<?php

namespace View;

use Obullo\Http\{
    HttpBundle,
    SubRequestInterface as SubRequest
};
class Bundle extends HttpBundle
{
    public function onBootstrap()
    {
        $this->setErrorHandler();

        // Configure container for controllers.
        // 
        $this->container->configure(
            [
                'factories' => [
                    'View\Controller\ViewController' => '\\'.__NAMESPACE__.'\LazyControllerFactory'
                ]
            ]
        );
    }

    protected function setErrorHandler()
    {        
        $request = $this->getRequest();

        // set error listeners only for master requests.   
        // 
        if (false == $request instanceof SubRequest) { 
            $this->container->setFactory('App\ErrorHandler', 'Service\ErrorHandlerFactory');
            $this->container->build('App\ErrorHandler');
        }
    }
}
