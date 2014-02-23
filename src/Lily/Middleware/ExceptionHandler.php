<?php
/**
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Middleware;

use Lily\Util\Response;

use Exception;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

class ExceptionHandler
{
    private $handler;

    public function __construct($config = array())
    {
        if (isset($config['handler'])) {
            $this->handler = $config['handler'];
        } else {
            $this->exceptionHandler()->register();
        }
    }

    private function exceptionHandler()
    {
        $exceptionHandler = new Run;

        if (PHP_SAPI === 'cli') {
            $exceptionHandler->pushHandler(new JsonResponseHandler);
        } else {
            $exceptionHandler->pushHandler(new PrettyPageHandler);
        }

        return $exceptionHandler;
    }

    private function defaultHandler()
    {
        $exceptionHandler = $this->exceptionHandler();

        return function ($request) use ($exceptionHandler) {
            $exceptionHandler->allowQuit(FALSE);
            $exceptionHandler->writeToOutput(FALSE);

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
