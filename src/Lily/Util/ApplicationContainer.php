<?php

namespace Lily\Util;

class ApplicationContainer
{
	private $application;
	private $middleware;

	public function __construct($application, array $middleware = array())
	{
		$this->application = $application;
		$this->middleware = $middleware;
	}

	private function application()
	{
		return $this->application;
	}

	private function middleware()
	{
		return $this->middleware;
	}

	public function handler()
	{
		return function ($request) {
			$handler = $this->application()->handler();

			foreach (array_reverse($this->middleware()) as $_mw) {
				$handler = $_mw->wrapHandler($handler);
			}

			return $handler($request);
		};
	}
}
