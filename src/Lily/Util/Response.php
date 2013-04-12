<?php

namespace Lily\Util;

class Response {

	public static function response(
		$status,
		array $headers = array(),
		$body = '')
	{
		return compact('status', 'headers', 'body');
	}
  
    public static function redirect($uri, $status = 301)
    {
    	return static::response($status, array('Location' => $uri));
    }

    public static function notFound()
    {
        return static::response(404, array(), 'Not found.');
    }

}
