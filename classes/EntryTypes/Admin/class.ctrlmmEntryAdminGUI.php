<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntryGUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/AdvancedSelectionListDropdown/class.ctrlmmEntryAdvancedSelectionListDropdownGUI.php');

/**
 * ctrlmmEntryAdminGUI
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
class ctrlmmEntryAdminGUI extends ctrlmmEntryAdvancedSelectionListDropdownGUI {

	/**
	 * @return string
	 */
	public function customizeAdvancedSelectionList() {
		global $lng;

		$this->selection->setListTitle($lng->txt('administration'));
		$this->selection->setId('dd_adm');
		$this->selection->setAsynch(true);
		$this->selection->setAsynchUrl('ilias.php?baseClass=ilAdministrationGUI&cmd=getDropDown&cmdMode=asynch');
	}
}

?>
