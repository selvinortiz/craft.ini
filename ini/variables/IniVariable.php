<?php
namespace Craft;

class IniVariable extends \stdClass
{
	public function __construct()
	{
		$vars = craft()->ini->getVariables();

		foreach ($vars as $var => $value)
		{
			// The service layer is handling the sanity checks
			$this->{$var} = $value;
		}
	}
}
