<?php

namespace Lily\Middleware;

use Lily\Util\Response;

use Exception;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class ExceptionHandler
{
    private $handler;

    public function __construct($handler = NULL)
    {
        $this->handler = $handler;
    }

    private function defaultHandler()
    {
        return function ($request) {
            $exceptionHandler = new Run;
            $exceptionHandler->allowQuit(FALSE);
            $exceptionHandler->writeToOutput(FALSE);

            if (PHP_SAPI === 'cli') {
                $exceptionHandler->pushHandler(new JsonResponseHandler);
            } else {
                $exceptionHandler->pushHandler(new PrettyPageHandler);
            }

            $body = $exceptionHandler->handleException($request['exception']);
            return Response::response(500, $body);
        };
    }

    public function __invoke($handler)
    {
        $errorHandler = $this->handler;

        if ($errorHandler === NULL) {
            $errorHandler = $this->defaultHandler();
        }

        return function ($request) use ($handler, $errorHandler) {
            try {
                $response = $handler($request);
            } catch (Exception $e) {
                $request['exception'] = $e;
                $response = $errorHandler($request);
            }

            return $response;
        };
    }
}
