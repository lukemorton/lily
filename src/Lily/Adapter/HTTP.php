<?php
/*
 * Lily, a web application library
 *
 * (c) Luke Morton <lukemorton.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lily\Adapter;

/**
 * Execute your application handler in a HTTP environment.
 *
 * By accessing PHP globals this object builds up a request array representing
 * commonly used HTTP headers (`$_SERVER['CONTENT_LENGTH']`), parameters
 * (`$_GET`, `$_POST`, `$_COOKIE`) and URL (`$_SERVER['PATH_INFO']).
 *
 * Your application handler should then return a response object containing
 * `status`, `headers` and `body` keys. You can use `Lily\Util\Response` to
 * make this easier for yourself.
 *
 *     (new Lily\Adapter\HTTP)->run(
 *         function ($request) {
 *             if ($request['uri'] === '/') {
 *                 return Lily\Util\Response::ok('Hello World');
 *             }
 *             
 *             return Lily\Util\Response::notFound('Page not found, sorry');
 *         });
 */
class HTTP
{
    /**
     * An array of HTTP status code => HTTP status text.
     */
    private static $statusText = array(
        '100' => 'Continue',
        '101' => 'Switching Protocols',

        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',

        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '307' => 'Temporary Redirect',

        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request Uri Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',

        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
    );

    private $forceSlowHeaders = FALSE;
    private $returnResponse = FALSE;

    /**
     * Takes an optional map of settings, with these keys:
     *
     *  - `forceSlowHeaders`: set `TRUE` to ensure `::slowHeaders()` is used
     *  - `returnResponse`: set `TRUE` to return response from `::run()`
     *    instead of sending headers
     */
    public function __construct(array $config = NULL)
    {
        foreach (array('forceSlowHeaders', 'returnResponse') as $_setting) {
            if (isset($config[$_setting]) AND $config[$_setting] === TRUE) {
                $this->{$_setting} = TRUE;
            }
        }
    }

    /**
     * Are we forcing the use of `::slowHeaders()`?
     */
    private function forceSlowHeaders()
    {
        return $this->forceSlowHeaders;
    }

    /**
     * Are we returning a response instead of sending one?
     */
    private function returnResponse()
    {
        return $this->returnResponse;
    }

