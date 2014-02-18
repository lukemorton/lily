<?php

namespace Lily\Example\Controller;

use Lily\Util\Response;

class MainController
{
    public function index()
    {
        return '<a href="/admin">admin</a>';
    }

    public function notFound()
    {
        return Response::notFound('We could not find the page you are looking for');
    }
}
