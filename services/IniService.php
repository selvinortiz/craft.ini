<?php
namespace Craft;

class IniService extends BaseApplicationComponent
{
	/**
	 * Permanent storage for a combined array of environmentVariables and user defined variables
	 * @var array
	 */
	protected $vars = array();

	/**
	 * Fetches the environmentVariables, user defined variables, and merges them
	 * Once merged they get assigned to $this->vars and prepare() gets called for recursive parsing
	 */
	public function init()
	{
		$settings   = craft()->plugins->getPlugin('ini')->getSettings();
		$configFile = $settings->getAttribute('configFile');
		$configName = $settings->getAttribute('configName');

		// Config file is optional but adding it ensures forward compatibility
		$env    = craft()->config->get('environmentVariables', ConfigFile::General);
		$vars   = ($configFile == 'plugin') ? craft()->config->get($configName, 'ini') : craft()->config->get($configName, ConfigFile::General);

		// Make sure the merge does not trigger any errors or warnings
		$this->vars = array_merge(is_array($env) ? $env : array(), is_array($vars) ? $vars : array());

		// Explicitly passing the data here ensure recursion happens the same way from the start
		$this->prepare($this->vars);
	}

	/**
	 * @return array
	 */
	public function getVariables()
	{
		return $this->vars;
	}

	/**
	 * Fetches a prepared variable by key
	 *
	 * @param string $var
	 * @param null|mixed  $default
	 *
	 * @throws Exception
	 * @return mixed
	 */
	public function get($var, $default = null)
	{
		if (!is_string($var))
		{
			throw new Exception(Craft::t('The first argument passed to get() must be a string.'));
		}

		$var = trim($var);

		if (empty($var))
		{
			throw new Exception(Craft::t('The first argument passed to get() must be a non-empty string.'));
		}

		return $this->getValueWithDotSyntaxSupport($var, $this->vars, $default);
	}

	/**
	 * Prepares variable values by parsing strings with back/self references {element.key.key}
	 *
	 * @param array &$data The array received by reference so that it can be used for back references
	 */
	protected function prepare(array &$data=array())
	{
		// Make sure we can/should loop
		if (count($data))
		{
			foreach ($data as $key => $value)
			{
				// Make sure key is a valid string
				if (is_string($key) && strlen($key))
				{
					// Recur if value is an array
					if (is_array($value) && count($value))
					{
						$this->prepare($data[$key]);
					}

					$data[$key] = $this->parseValue($data[$key], $this->vars);
				}
			}
		}
	}

	/**
	 * Parses a string value '{siteUrl}images' into 'http://domain.com/images'
	 *
	 * @param mixed $value
	 * @param array $data
	 *
	 * @return mixed
	 */
	protected function parseValue($value, $data)
	{
		if (!is_string($value) || !strlen($value))
		{
			return $value;
		}

		try
		{
			return craft()->templates->renderObjectTemplate($value, $data);
		}
		catch (\Exception $e)
		{
			return $value;
		}
	}

	/**
	 * Finds an array value by key with support for dot syntax in multiple dimension arrays
	 *
	 * @param string $key
	 * @param array $data
	 * @param mixed  $default
	 *
	 * @return array|null
	 */
	protected function getValueWithDotSyntaxSupport($key, $data=array(), $default=null)
	{
		if (strpos($key, '.') !== false)
		{
			$keys = explode('.', $key);

			foreach ($keys as $v)
			{
				if (isset($data[$v]))
				{
					$data = $data[$v];
				}
				else
				{
					return $default;
				}
			}

			return $data;
		}

		return isset($data[$key]) ? $data[$key] : $default;
	}
}
