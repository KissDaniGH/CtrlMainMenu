<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * Class ctrlmmEntryFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ctrlmmEntryFormGUI extends ilPropertyFormGUI {

	/**
	 * @var ilHubConfigGUI
	 */
	protected $parent_gui;
	/**
	 * @var  ilCtrl
	 */
	protected $ctrl;


	/**
	 * @param $parent_gui
	 */
	public function __construct($parent_gui, ctrlmmEntry $entry) {
		global $ilCtrl;
		$this->parent_gui = $parent_gui;
		$this->ctrl = $ilCtrl;
		$this->entry = $entry;
		$this->pl = new ilCtrlMainMenuPlugin();
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
		$this->initForm();
		$this->initPermissionSelectionForm();
	}

	/**
	 * @param int  $filter
	 * @param bool $with_text
	 *
	 * @return array
	 */
	public static function getRoles($filter, $with_text = true) {
		global $rbacreview;
		$opt = array();
		$role_ids = array();
		foreach ($rbacreview->getRolesByFilter($filter) as $role) {
			$opt[$role['obj_id']] = $role['title'] . ' (' . $role['obj_id'] . ')';
			$role_ids[] = $role['obj_id'];
		}
		if ($with_text) {
			return $opt;
		} else {
			return $role_ids;
		}
	}

	private function initPermissionSelectionForm() {
		$global_roles = self::getRoles(ilRbacReview::FILTER_ALL_GLOBAL);
		$locale_roles = self::getRoles(ilRbacReview::FILTER_ALL_LOCAL);
		$ro = new ilRadioGroupInputGUI($this->pl->txt('permission_type'), 'permission_type');
		$ro->setRequired(true);
		foreach (ctrlmmMenu::getAllPermissionsAsArray() as $k => $v) {
			$option = new ilRadioOption($v, $k);
			switch ($k) {
				case ctrlmmMenu::PERM_NONE :
					break;
				case ctrlmmMenu::PERM_ROLE :
				case ctrlmmMenu::PERM_ROLE_EXEPTION :
					$se = new ilMultiSelectInputGUI($this->pl->txt('perm_input'), 'permission_' . $k);
					$se->setWidth(400);
					$se->setOptions($global_roles);
					$option->addSubItem($se);
					// Variante mit MultiSelection
					$se = new ilMultiSelectInputGUI($this->pl->txt('perm_input_locale'), 'permission_locale_' . $k);
					$se->setWidth(400);
					$se->setOptions($locale_roles);
					// $option->addSubItem($se);
					// Variante mit TextInputGUI
					$te = new ilTextInputGUI($this->pl->txt('perm_input_locale'), 'permission_locale_' . $k);
					$te->setInfo($this->pl->txt('perm_input_locale_info'));
					$option->addSubItem($te);
					break;
				case ctrlmmMenu::PERM_REF_WRITE :
				case ctrlmmMenu::PERM_REF_READ :
					$te = new ilTextInputGUI($this->pl->txt('perm_input'), 'permission_' . $k);
					$option->addSubItem($te);
					break;
				case ctrlmmMenu::PERM_USERID :
					$te = new ilTextInputGUI($this->pl->txt('perm_input_user'), 'permission_user_' . $k);
					$te->setInfo($this->pl->txt('perm_input_user_info'));
					$option->addSubItem($te);
					break;
			}
			$ro->addOption($option);
		}
		$this->addItem($ro);
	}


	private function initForm() {
		global $lng;
		/**
		 * @var $lng ilLanguage
		 */
		$lng->loadLanguageModule('meta');
		$this->form = new ilPropertyFormGUI();

		$te = new ilFormSectionHeaderGUI();
		$te->setTitle($this->pl->txt('title'));
		$this->addItem($te);
		$this->setTitle($this->pl->txt('form_title'));
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
		foreach (ctrlmmEntry::getAllLanguageIds() as $language) {
			$te = new ilTextInputGUI($lng->txt('meta_l_' . $language), 'title_' . $language);
			$te->setRequired(ctrlmmEntry::isDefaultLanguage($language));
			$this->addItem($te);
		}
		$type = new ilHiddenInputGUI('type');
		$this->addItem($type);
		$link = new ilHiddenInputGUI('link');
		$this->addItem($link);
		if (count(ctrlmmEntry::getAdditionalFieldsAsArray($this->entry)) > 0) {
			$te = new ilFormSectionHeaderGUI();
			$te->setTitle($this->pl->txt('settings'));
			$this->addItem($te);
		}
		$this->addCommandButton($mode . 'Object', $this->pl->txt('create'));
		if ($mode != 'create') {
			$this->addCommandButton($mode . 'ObjectAndStay', $this->pl->txt('create_and_stay'));
		}
		$this->addCommandButton('configure', $this->pl->txt('cancel'));
	}

	abstract public function addFields();


	public function fillForm() {
		$array = array();

		$this->setValuesByArray($array);
	}


	/**
	 * returns whether checkinput was successful or not.
	 *
	 * @return bool
	 */
	public function fillObject() {
		if (! $this->checkInput()) {
			return false;
		}

		return true;
	}


	/**
	 * @return bool
	 */
	public function saveObject() {
		if (! $this->fillObject()) {
			return false;
		}

		return true;
	}


	protected function addCommandButtons() {
		$this->addCommandButton('save', $this->pl->txt('admin_form_button_save'));
		$this->addCommandButton('cancel', $this->pl->txt('admin_form_button_cancel'));
	}
}

?>
