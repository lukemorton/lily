<?php

namespace Lily\Middleware;

use Lily\Util\Response;

use Exception;

use Kint;

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
            ob_start();

            $e = $request['exception'];

            $template = '%s thrown in %s on line %s with message "%s"';
            $filename = $e->getFile();

            if (PHP_SAPI !== 'cli') {
                $template = "<h1>{$template}</h1>";

                Kint::$appRootDirs = array(
                    $_SERVER['DOCUMENT_ROOT'] => 'DOCUMENT_ROOT',
                );

                $filename = Kint::shortenPath($filename);
            }

            echo sprintf(
                $template,
                get_class($e),
                $filename,
                $e->getLine(),
                $e->getMessage());

            if (PHP_SAPI === 'cli') {
                echo $request['exception']->getTraceAsString();
            } else {
                Kint::trace($request['exception']->getTrace());
            }

            return Response::response(500, array(), ob_get_clean());
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
