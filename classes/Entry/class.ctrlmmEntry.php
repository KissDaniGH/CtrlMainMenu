<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/class.ctrlmmData.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/class.ctrlmmTranslation.php');
require_once('./Services/Language/classes/class.ilLanguage.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Menu/class.ctrlmmMenu.php');
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
 * Application class for ctrlmmEntry Object.
 *
 * @author         Fabian Schmid <fs@studer-raimann.ch>
 *
 * $Id$
 */
class ctrlmmEntry {

	const TABLE_NAME = 'ui_uihk_ctrlmm_e';
	/**
	 * @var int
	 */
	public $id = 0;
	/**
	 * @var int
	 */
	protected $type = ctrlmmMenu::TYPE_LINK;
	/**
	 * @var string
	 */
	protected $link = '';
	/**
	 * @var string
	 */
	protected $title = '';
	/**
	 * @var string
	 */
	protected $permission = '';
	/**
	 * @var int
	 */
	protected $permission_type = ctrlmmMenu::PERM_NONE;
	/**
	 * @var int
	 */
	protected $position = 99;
	/**
	 * @var int
	 */
	protected $parent = 0;
	/**
	 * @var string
	 */
	protected $target = '_top';
	/**
	 * @var array
	 */
	protected $translations = array();
	/**
	 * @var array
	 */
	protected static $restricted_types = array();
	/**
	 * @var bool
	 */
	protected $restricted = false;
	/**
	 * @var bool
	 */
	protected $read = false;
	/**
	 * @var array
	 */
	protected static $cache = array();
	/**
	 * @var array
	 */
	protected static $childs_cache = array();
	/**
	 * @var bool
	 */
	protected $plugin = false;


	/**
	 * @param $id
	 */
	public function __construct($id = 0) {
		//if (! isset(self::$cache[$id])) {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$this->id = $id;
		$this->db = $ilDB;
		if ($this->restricted) {
			self::$restricted_types[] = $this->type;
		}
		if ($id != 0 AND ! $this->read) {
			$this->read();
		}
		self::$cache[$id] = $this;
		//	}
	}


	//
	// Static
	//
	/**
	 * @param $obj_name
	 * @param $field_name
	 *
	 * @return bool
	 */
	public static function isAdditionalField($obj_name, $field_name) {
		$properties = array();
		$reflection = new ReflectionClass($obj_name);
		foreach ($reflection->getProperties() as $property) {
			$properties[] = $property->getName();
		}

		return in_array($field_name, array_diff($properties, array(
			'db',
			'ctrl',
			'permission',
			'permission_type',
			'link',
			'id',
			'type',
			'title',
			'position',
			'parent',
			'entries',
			'translations',
			'plugin',
		)));
	}


	/**
	 * @param $childs
	 *
	 * @param $id
	 *
	 * @return array
	 */
	protected static function getPluginEntries($childs, $id) {
		foreach (ilPluginAdmin::$active_plugins as $slot) {
			foreach ($slot as $hook) {
				foreach ($hook as $pls) {
					foreach ($pls as $pl) {
						$plugin_class = 'il' . $pl . 'Plugin';
						if (method_exists($plugin_class, 'getMenuEntries')) {
							$menuEntries = $plugin_class::getMenuEntries($id);
							if(is_array($menuEntries)) {
								$childs = array_merge($childs, $menuEntries);
							}
						}
					}
				}
			}
		}

		return $childs;
	}


	/**
	 * @param $obj
	 *
	 * @return array
	 */
	public static function getAdditionalFieldsAsArray($obj) {
		$return = array();
		$reflection = new ReflectionClass($obj);
		foreach ($reflection->getProperties() as $property) {
			$k = $property->getName();
			if (self::isAdditionalField(get_class($obj), $k)) {
				$return[$k] = $obj->{$k};
			}
		}

		return $return;
	}


	protected function writeAdditionalData() {
		foreach (self::getAdditionalFieldsAsArray($this) as $k => $v) {
			$data = ctrlmmData::_getInstanceForDataKey($this->getId(), $k);
			$data->setDataValue($v);
			$data->update();
		}
	}


	protected function writeTranslations() {
		foreach ($this->getTranslations() as $k => $v) {
			$trans = ctrlmmTranslation::_getInstanceForLanguageKey($this->getId(), $k);
			$trans->setTitle($v);
			$trans->update();
		}
	}


