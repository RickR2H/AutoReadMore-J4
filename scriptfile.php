<?php
// No direct access to this file
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Version;
use Joomla\CMS\Filesystem\File;

class plgContentAutoreadmoreInstallerScript  {
	
	private $extname                 = 'autoreadmore';	
	private $min_joomla_version      = '3.10.0';
	private $min_php_version         = '7.2';
	
	public function __construct()
	{
		$this->dir = __DIR__;
		$this->lang = Factory::getLanguage();
		$this->lang->load($this->extname);
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) {
		if ( ! $this->passMinimumJoomlaVersion())
		{
			$this->uninstallInstaller();
			return false;
		}

		if ( ! $this->passMinimumPHPVersion())
		{
			$this->uninstallInstaller();
			return false;
		}
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) {
		if (($type=='install') || ($type == 'update')) { // remove obsolete dir/files
			$this->postinstall_cleanup();
			$this->enable_plugin();
		}
		return true;
    }
	
	private function postinstall_cleanup() {
				$obsloteFolders = ['extensions', 'images','language'];
		// Remove plugins' files which load outside of the component. If any is not fully updated your site won't crash.
		foreach ($obsloteFolders as $folder)
		{
			$f = JPATH_SITE . '/plugins/content/'.$this->extname.'/' . $folder;

			if (!@file_exists($f) || !is_dir($f) || is_link($f))
			{
				continue;
			}

			Folder::delete($f);
		}
		$obsloteFiles = [sprintf("%s/plugins/content/%s/scriptary.php", JPATH_SITE, $this->extname)];
		foreach ($obsloteFiles as $file)
		{
			if (@is_file($file))
			{
				File::delete($file);
			}
		}
	}	
	private function enable_plugin() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' = ' . $db->quote(1))
			->where($db->quoteName('element') . ' = ' . $db->quote($this->extname));
		$db->setQuery($query);
		
		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			echo Text::_('Error while enabling '.$this->extname);
			
			return;
		}
		
	}

	// Check if Joomla version passes minimum requirement
	private function passMinimumJoomlaVersion()
	{
		$j = new Version();
		$version=$j->getShortVersion(); 
		if (version_compare($version, $this->min_joomla_version, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Incompatible Joomla version : found <strong>' . $version . '</strong>, Minimum : <strong>' . $this->min_joomla_version . '</strong>',
				'error'
			);

			return false;
		}

		return true;
	}

	// Check if PHP version passes minimum requirement
	private function passMinimumPHPVersion()
	{

		if (version_compare(PHP_VERSION, $this->min_php_version, '<'))
		{
			Factory::getApplication()->enqueueMessage(
					'Incompatible PHP version : found  <strong>' . PHP_VERSION . '</strong>, Minimum <strong>' . $this->min_php_version . '</strong>',
				'error'
			);
			return false;
		}

		return true;
	}

}
?>

