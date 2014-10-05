<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntryGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/GroupedListDropdown/class.ctrlmmEntryGroupedListDropdownGUI.php');

/**
 * ctrlmmEntryDropdownGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.02
 *
 */
class ctrlmmEntryDropdownGUI extends ctrlmmEntryGroupedListDropdownGUI {

	const DOWN_ARROW_DARK = 'mm_down_arrow.png'; // ilAdvancedSelectionListGUI::DOWN_ARROW_DARK
	/**
	 * @var ctrlmmEntryDropdown
	 */
	public $entry;


	/**
	 * Render main menu entry
	 *
	 * @param
	 *
	 * @return html
	 */
	protected function setGroupedListContent() {
		/*
		 * MSTX - Bugfix - if previos is identicals to current. don't print
		foreach ($this->entry->getEntries() as $entry) {
			if ($entry->checkPermission()) {
				$this->gl->addEntry($entry->getTitle(), $entry->getLink(), $entry->getTarget(), '', '',
					'mm_pd_sel_items' . $entry->getId(), '', 'left center', 'right center', false);
			}
		}*/

		//MSTX - Bugfix - if previos is identicals to current. don't print
		$previus_link = "";
		foreach ($this->entry->getEntries() as $entry) {
			if ($entry->checkPermission()) {
				if($previus_link != $entry->getLink()) {
					$this->gl->addEntry($entry->getTitle(), $entry->getLink(), $entry->getTarget(), '', '',
						'mm_pd_sel_items' . $entry->getId(), '', 'left center', 'right center', false);
				}
				$previus_link = $entry->getLink();
			}
		}
	}


	/**
	 * @param string $mode
	 */
	public function initForm($mode = 'create') {
		parent::initForm($mode);
		$use_image = new ilCheckboxInputGUI($this->pl->txt('use_image'), 'use_image');
		$this->form->addItem($use_image);
	}


	public function setFormValuesByArray() {
		$values = parent::setFormValuesByArray();
		$values['use_image'] = $this->entry->getUseImage();
		$this->form->setValuesByArray($values);

		return $values;
	}


	public function createEntry() {
		parent::createEntry();
		$this->entry->setUseImage($this->form->getInput('use_image'));
		$this->entry->update();
	}
}

?>
