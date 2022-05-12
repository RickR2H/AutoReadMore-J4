<?php
/**
 * AutoReadMore plugin
 *
 * @from       https://github.com/gruz/AutoReadMore
 * @author     ConseilgGouz
 * @copyright (C) 2022 www.conseilgouz.com. All Rights Reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace ConseilGouz\Plugin\Content\Autoreadmore\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;

// Prevent direct access
defined('_JEXEC') || die;

class VersionField extends FormField
{
	/**
	 * Element name
	 *
	 * @var   string
	 */
	protected $_name = 'Version';

	function getInput()
	{
		$return = '';

		$xml = $this->def('xml');

		// Load language
		$jinput = Factory::getApplication()->input;
		$db = Factory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName(array('element','folder','type')))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('extension_id') . '=' . $db->Quote($jinput->get('extension_id', null)));
		$db->setQuery($query, 0, 1);
		$row = $db->loadAssoc();

		if ($row['type'] == 'plugin')
		{
			$this->plg_full_name = 'plg_' . $row['folder'] . '_' . $row['element'];

			// Is used for building joomfish links
			$this->langShortCode = null;

			$this->default_lang = ComponentHelper::getParams('com_languages')->get('admin');
			$language = Factory::getLanguage();
			$language->load($this->plg_full_name, JPATH_ROOT . dirname($xml), 'en-GB', true);
			$language->load($this->plg_full_name, JPATH_ROOT . dirname($xml), $this->default_lang, true);
		}

		$extension = $this->def('extension');

		$user = Factory::getUser();
		$authorise = $user->authorise('core.manage', 'com_installer');

		if (!StringHelper::strlen($extension) || !StringHelper::strlen($xml) || !$authorise)
		{
			return;
		}

		$version = '';

		if ($xml)
		{
			$xml = simplexml_load_file(JPATH_SITE . '/' . $xml);
			if ($xml && isset($xml->version))
			{
				$version = $xml->version;
			}
		}

		$document = Factory::getDocument();
		$css = '';
		$css .= ".version {display:block;text-align:right;color:brown;font-size:10px;}";
		$css .= ".readonly.plg-desc {font-weight:normal;}";
		$css .= "fieldset.radio label {width:auto;}";
		$document->addStyleDeclaration($css);

		$return .= '<span class="version">' . Text::_('JVERSION') . ' ' . $version . "</span>";

		return $return;
	}
	public function def($val, $default = '')
	{
	    return ( isset( $this->element[$val] ) && (string) $this->element[$val] != '' ) ? (string) $this->element[$val] : $default;
	}
	
}
