<?php

namespace Lily\Application;

use Lily\Util\Response;

class RoutedApplication
{
    // Defines the pattern of a :param
    const REGEX_KEY = ':([a-zA-Z0-9_]++)';

    // What can be part of a :param value
    const REGEX_SEGMENT = '[^/.,;?\n]++';

    // What must be escaped in the route regex
    const REGEX_ESCAPE = '[.\\+*?[^\\]${}=!|<>]';

    private $routes = array();

    public function __construct(array $routes = NULL)
    {
        if ($routes !== NULL) {
            $this->routes = $routes;
        }
    }

    protected function routes()
    {
        return $this->routes;
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

    private function uriRegex($uri)
    {
        // The URI should be considered literal except for
        // keys and optional parts
        // Escape everything preg_quote would escape except
        // for : ( ) < >
        $expression = preg_replace(
            '#'.static::REGEX_ESCAPE.'#',
            '\\\\$0',
            $uri);

        if (strpos($expression, '(') !== FALSE) {
            // Make optional parts of the URI non-capturing
            // and optional
            $expression = str_replace(
                array('(', ')'),
                array('(?:', ')?'),
                $expression);
        }

        // Insert default regex for keys
        $replace = '#'.static::REGEX_KEY.'#';
        $expression = preg_replace(
            $replace,
            '(?P<$1>'.static::REGEX_SEGMENT.')',
            $expression);

        return '#^'.$expression.'$#uD';
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

        // This match might be dangerous if the URL looks like
        // regex... might that happen?
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

    public function __invoke($request)
    {
        $routes = $this->routes();

        foreach ($routes as $_route) {
            list($method, $uri, $handler, $additionalRequest) =
                $this->normaliseRoute($_route, $this);

            $request += $additionalRequest;

            if ( ! $this->methodMatches($request, $method)) {
                continue;
            }

            $params = $this->uriMatches($request, $uri);

            if ( ! $params) {
                continue;
            }

            if (is_array($params)) {
                $request['route-params'] = $params;

                if (isset($request['params']))
                {
                    $request['params'] = $params + $request['params'];
                }
                else
                {
                    $request['params'] = $params;
                }
            }

            if (is_callable($handler)) {
                $response = $handler($request);
            } else {
                $response = $handler;
            }

            if ($response !== FALSE) {
                return $response;
            }
        }

        return Response::notFound();
    }

    public function uri($name, array $params = array())
    {
        $routes = $this->routes();
        $uri = $routes[$name][1];

        foreach ($params as $_k => $_v) {
            $uri = str_replace(":{$_k}", $_v, $uri);
        }

        return $uri;
    }
}
