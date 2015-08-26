<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntryFormGUI.php');

/**
 * Class ctrlmmEntryCtrlFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ctrlmmEntryCtrlFormGUI extends ctrlmmEntryFormGUI {

	public function addFields() {
		$te = new ilTextInputGUI($this->pl->txt('test'), 'test');
		$this->addItem($te);
	}
}

?>
