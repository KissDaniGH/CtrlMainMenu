<?php

include_once('./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php');
require_once('class.ilCtrlMainMenuConfig.php');

/**
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id$
 *
 */
class ilCtrlMainMenuPlugin extends ilUserInterfaceHookPlugin {

	/**
	 * @return string
	 */
	function getPluginName() {
		return 'CtrlMainMenu';
	}


	/**
	 * @return ilCtrlMainMenuConfig
	 */
	public function getConfigObject() {
		$conf = new ilCtrlMainMenuConfig($this->getConfigTableName());

		return $conf;
	}


	/**
	 * @return ilCtrlMainMenuConfig
	 */
	public function conf() {
		return $this->getConfigObject();
	}


	/**
	 * @return string
	 */
	public function getConfigTableName() {
		return
			$this->getSlotId() . substr(strtolower($this->getPluginName()), 0, 20 - strlen($this->getSlotId())) . '_c';
	}
}

?>
