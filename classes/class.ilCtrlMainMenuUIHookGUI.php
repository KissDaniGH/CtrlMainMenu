<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/UIComponent/classes/class.ilUIHookPluginGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Menu/class.ctrlmmMenuGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Menu/class.ctrlmmMenu.php');
require_once('class.ilCtrlMainMenuPlugin.php');


/**
 * User interface hook class
 *
 * @author            Alex Killing <alex.killing@gmx.de>
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           $Id$
 * @ingroup           ServicesUIComponent
 * @ilCtrl_IsCalledBy ilCtrlMainMenuUIHookGUI: ilAdministrationGUI, ilPersonalDesktopGUI, ilRepositoryGUI, ilObjPluginDispatchGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilCtrlMainMenuUIHookGUI: ilAdministrationGUI, ilPersonalDesktopGUI, ilRepositoryGUI, ilObjPluginDispatchGUI, ilCommonActionDispatcherGUI
 */
class ilCtrlMainMenuUIHookGUI extends ilUIHookPluginGUI {

	/**
	 * @param       $a_comp
	 * @param       $a_part
	 * @param array $a_par
	 *
	 * @return array
	 */
	public function getHTML($a_comp, $a_part, $a_par = array()) {

		if ($a_comp == 'Services/MainMenu' && $a_part == 'main_menu_list_entries') {
			return array(
				'mode' => ilUIHookPluginGUI::REPLACE,
				'html' => $this->getCtrlMainMenu()
			);
		}

		return array( 'mode' => ilUIHookPluginGUI::KEEP, 'html' => '' );
	}


	/**
	 * @return string
	 */
	public function getCtrlMainMenu() {
		$mm = new ctrlmmMenuGUI();

		return $mm->getHTML();
	}
}

?>