	/**
	 * @return bool
	 */
	public function read() {
		$result = $this->db->query('SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = '
			. $this->db->quote($this->getId(), 'integer'));
		while ($record = $this->db->fetchObject($result)) {
			$this->setLink($record->link);
			$this->setType($record->type);
			$this->setPermission($record->permission);
			$this->setPermissionType($record->permission_type);
			$this->setPosition($record->position);
			$this->setParent($record->parent);
		}
		foreach (ctrlmmData::getDataForEntry($this->getId()) as $k => $v) {
			if (self::isAdditionalField(get_class($this), $k)) {
				$this->{$k} = $v;
			}
		}
		$this->setTitle(ctrlmmTranslation::_getTitleForEntryId($this->getId()));
		$this->setTranslations(ctrlmmTranslation::_getAllTranslationsAsArray($this->getId()));

		return $this->read = true;
	}


	public function replacePlaceholders() {
		global $ilUser;
		$replacements = array(
			'[firstname]' => "<span class='headerFirstname'>" . $ilUser->getFirstname() . "</span>",
			'[lastname]' => "<span class='headerLastname'>" . $ilUser->getLastname() . "</span>",
			'[login]' => "<span class='headerLogin' > " . $ilUser->getLogin() . "</span>",
			'[email]' => "<span class='headerEmail'>" . $ilUser->getEmail() . "</span>",
			'[picture]' => "<img class='headerImage' src='" . $ilUser->getPersonalPicturePath('xxsmall') . "' />",
		);

		$this->setTitle(strtr($this->getTitle(), $replacements));
	}


	public function migrate() {
		$result = $this->db->query('SELECT data, title FROM ' . self::TABLE_NAME . ' WHERE id = '
			. $this->db->quote($this->getId(), 'integer'));
		while ($record = $this->db->fetchObject($result)) {
			if (json_decode($record->data)) {
				foreach (json_decode($record->data) as $k => $v) {
					if (self::isAdditionalField(get_class($this), $k)) {
						$data = ctrlmmData::_getInstanceForDataKey($this->getId(), $k);
						if (is_array($v)) {
							$data->setDataValue(implode(',', $v));
						} else {
							$data->setDataValue($v);
						}
						$data->create();
					}
				}
			}
			foreach (self::getAllLanguageIds() as $lng) {
				$trans = ctrlmmTranslation::_getInstanceForLanguageKey($this->getId(), $lng);
				$trans->setTitle($record->title);
				$trans->create();
			}
		}
	}


	public function create() {
		if ($this->getParent() > 0 AND in_array($this->getType(), array(
				ctrlmmMenu::TYPE_ADMIN,
				ctrlmmMenu::TYPE_DROPDOWN,
			))
		) {
			ilUtil::sendFailure('Wrong Child-Type');
		} else {
			if ($this->getId() != 0) {
				$this->update();
			} else {
				$this->setId($this->db->nextID(self::TABLE_NAME));
				$this->db->insert(self::TABLE_NAME, array(
					'id' => array( 'integer', $this->getId() ),
					'link' => array( 'text', $this->getLink() ),
					'type' => array( 'integer', $this->getType() ),
					'permission' => array( 'text', $this->getPermission() ),
					'permission_type' => array( 'integer', $this->getPermissionType() ),
					'position' => array( 'integer', $this->getPosition() ),
					'parent' => array( 'integer', $this->getParent() ),
				));
				$this->writeAdditionalData();
				$this->writeTranslations();
				$this->read();
			}
		}
	}


	public function update() {
		$this->db->update(self::TABLE_NAME, array(
			'link' => array( 'text', $this->getLink() ),
			'type' => array( 'integer', $this->getType() ),
			'permission' => array( 'text', $this->getPermission() ),
			'permission_type' => array( 'integer', $this->getPermissionType() ),
			'position' => array( 'integer', $this->getPosition() ),
			'parent' => array( 'integer', $this->getParent() ),
		), array(
			'id' => array( 'integer', $this->getId() )
		));
		$this->writeAdditionalData();
		$this->writeTranslations();
		$this->read();
	}


	/**
	 * @return int
	 */
	public function delete() {
		ctrlmmTranslation::_deleteAllInstancesForEntryId($this->getId());
		ctrlmmData::_deleteAllInstancesForParentId($this->getId());

		$this->db->manipulate('DELETE FROM ' . self::TABLE_NAME . ' WHERE id = '
			. $this->db->quote($this->getId(), 'integer'));

		if ($this->getType() == ctrlmmMenu::TYPE_DROPDOWN) {
			/**
			 * @var $entry ctrlmmEntry
			 */
			foreach (ctrlmmEntry::getAllChildsForId($this->getId()) as $entry) {
				$entry->delete();
			}
		}
	}


