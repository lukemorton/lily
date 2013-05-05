<?php

namespace Lily\Adapter;

/**
 * HTTP Adapter for most PHP applications.
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

            // `parse_url()` cannot parse malformed URLs like:
            // 
            //     http://localhost/http://example.com/index.php
            //
            // Only if truthy do we use what `parse_url()` it returns, otherwise
            // we default to the raw `REQUEST_URI`.
            $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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
     * Please note keys will always be lowercase-hyphenated.
     */
    private function headers()
    {
        $headers = array();

        if ( ! $this->forceSlowHeaders()) {
            if (function_exists('apache_request_headers')) {
                return
                    array_change_key_case(
                        apache_request_headers());
            } elseif (extension_loaded('http')) {
                return
                    array_change_key_case(
                        http_get_request_headers());
            }
        }

        return $this->slowHeaders();
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
     * Send headers and echo body of given `$response` array.
     */
    private function sendResponse(array $response)
    {
        $status = $response['status'];
        $statusText = static::$statusText[$status];
        header($statusText, TRUE, $status);

        foreach ($response['headers'] as $_header => $_value) {
            header("{$_header}: {$_value}");
        }

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
