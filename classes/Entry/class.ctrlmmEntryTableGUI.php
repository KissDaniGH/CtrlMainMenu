<?php
require_once('./Services/Table/classes/class.ilTable2GUI.php');
require_once('class.ctrlmmEntry.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');

/**
 * TableGUI ctrlmmEntryTableGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id:
 *
 */
class ctrlmmEntryTableGUI extends ilTable2GUI {

	/**
	 * @param ilCtrlMainMenuConfigGUI $a_parent_obj
	 * @param string                  $a_parent_cmd
	 * @param int                     $parent_id
	 */
	function __construct(ilCtrlMainMenuConfigGUI $a_parent_obj, $a_parent_cmd, $parent_id = 0) {
		global $ilCtrl, $ilTabs, $ilToolbar;
		/**
		 * @var $tpl       ilTemplate
		 * @var $ilCtrl    ilCtrl
		 * @var $ilTabs    ilTabsGUI
		 * @var $ilToolbar ilToolbarGUI
		 */
		$this->pl = new ilCtrlMainMenuPlugin();
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->setId('mm_entry_list');
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setRowTemplate('tpl.entry_list.html', $this->pl->getDirectory());
		$this->setTitle($this->pl->txt('list_title'));
		//
		// Columns
		$this->addColumn('', 'position', '20px');
		$this->addColumn($this->pl->txt('title'), 'title', 'auto');
		$this->addColumn($this->pl->txt('type'), 'type', 'auto');
		$this->addColumn($this->pl->txt('actions'), 'actions', 'auto');
		// ...
		// Header
		$ilToolbar->addButton($this->pl->txt('add_new'), $this->ctrl->getLinkTarget($a_parent_obj, 'selectEntryType'));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		if (ctrlmmMenu::isOldILIAS()) {
			$this->addCommandButton('saveSortingOld', $this->pl->txt('save_sorting'));
		} else {
			$this->addCommandButton('saveSorting', $this->pl->txt('save_sorting'));
		}
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		ctrlmmMenu::includeAllTypes();
		$this->setData(ctrlmmEntry::getAllChildsForIdAsArray($parent_id));
	}


	/**
	 * @var int
	 */
	protected static $num = 1;


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$obj = ctrlmmEntry::find($a_set['id']);
		$this->tpl->setVariable('TITLE', $obj->getTitle() . ' ' . ($obj->checkPermission() ? '' : '*'));
		$this->tpl->setVariable('TYPE', ctrlmmEntry::getClassAppendForValue($obj->getType()));
		$this->ctrl->setParameter($this->parent_obj, 'entry_id', $obj->getId());
		if (ctrlmmMenu::isOldILIAS()) {
			$this->tpl->setVariable('ID_OLD', $obj->getId());
			$this->tpl->setVariable('POSITION', self::$num);
			self::$num ++;
		} else {
			$this->tpl->setVariable('ID_NEW', $obj->getId());
		}
		if ($obj->getType() != ctrlmmMenu::TYPE_ADMIN AND ! $obj->getPlugin()) {
			$actions = new ilAdvancedSelectionListGUI();
			$actions->setId('actions_' . $obj->getId());
			$actions->setListTitle($this->pl->txt('actions'));
			$actions->addItem($this->pl->txt('edit'), 'edit', $this->ctrl->getLinkTarget($this->parent_obj, 'editEntry'));
			$actions->addItem($this->pl->txt('delete'), 'delete', $this->ctrl->getLinkTarget($this->parent_obj, 'deleteEntry'));
			if ($obj->getType() == ctrlmmMenu::TYPE_DROPDOWN) {
				$actions->addItem($this->pl->txt('edit_childs'), 'edit_childs', $this->ctrl->getLinkTarget($this->parent_obj, 'editChilds'));
			}
			$this->tpl->setVariable('ACTIONS', $actions->getHTML());
		}
	}
}

?>