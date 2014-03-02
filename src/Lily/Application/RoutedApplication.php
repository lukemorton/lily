<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Application;

use Lily\Util\Response;

/**
 * An application handler that provides routing to other handlers or responses.
 */
class RoutedApplication
{
    // Defines the pattern of a :param
    const REGEX_PARAM_KEY = ':([a-zA-Z0-9_]++)';

    // What can be part of a :param value
    const REGEX_PARAM_VALUE = '(?P<$1>[^/.,;?\n]++)';

    // Splat regex
    const REGEX_SUPER_SPLAT = '\*\*';
    const REGEX_SUPER_SPLAT_VALUE = '.+';
    const REGEX_SPLAT = '\*';
    const REGEX_SPLAT_VALUE = '[^/]+';

    // What must be escaped in the route regex
    const REGEX_ESCAPE = '[.\\+?[^\\]${}=!|<>]';

    private $routes = array();

    public function __construct($config = NULL)
    {
        if (isset($config['routes'])) {
            $this->routes = $config['routes'];
        }
    }
    
    private function normaliseRoute($route)
    {
        if ( ! isset($route[3])) {
            $route[3] = array();
        }

        $route[3]['app'] = $this;

        return $route;
    }

    private function methodMatches($request, $method)
    {
        return $method === NULL OR $request['method'] === $method;
    }

    private function escapeLiterals($uri)
    {
        return
            preg_replace(
                '#'.static::REGEX_ESCAPE.'#',
                '\\\\$0',
                $uri);
    }

    private function addOptionalPartRegex($uri)
    {
        if (strpos($uri, '(') !== FALSE) {
            // Make optional parts of the URI non-capturing and optional
            $uri =
                str_replace(
                    array('(', ')'),
                    array('(?:', ')?'),
                    $uri);
        }

        return $uri;
    }

    private function addParamRegex($uri)
    {
        return 
            preg_replace(
                '#'.static::REGEX_PARAM_KEY.'#',
                static::REGEX_PARAM_VALUE,
                $uri);
    }

    private function addSuperSplatRegex($uri)
    {
        return
            preg_replace(
                '#'.static::REGEX_SUPER_SPLAT.'#',
                static::REGEX_SUPER_SPLAT_VALUE,
                $uri);
    }

    private function addSplatRegex($uri)
    {
        return
            preg_replace(
                '#'.static::REGEX_SPLAT.'#',
                static::REGEX_SPLAT_VALUE,
                $uri);
    }

    private function wrapRegex($uri)
    {
        return '#^'.$uri.'$#uD';
    }

    private function uriRegex($uri)
    {
        return // blame php 5.3 support for this nesting:
            $this->wrapRegex(
                $this->addSplatRegex(
                    $this->addSuperSplatRegex(
                        $this->addParamRegex(
                            $this->addOptionalPartRegex(
                                $this->escapeLiterals(
                                    $uri))))));
    }

    private function removeNumeric(array $mixedArray)
    {
        $assocArray = array();

        foreach ($mixedArray as $_k => $_v) {
            if (is_string($_k)) {
                $assocArray[$_k] = $_v;
            }
        }

        return $assocArray;
    }

    private function uriMatches($request, $uri)
    {
        if ($uri === NULL) {
            return TRUE;

        // This match might be dangerous if the URL looks like regex... might
        // that happen?
        } elseif ($request['uri'] === $uri) {
            return TRUE;
        }

        $match =
            (bool) preg_match(
                $this->uriRegex($uri),
                $request['uri'],
                $matches);

        if (isset($matches[1])) {
            return $this->removeNumeric($matches);
        }

        return $match;
    }

    private function matchedRequest($request, $method, $uri)
    {
        if ( ! $this->methodMatches($request, $method)) {
            return FALSE;
        }

        $params = $this->uriMatches($request, $uri);

        if ( ! $params) {
            return FALSE;
        }

        if (is_array($params)) {
            $request['route-params'] = $params;
            $request['params'] = $params + $request['params'];
        }

        return $request;
    }

    private function isMap(array $array)
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be map (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

    private function normaliseResponse($response)
    {
        if (is_string($response)) {
            $response = array(
                'status' => 200,
                'headers' => array(),
                'body' => $response,
            );
        } elseif ( ! $this->isMap($response)) {
            list($status, $headers, $body) = $response;
            $response = compact('status', 'headers', 'body');
        }

        return $response;
    }

    private function handlerResponse($handler, $request)
    {
        if (is_callable($handler)) {
            $response = $handler($request);
        } else {
            $response = $handler;
        }

        if ($response === FALSE) {
            return FALSE;
        }
        
        return $this->normaliseResponse($response);

    }

    private function routeResponse($request, $route)
    {
        list($method, $uri, $handler, $additionalRequest) =
            $this->normaliseRoute($route, $this);

        $request += $additionalRequest;

        $request = $this->matchedRequest($request, $method, $uri);

        if ($request === FALSE) {
            return FALSE;
        }

        return $this->handlerResponse($handler, $request);
    }

    private function matchRoute($request, $routes)
    {
        foreach ($routes as $_route) {
            $response = $this->routeResponse($request, $_route);

            if ($response !== FALSE) {
                return $response;
            }
        }        

        return Response::notFound();
    }

    protected function routes()
    {
        return $this->routes;
    }

    public function __invoke($request)
    {
        return $this->matchRoute($request, $this->routes());
    }

    public function uri($name, $params = array())
    {
        $routes = $this->routes();
        $uri = $routes[$name][1];

        foreach ($params as $_k => $_v) {
            $uri = str_replace(":{$_k}", $_v, $uri);
        }

        return $uri;
    }
}