	//
	// Static
	//
	/**
	 * @param $type_id
	 *
	 * @return bool
	 */
	public static function entriesExistForType($type_id) {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$set = $ilDB->query('SELECT type FROM ' . self::TABLE_NAME . ' ' . ' WHERE type = '
			. $ilDB->quote($type_id, 'integer'));

		return ($ilDB->numRows($set) > 0 ? true : false);
	}


	/**
	 * @param $type_id
	 *
	 * @return bool
	 */
	public static function isSecondInstanceAllowed($type_id) {
		if (! in_array($type_id, self::$restricted_types)) {
			return true;
		} else {
			if ($type_id == ctrlmmMenu::TYPE_CTRL) {
				return false;
			}

			return self::entriesExistForType($type_id) ? false : true;
		}
	}


	/**
	 * @param int $id
	 *
	 * @return ctrlmmEntry[]
	 */
	public static function getAllChildsForId($id) {
		$as_array = false;
		if (! isset(self::$childs_cache[$id])) {
			global $ilDB;
			/**
			 * @var $ilDB ilDB
			 */
			$childs = array();
			$set = $ilDB->query('SELECT id,type,position FROM ' . self::TABLE_NAME . ' ' . ' WHERE parent = '
				. $ilDB->quote($id, 'integer') . ' ORDER by position ASC');
			while ($rec = $ilDB->fetchObject($set)) {
				$type = 'ctrlmmEntry' . self::getClassAppendForValue($rec->type);
				if ($as_array) {
					$childs[] = (array)new $type($rec->id);
				} else {
					$childs[] = new $type($rec->id);
				}
			}
			if (count($childs) == 0 AND $id == 0) {
				$admin = 'ctrlmmEntry' . self::getClassAppendForValue(ctrlmmMenu::TYPE_ADMIN);
				$obj = new $admin(0);
				$obj->create();
				if ($as_array) {
					$childs[] = (array)$obj;
				} else {
					$childs[] = new $obj;
				}
			}

			$childs = self::getPluginEntries($childs, $id);

			self::$childs_cache[$id] = $childs;
		}

		return self::$childs_cache[$id];
	}


	/**
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getAllChildsForIdAsArray($id = 0) {
		$return = array();
		foreach (self::getAllChildsForId($id) as $child) {
			$return[] = (array)$child;
		}

		return $return;
	}


	/**
	 * @param $id
	 *
	 * @return ctrlmmEntry
	 */
	public static function find($id) {
		if (! isset(self::$cache[$id])) {
			global $ilDB;
			ctrlmmMenu::includeAllTypes();
			/**
			 * @var $ilDB ilDB
			 */
			$set = $ilDB->query('SELECT id,type,position FROM ' . self::TABLE_NAME . ' ' . ' WHERE id = '
				. $ilDB->quote($id, 'integer'));
			$type = 'ctrlmmEntry' . self::getClassAppendForValue(ctrlmmMenu::TYPE_ADMIN);
			self::$cache[$id] = new $type();
			while ($rec = $ilDB->fetchObject($set)) {
				$type = 'ctrlmmEntry' . self::getClassAppendForValue($rec->type);

				self::$cache[$id] = new $type($rec->id);
			}
		}

		return self::$cache[$id];
	}


	/**
	 * @param $type_id
	 *
	 * @return ctrlmmEntry
	 */
	public static function getNewInstanceForTypeId($type_id) {
		ctrlmmMenu::includeAllTypes();
		$type = 'ctrlmmEntry' . self::getClassAppendForValue($type_id);
		$object = new $type(0);

		return $object;
	}


	/**
	 * @param bool $as_array
	 *
	 * @return ctrlmmEntry[]
	 */
	public static function getAll($as_array = false) {
		global $ilDB;
		ctrlmmMenu::includeAllTypes();
		/**
		 * @var $ilDB ilDB
		 */
		$childs = array();
		$set = $ilDB->query('SELECT id, type FROM ' . self::TABLE_NAME);
		while ($rec = $ilDB->fetchObject($set)) {
			$type = 'ctrlmmEntry' . self::getClassAppendForValue($rec->type);
			if ($as_array) {
				$childs[] = (array)new $type($rec->id);
			} else {
				$childs[] = new $type($rec->id);
			}
		}

		return $childs;
	}


