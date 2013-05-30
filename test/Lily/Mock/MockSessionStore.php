<?php

namespace Lily\Mock;

use Lily\Application\RoutedApplication;

class MockSessionStore
{
	public $session;

    public function set(array $session)
    {
    	$this->session = $session;
    }

    public function get()
    {
    	return $this->session;
    }
}
