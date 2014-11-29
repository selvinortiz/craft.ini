<?php
namespace Craft;

class IniPlugin extends BasePlugin
{
	/**
	 * Returns the plugin name or the plugin alias assigned by the end user
	 *
	 * @param bool $real Whether the real name should be returned
	 *
	 * @return string
	 */
	public function getName($real=false)
	{
		$alias	= $this->getSettings()->getAttribute('pluginAlias');

		return ($real || empty($alias)) ? 'Ini' : $alias;
	}

	public function getVersion()
	{
		return '1.1.0';
	}

	public function getDeveloper()
	{
		return 'Selvin Ortiz';
	}

	public function getDeveloperUrl()
	{
		return 'http://selv.in';
	}

	public function hasCpSection()
	{
		return $this->getSettings()->getAttribute('enableCpTab');
	}

	public function defineSettings()
	{
		return array(
			'configName'    => array(AttributeType::String, 'default' => 'globals'),
			'configFile'    => array(AttributeType::String, 'default' => 'general'),
			'enableCpTab'   => array(AttributeType::String, 'default' => false),
			'pluginAlias'   => array(AttributeType::String, 'default' => 'Initialize'),
		);
	}

	/**
	 * Returns a rendered view for plugin settings
	 *
	 * @return string The html content
	 */
	public function getSettingsHtml()
	{
		return craft()->templates->render('ini/settings',
			array(
				'name'          => $this->getName(true),
				'alias'         => $this->getName(),
				'version'       => $this->getVersion(),
				'settings'      => $this->getSettings(),
			)
		);
	}
}
