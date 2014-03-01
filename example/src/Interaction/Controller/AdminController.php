<?php

namespace Lily\Example\Interaction\Controller;

use Lily\Util\Response;

class AdminController
{
    public function index()
    {
        return '<a href="/admin/logout">logout</a>';
    }

    public function login()
    {
        return '<form method="post"><button>Login</button></form>';
    }

    public function login_process()
    {
        $cookies = array('authed' => TRUE);
        return Response::redirect('/admin') + compact('cookies');
    }

    public function logout()
    {
        $cookies = array('authed' => NULL);
        return Response::redirect('/admin/login') + compact('cookies');
    }
}