    /**
     * Returns server host.
     */
    private function host()
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME']; 
        }
    }

    /**
     * Returns server port.
     */
    private function port()
    {
        if (isset($_SERVER['SERVER_PORT'])) {
            return $_SERVER['SERVER_PORT']; 
        }
    }

    /**
     * Returns server IP address.
     */
    private function addr()
    {
        if (isset($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR']; 
        }
    }

    /**
     * Returns remote IP address.
     */
    private function remote_addr()
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR']; 
        }
    }

    /**
     * Returns HTTP scheme, i.e. 'https' or 'http'. Defaults to 'http'.
     */
    private function scheme()
    {
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $parts = explode('/', $_SERVER['SERVER_PROTOCOL']);
            return strtolower($parts[0]);
        } else {
            return 'http';
        }
    }

    /**
     * Returns HTTP request method. Defaults to `'GET'`.
     */
    private function method()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        } else {
            return 'GET';
        }
    }

    /**
     * Returns the URI for the HTTP request via various methods depending on
     * what is provided by `$_SERVER`. This often depends on your environment
     * or server you are running this library on.
     */
    private function uri()
    {
        if ( ! empty($_SERVER['PATH_INFO'])) {
            $uri = $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];

            // We parse the REQUEST_URI since it can contain GET parameters.
            //
            // `parse_url()` cannot parse malformed URLs like:
            // 
            //     http://localhost/http://example.com/index.php
            //
            // Only if truthy do we use what `parse_url()` it returns, otherwise
            // we default to the raw `REQUEST_URI`.
            $request_uri = parse_url($uri, PHP_URL_PATH);

            if ($request_uri) {
                $uri = $request_uri;
            }

            $uri = rawurldecode($uri);
        } elseif (isset($_SERVER['PHP_SELF'])) {
            $uri = $_SERVER['PHP_SELF'];
        } elseif (isset($_SERVER['REDIRECT_URL'])) {
            $uri = $_SERVER['REDIRECT_URL'];
        } else {
            $uri = NULL;
        }

        return $uri;
    }

    /**
     * Returns an array of headers with hyphenated-lowercase from the PHP
     * `$_SERVER` variable.
     */
    private function slowHeaders()
    {
        $headers = array();

        if ( ! empty($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        if ( ! empty($_SERVER['CONTENT_LENGTH'])) {
            $headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
        }

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') !== 0) {
                continue;
            }

            $key =
                strtolower(
                    str_replace(
                        array('HTTP_', '_'),
                        array('', '-'),
                        $key));
            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Use `apache_request_headers()` or `http_get_request_headers()` to
     * retrieve headers as an array if either is available and the
     * `forceSlowHeaders` setting is not set to `TRUE`. Otherwise
     * `::slowHeaders()` will be called.
     *
     * Also adds a `cookies` key to the header array from `$_COOKIE`.
     *
     * Please note keys will always be lowercase-hyphenated.
     */
    private function headers()
    {
        if ( ! $this->forceSlowHeaders()) {
            if (function_exists('apache_request_headers')) {
                $headers = 
                    array_change_key_case(
                        apache_request_headers());
            } elseif (extension_loaded('http')) {
                $headers =
                    array_change_key_case(
                        http_get_request_headers());
            }
        }

        if ( ! isset($headers)) {
            $headers = $this->slowHeaders();
        }

        $headers['cookies'] = $_COOKIE;

        return $headers;
    }

    /**
     * An associative array of HTTP query string key => values.
     */
    private function query()
    {
        return $_GET;
    }

    /**
     * An associative array of POST form data.
     */
    private function post()
    {
        return $_POST;
    }

    /**
     * A combination of arrays returned by `::post()` and `::get()`. The former
     * having precedence over the latter.
     */
    private function params()
    {
        return $_POST + $_GET;
    }

    /**
     * Array of files submitted sent in the HTTP request.
     */
    private function files()
    {
        return $_FILES;
    }

    /**
     * Return associative array representing the HTTP request.
     *
     * Please note all keys are lowercase. For the request array – including
     * it's `'headers'` map – this is the expected format.
     */
    private function parseRequest()
    {
        $headers = $this->headers();

        return array(
            'host' => $this->host(),
            'port' => $this->port(),
            'addr' => $this->addr(),

            'remote-addr' => $this->remote_addr(),
            
            'scheme' => $this->scheme(),
            'method' => $this->method(),
            'uri' => $this->uri(),
            'headers' => $headers,

            'type' => isset($headers['content-type']) ? $headers['content-type'] : NULL,
            'length' => isset($headers['content-length']) ? $headers['content-length'] : NULL,

            'query' => $this->query(),
            'post' => $this->post(),
            'params' => $this->params(),
            'files' => $this->files(),
        );
    }

    /**
     * Add default `Content-Type` and `Content-Length` headers if these keys are
     * not defined. `Content-Type` defaults to `'text/html'` whereas
     * `Content-Length` is calculated from the length of `$response['body']`.
     */
    private function addDefaultHeadersToResponse(array $response)
    {
        $headers = $response['headers'];

        if (empty($headers['Content-Type'])) {
            $headers['Content-Type'] = 'text/html';
        }

        if (empty($headers['Content-Length'])) {
            if (function_exists('mb_strlen')) {
                $headers['Content-Length'] = mb_strlen($response['body']);
            } else {
                $headers['Content-Length'] = strlen($response['body']);
            }
        }

        $response['headers'] = $headers;
        return $response;
    }

    /**
     * Send status header.
     */
    private function sendStatusHeader($status)
    {
        header(static::$statusText[$status], TRUE, $status);
    }

    /**
     * Send cookies array from `$response['headers']['Set-Cookie']`. Should be
     * a numeric array of associative arrays representing individual cookies.
     *
     * Each cookie can have the following keys:
     *
     *  - `'name'`, the name of the cookie
     *  - `'value'`, the value of the cookie
     *  - `'expires'`, the unix timestamp the cookie expires
     *  - `'secure'`, indicates that the cookie should only be sent via HTTPS
     *  - `'domain'`, the domain that the cookie is available to
     *  - `'path'`, the path prefix the cookie will be available to
     *  - `'http-only'`, when TRUE the cookie can only be accessed over HTTP(S)
     *
     * See http://php.net/manual/en/function.setcookie.php for more details.
     */
    private function sendCookies(array $cookies)
    {
        foreach ($cookies as $_c)
        {
            setcookie(
                $_c['name'],
                isset($_c['value']) ? $_c['value'] : '',
                isset($_c['expires']) ? $_c['expires'] : 0,
                isset($_c['path']) ? $_c['path'] : '',
                isset($_c['domain']) ? $_c['domain'] : '',
                isset($_c['secure']) ? $_c['secure'] : FALSE,
                isset($_c['http-only']) ? $_c['http-only'] : FALSE);
        }
    }

    /**
     * Send header field => values.
     */
    private function sendHeaders(array $headers)
    {
        foreach ($headers as $_header => $_values) {
            if ( ! is_array($_values)) {
                $_values = array($_values);
            }

            foreach ($_values as $_value) {
                header("{$_header}: {$_value}");
            }
        }
    }

    /**
     * Send headers and echo body of given `$response` array.
     */
    private function sendResponse(array $response)
    {
        $this->sendStatusHeader($response['status']);

        if (isset($response['headers']['Set-Cookie']))
        {
            $this->sendCookies($response['headers']['Set-Cookie']);
            unset($response['headers']['Set-Cookie']);
        }

        $this->sendHeaders($response['headers']);

        echo $response['body'];
    }

    /**
     * Run an application handler. This process involves:
     *
     *  - Parsing the HTTP request into an associative array
     *  - Passing this request array into the given handler
     *  - The application handler should then return an associative array
     *    containing the keys: `'status'`, `'headers'` and `'body'`
     *  - Adding default headers onto the returned response array's `'headers'`
     *    key if they aren't already set
     *  - Depending on whether the `returnResponse` setting is set to `TRUE`:
     *     - Returns response if `TRUE`
     *     - Sends response otherwise
     */
    public function run($handler)
    {
        $response = 
            $this->addDefaultHeadersToResponse(
                $handler(
                    $this->parseRequest()));

        if ($this->returnResponse()) {
            return $response;
        }
        
        $this->sendResponse($response);
    }
}
