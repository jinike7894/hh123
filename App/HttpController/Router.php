<?php

namespace App\HttpController;

use EasySwoole\Component\Context\ContextManager;

// 别删，下面有举例
use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        /*
          * eg path : /router/index.html  ; /router/ ;  /router
         */
        $routeCollector->get('/router', '/test');
        /*
         * eg path : /closure/index.html  ; /closure/ ;  /closure
         */
        $routeCollector->get('/closure', function (Request $request, Response $response) {
            $response->write('this is closure router');
            //不再进入控制器解析
            return false;
        });

        /*$routeCollector->addRoute(['GET', 'POST'], '/Api/Common/Payment/callback/{objName}', function (Request $request, Response $response) {
            $response->write(json_encode([
                // 下面对应4种参数获取方式
                'get' => $request->getQueryParams(),
                'post' => $request->getParsedBody(),
                'body' => $request->getBody()->__toString(),
                // 在这里可以获取Url中匹配的参数
                'context' => ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY)
            ]));
            return false;// 不再往下请求,结束此次响应
        });*/

        // 给支付用的伪静态，就不要带Url参数了，因为有的傻逼三方不会拼接Url参数。
        $routeCollector->addRoute(['GET', 'POST'], '/Api/Common/Payment/callback/{objName}', '/Api/Common/Payment/callback');

    }
}