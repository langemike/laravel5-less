<?php namespace Langemike\Laravel5Less;

use Illuminate\Support\Facades\Facade;

class LessFacade extends Facade {
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'less'; }
}