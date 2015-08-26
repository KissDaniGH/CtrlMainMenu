<?php

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntry.php');
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
 * Application class for ctrlmmMenu Object.
 *
 * @author         Fabian Schmid <fs@studer-raimann.ch>
 *                 CtrlMainMenuEntryTableGUI
 *
 * $Id$
 */
class ctrlmmMenu {

	const TYPE_CTRL = 1;
	const TYPE_LINK = 2;
	const TYPE_DROPDOWN = 3;
	const TYPE_REFID = 4;
	const TYPE_ADMIN = 5;
	const TYPE_LASTVISITED = 6;
	const TYPE_DESKTOP = 7;
	const TYPE_REPOSITORY = 8;
	//	const TYPE_USER = 9;
	const PERM_NONE = 100;
	const PERM_ROLE = 101;
	const PERM_ROLE_EXEPTION = 104;
	const PERM_REF_READ = 102;
	const PERM_REF_WRITE = 103;
	const PERM_USERID = 105;
	/**
	 * @var array
	 */
	protected $entries;
	/**
	 * @var bool
	 */
	protected static $types_included = false;


	/**
	 * @param int $id
	 */
	public function __construct($id = 0) {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$this->db = $ilDB;
		self::includeAllTypes();
		$this->setEntries(ctrlmmEntry::getAllChildsForId($id));
	}


	/**
	 * @param mixed $entry
	 */
	public function addEntry($entry) {
	}


	/**
	 * @param array $entries
	 */
	public function setEntries($entries) {
		$this->entries = $entries;
	}


	/**
	 * @return array
	 */
	public function getEntries() {
		return $this->entries;
	}


	//
	// Static
	//
	/**
	 * @return string
	 */
	public static function getCssPrefix() {
		$pl = new ilCtrlMainMenuPlugin();

		return $pl->getConfigObject()->getCssPrefix();
	}


	/**
	 * @param bool $filter
	 *
	 * @return array
	 */
	public static function getAllTypesAsArray($filter = false) {
		$pl = new ilCtrlMainMenuPlugin();
		foreach (self::getAllTypeConstants() as $name => $value) {
			ctrlmmEntry::getNewInstanceForTypeId($value);
			$names[$value] = $pl->txt(strtolower($name));
		}
		if ($filter) {
			foreach ($names as $type_id => $name) {
				if (! ctrlmmEntry::isSecondInstanceAllowed($type_id)) {
					unset($names[$type_id]);
				}
			}
		}

		return $names;
	}


	/**
	 * @return array
	 */
	public static function getAllTypeConstants() {
		$fooClass = new ReflectionClass('ctrlmmMenu');
		$fooClass->getConstants();
		$return = array();
		foreach ($fooClass->getConstants() as $name => $value) {
			if (strpos($name, 'TYPE_') === 0) {
				$return[$name] = $value;
			}
		}

		return $return;
	}


	public static function includeAllTypes() {
		if (! self::$types_included) {
			foreach (self::getAllTypeConstants() as $name => $value) {
				$name = ctrlmmEntry::getClassAppendForValue($value);
				$type = './Customizing/global/plugins/Services/'
					. 'UIComponent/UserInterfaceHook/CtrlMainMenu/classes/EntryTypes/' . $name
					. '/class.ctrlmmEntry' . $name;
				require_once($type . '.php');
				require_once($type . 'GUI.php');
				if (is_file($type . 'FormGUI.php')) {
					require_once($type . 'FormGUI.php');
				}
			}
			self::$types_included = true;
		}
	}


	/**
	 * @return array
	 */
	public static function getAllPermissionsAsArray() {
		$pl = new ilCtrlMainMenuPlugin();
		$fooClass = new ReflectionClass('ctrlmmMenu');
		$names = array();
		foreach ($fooClass->getConstants() as $name => $value) {
			if (strpos($name, 'PERM_') === 0 AND strpos($name, 'PERM_REF_') === false) {
				$names[$value] = $pl->txt(strtolower($name));
			}
		}

		return $names;
	}


	/**
	 * @return bool
	 */
	public static function isOldILIAS() {
		require_once('./include/inc.ilias_version.php');
		require_once('./Services/Component/classes/class.ilComponent.php');

		return ! ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '4.2.999');
	}
}