	/**
	 * Return all entries for a given command class
	 *
	 * @param $cmdClass
	 *
	 * @return array ctrlmmEntry[]
	 */
	public static function getEntriesByCmdClass($cmdClass) {
		global $ilDB;
		$sql =
			'SELECT * FROM ' . ctrlmmData::TABLE_NAME . ' WHERE data_value LIKE ' . $ilDB->quote("%$cmdClass", 'text');
		$set = $ilDB->query($sql);
		$entries = array();
		while ($rec = $ilDB->fetchAssoc($set)) {
			$entries[] = new ctrlmmEntry($rec['parent_id']);
		}

		return $entries;
	}


	/**
	 * @return bool
	 */
	public function checkPermission() {
		global $ilAccess, $ilUser, $rbacreview;
		switch ($this->getPermissionType()) {
			case ctrlmmMenu::PERM_ROLE:
				foreach ((array)json_decode($this->getPermission()) as $pid) {
					if (in_array($pid, $rbacreview->assignedRoles($ilUser->getId()))) {
						return true;
					}
				}
				break;
			case ctrlmmMenu::PERM_REF_READ:
				if ($ilAccess->checkAccess('read', '', $this->getPermission())) {
					return true;
				}
				break;
			case ctrlmmMenu::PERM_REF_WRITE:
				if ($ilAccess->checkAccess('write', '', $this->getPermission())) {
					return true;
				}
				break;
			case ctrlmmMenu::PERM_USERID:
				return in_array($ilUser->getId(), json_decode($this->getPermission()));
				break;
			case ctrlmmMenu::PERM_NONE:
			case NULL;
				return true;
			default:
				return false;
		}

		return false;
	}


	/**
	 * @return bool
	 */
	public function isActive() {
		return true;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param string $link
	 */
	public function setLink($link) {
		$this->link = $link;
	}


	/**
	 * @return string
	 */
	public function getLink() {
		return $this->link;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param int $type
	 */
	public function setType($type) {
		$this->type = $type;
	}


	/**
	 * @return int
	 */
	public function getType() {
		return $this->type;
	}


	/**
	 * @param string $permission (comma separated id's)
	 */
	public function setPermission($permission) {
		$this->permission = $permission;
	}


	/**
	 * @return string $permission (comma separated id's)
	 */
	public function getPermission() {
		return $this->permission;
	}


	/**
	 * @param int $permission_type
	 */
	public function setPermissionType($permission_type) {
		$this->permission_type = $permission_type;
	}


	/**
	 * @return int
	 */
	public function getPermissionType() {
		return $this->permission_type;
	}


	/**
	 * @param int $position
	 */
	public function setPosition($position) {
		$this->position = $position;
	}


	/**
	 * @return int
	 */
	public function getPosition() {
		return $this->position;
	}


	/**
	 * @param int $parent
	 */
	public function setParent($parent) {
		$this->parent = $parent;
	}


	/**
	 * @return int
	 */
	public function getParent() {
		return $this->parent;
	}


	/**
	 * @param string $target
	 */
	public function setTarget($target) {
		$this->target = $target;
	}


	/**
	 * @return string
	 */
	public function getTarget() {
		return $this->target;
	}


	/**
	 * @param array $translations
	 */
	public function setTranslations($translations) {
		$this->translations = $translations;
	}


	/**
	 * @return array
	 */
	public function getTranslations() {
		return $this->translations;
	}


	/**
	 * @param boolean $plugin
	 */
	public function setPlugin($plugin) {
		$this->plugin = $plugin;
	}


	/**
	 * @return boolean
	 */
	public function getPlugin() {
		return $this->plugin;
	}



	//
	// Helper
	//

	/**
	 * @param $id
	 *
	 * @return string
	 */
	public static function getClassConstantForId($id) {
		$constants = array_flip(ctrlmmMenu::getAllTypeConstants());

		return $constants[$id];
	}


	/**
	 * @param $id
	 *
	 * @return string
	 */
	public static function getClassAppendForValue($id) {
		return ucfirst(strtolower(str_ireplace('TYPE_', '', self::getClassConstantForId($id))));
	}


	/**
	 * @param $id
	 *
	 * @return string
	 */
	public static function getPermissionTypeForValue($id) {
		return strtolower(str_ireplace('PERM_', '', self::getClassConstantForId($id)));
	}


	public static function includeAllEntryTypes() {
		ctrlmmMenu::includeAllTypes();
	}


	/**
	 * @return array
	 */
	public static function getAllLanguageIds() {
		$lngs = new ilLanguage('en');

		return $lngs->getInstalledLanguages();
	}


	/**
	 * @param $lng
	 *
	 * @return bool
	 */
	public static function isDefaultLanguage($lng) {
		$lngs = new ilLanguage('en');

		return $lngs->getDefaultLanguage() == $lng ? true : false;
	}
}