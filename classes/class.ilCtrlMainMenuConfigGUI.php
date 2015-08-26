<?php
require_once('class.ilCtrlMainMenuPlugin.php');
require_once('class.ilCtrlMainMenuConfig.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntryGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntryTableGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntry.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Menu/class.ctrlmmMenu.php');
require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');


/**
 * CtrlMainMenu Configuration
 *
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilCtrlMainMenuConfigGUI extends ilPluginConfigGUI {

	/**
	 * @var ctrlmmMenuConfig
	 */
	protected $object;
	/**q
	 *
	 * @var array
	 */
	protected $fields = array();
	/**
	 * @var string
	 */
	protected $table_name = '';


	public function __construct() {
		global $ilCtrl, $tpl, $ilTabs;
		/**
		 * @var $ilCtrl ilCtrl
		 * @var $tpl    ilTemplate
		 * @var $ilTabs ilTabsGUI
		 */
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->tabs = & $ilTabs;
		$this->pl = new ilCtrlMainMenuPlugin();
		if($_GET['rl']) {
			$this->pl->updateLanguages();
		}
		if (! ctrlmmMenu::isOldILIAS()) {
			$this->tpl->addJavaScript('https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js', true, 1);
			$this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/js/sortable.js');
		}
		ctrlmmMenu::includeAllTypes();
		$this->object = new ilCtrlMainMenuConfig($this->pl->getConfigTableName());
	}


	/**
	 * @return array
	 */
	public function getFields() {
		$this->fields = array(
			'css_prefix' => array(
				'type' => 'ilTextInputGUI',
			),
			'css_active' => array(
				'type' => 'ilTextInputGUI',
			),
			'css_inactive' => array(
				'type' => 'ilTextInputGUI',
			),
			'doubleclick_prevention' => array(
				'type' => 'ilCheckboxInputGUI',
			),
			'simple_form_validation' => array(
				'type' => 'ilCheckboxInputGUI',
			),
		);

		return $this->fields;
	}


	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->table_name;
	}


	/**
	 * @return ilCtrlMainMenuConfig
	 */
	public function getObject() {
		return $this->object;
	}


	/**
	 * Handles all commmands, default is 'configure'
	 */
	function performCommand($cmd) {
		$this->ctrl->setParameter($this, 'parent_id', $_GET['parent_id'] ? $_GET['parent_id'] : 0);
		if ($_GET['parent_id'] > 0) {
			$this->tabs->addTab('mm_admin', $this->pl->txt('back_to_main'), $this->ctrl->getLinkTarget($this, 'resetParent'));
			$this->tabs->addTab('child_admin', $this->pl->txt('tabs_title_childs'), $this->ctrl->getLinkTarget($this, 'configure'));
			$this->tabs->activateTab('child_admin');
		} else {
			$this->tabs->addTab('mm_admin', $this->pl->txt('tab_main'), $this->ctrl->getLinkTarget($this, 'configure'));
			$this->tabs->activateTab('mm_admin');
		}
		$this->tabs->addTab('css', $this->pl->txt('css_settings'), $this->ctrl->getLinkTarget($this, 'cssSettings'));
		switch ($cmd) {
			case 'configure':
			case 'save':
			case 'saveSorting':
			case 'addEntry':
			case 'createEntry':
			case 'selectEntryType':
				$this->$cmd();
				break;
			default:
				$this->$cmd();
				break;
		}
	}


	public function cssSettings() {
		$this->tabs->setTabActive('css');
		$this->initConfigurationForm();
		$this->getValues();
		$this->tpl->setContent($this->form->getHTML());
	}


	public function editChilds() {
		$this->ctrl->setParameter($this, 'parent_id', $_GET['entry_id']);
		$this->ctrl->redirect($this, 'configure');
	}


	public function configure() {
		$table = new ctrlmmEntryTableGUI($this, 'configure', $_GET['parent_id'] ? $_GET['parent_id'] : 0);
		$this->tpl->setContent($table->getHTML());
	}


	public function resetParent() {
		$this->ctrl->setParameter($this, 'parent_id', 0);
		$this->ctrl->redirect($this, 'configure');
	}


	public function saveSorting() {
		foreach ($_POST['position'] as $k => $v) {
			$obj = ctrlmmEntry::find($v);
			$obj->setPosition($k);
			$obj->update();
		}
		ilUtil::sendSuccess($this->pl->txt('sorting_saved'));
		$this->ctrl->redirect($this);
	}


	public function saveSortingOld() {
		foreach ($_POST['id'] as $k => $v) {
			$obj = ctrlmmEntry::find($k);
			$obj->setPosition($v);
			$obj->update();
		}
		ilUtil::sendSuccess($this->pl->txt('sorting_saved'));
		$this->ctrl->redirect($this);
	}


	public function selectEntryType() {
		$select = new ilPropertyFormGUI();
		$select->setFormAction($this->ctrl->getFormAction($this));
		$select->setTitle($this->pl->txt('select_type'));
		$se = new ilSelectInputGUI($this->pl->txt('type'), 'type');
		$se->setOptions(ctrlmmMenu::getAllTypesAsArray(true));
		$select->addItem($se);
		$select->addCommandButton('addEntry', $this->pl->txt('select'));
		$select->addCommandButton('configure', $this->pl->txt('cancel'));
		$this->tpl->setContent($select->getHTML());
	}


	public function addEntry() {
		/**
		 * @var $entry_gui ctrlmmEntryCtrlGUI
		 */

		$gui_class = 'ctrlmmEntry' . ctrlmmEntry::getClassAppendForValue($_POST['type']) . 'GUI';
		$entry_gui = new $gui_class(ctrlmmEntry::getNewInstanceForTypeId($_POST['type']), $this);
		$entry_gui->initForm();
		$entry_gui->setFormValuesByArray();
		$this->tpl->setContent($entry_gui->form->getHTML());
	}


	public function createObjectAndStay() {
		$this->createObject(false);
		$this->editEntry();
	}


	public function createObject($redirect = true) {
		/**
		 * @var $entry_gui ctrlmmEntryCtrlGUI
		 */

		$gui_class = 'ctrlmmEntry' . ctrlmmEntry::getClassAppendForValue($_POST['type']) . 'GUI';
		$entry_gui = new $gui_class(ctrlmmEntry::getNewInstanceForTypeId($_POST['type']), $this);
		$entry_gui->initForm();
		if ($entry_gui->form->checkInput()) {
			$entry_gui->createEntry();
			ilUtil::sendSuccess($this->pl->txt('entry_added'), $redirect);
			if ($redirect) {
				$this->ctrl->redirect($this);
			}
		}
		$entry_gui->form->setValuesByPost();
		$this->tpl->setContent($entry_gui->form->getHTML());
	}


	public function editEntry() {
		/**
		 * @var $entry_gui     ctrlmmEntryCtrlGUI
		 * @var $entry_formgui ctrlmmEntryCtrlFormGUI
		 */
		$this->ctrl->saveParameter($this, 'entry_id');
		$entry = ctrlmmEntry::find($_GET['entry_id']);
		$gui_class = 'ctrlmmEntry' . ctrlmmEntry::getClassAppendForValue($entry->getType()) . 'GUI';
//		$formgui_class = 'ctrlmmEntry' . ctrlmmEntry::getClassAppendForValue($entry->getType()) . 'FormGUI';

		$entry_gui = new $gui_class($entry, $this);
		$entry_gui->initForm('update');
		$entry_gui->setFormValuesByArray();

//		$entry_formgui = new $formgui_class($this, $entry);
//		$entry_formgui->fillForm();

		$this->tpl->setContent($entry_gui->form->getHTML());
//		$this->tpl->setContent($entry_formgui->getHTML());
	}


	public function updateObjectAndStay() {
		$this->updateObject(false);
		$this->editEntry();
	}


	/**
	 * @param bool $redirect
	 */
	public function updateObject($redirect = true) {
		/**
		 * @var $entry_gui ctrlmmEntryCtrlGUI
		 */

		$entry = ctrlmmEntry::find($_GET['entry_id']);
		$gui_class = 'ctrlmmEntry' . ctrlmmEntry::getClassAppendForValue($entry->getType()) . 'GUI';
		$entry_gui = new $gui_class($entry, $this);
		$entry_gui->initForm('update');
		if ($entry_gui->form->checkInput()) {
			$entry_gui->createEntry();
			ilUtil::sendSuccess($this->pl->txt('entry_updated'), $redirect);
			if ($redirect) {
				$this->ctrl->redirect($this);
			}
		}
		$entry_gui->form->setValuesByPost();
		$this->tpl->setContent($entry_gui->form->getHTML());
	}


	public function deleteEntry() {
		$entry = ctrlmmEntry::find($_GET['entry_id']);
		$conf = new ilConfirmationGUI();
		ilUtil::sendQuestion($this->pl->txt('qst_delete_entry'));
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setConfirm($this->pl->txt('delete'), 'deleteObject');
		$conf->setCancel($this->pl->txt('cancel'), 'configure');
		$conf->addItem('entry_id', $_GET['entry_id'], $entry->getTitle());
		$this->tpl->setContent($conf->getHTML());
	}


	public function deleteObject() {
		/**
		 * @var $entry ctrlmmEntry
		 */
		$entry = ctrlmmEntry::find($_POST['entry_id']);
		$entry->delete();
		ilUtil::sendSuccess($this->pl->txt('entry_deleted'));
		$this->ctrl->redirect($this, 'configure');
	}


	//
	// Default Configuration
	//
	public function getValues() {
		foreach ($this->getFields() as $key => $item) {
			$values[$key] = $this->object->getValue($key);
			if (is_array($item['subelements'])) {
				foreach ($item['subelements'] as $subkey => $subitem) {
					$values[$key . '_' . $subkey] = $this->object->getValue($key . '_' . $subkey);
				}
			}
		}
		$this->form->setValuesByArray($values);
	}


	/**
	 * @return ilPropertyFormGUI
	 */
	public function initConfigurationForm() {
		global $lng, $ilCtrl;
		$this->form = new ilPropertyFormGUI();
		foreach ($this->getFields() as $key => $item) {
			$field = new $item['type']($this->pl->txt($key), $key);
			if ($item['info']) {
				$field->setInfo($this->pl->txt($key . '_info'));
			}
			if (is_array($item['subelements'])) {
				foreach ($item['subelements'] as $subkey => $subitem) {
					$subfield = new $subitem['type']($this->pl->txt($key . '_' . $subkey), $key . '_' . $subkey);
					if ($subitem['info']) {
						$subfield->setInfo($this->pl->txt($key . '_info'));
					}
					$field->addSubItem($subfield);
				}
			}
			$this->form->addItem($field);
		}
		$this->form->addCommandButton('save', $lng->txt('save'));
		$this->form->setTitle($this->pl->txt('configuration'));
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		return $this->form;
	}


	public function save() {
		global $tpl, $ilCtrl;
		$this->initConfigurationForm();
		if ($this->form->checkInput()) {
			foreach ($this->getFields() as $key => $item) {
				$this->object->setValue($key, $this->form->getInput($key));
				if (is_array($item['subelements'])) {
					foreach ($item['subelements'] as $subkey => $subitem) {
						$this->object->setValue($key . '_' . $subkey, $this->form->getInput($key . '_' . $subkey));
					}
				}
			}
			ilUtil::sendSuccess($this->pl->txt('conf_saved'), true);
			$ilCtrl->redirect($this, 'cssSettings');
		} else {
			$this->form->setValuesByPost();
			$tpl->setContent($this->form->getHtml());
		}
	}
}

?>
