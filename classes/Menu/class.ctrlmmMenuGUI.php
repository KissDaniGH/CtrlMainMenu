<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
//MST 20131130: I commented out the following line because of problems with ILIAS Modules which use include instead of include_once
//require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/class.ilCtrlMainMenuPlugin.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntryGUI.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntry.php');

/**
 * User interface hook class
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @version           $Id$
 * @ingroup           ServicesUIComponent
 *
 */
class ctrlmmMenuGUI {

	/**
	 * @var ilTemplate
	 */
	protected $html;


	public function __construct($id = 0) {
		global $tpl;

		$this->pl = new ilCtrlMainMenuPlugin();
		$this->object = new ctrlmmMenu($id);

		$tpl->addCss($this->pl->getDirectory() . '/templates/css/ctrlmm.css');
		if ($this->pl->conf()->getCssPrefix() == 'fb') {
			$tpl->addCss($this->pl->getDirectory() . '/templates/css/fb.css');
		}
		if ($this->pl->conf()->getSimpleFormValidation()) {
			$tpl->addCss($this->pl->getDirectory() . '/templates/css/forms.css');
			$tpl->addJavaScript($this->pl->getDirectory() . '/templates/js/forms.js');
		}
		if ($this->pl->conf()->getDoubleclickPrevention()) {
			$tpl->addCss($this->pl->getDirectory() . '/templates/css/click.css');
			$tpl->addJavaScript($this->pl->getDirectory() . '/templates/js/click.js');
		}
	}


	public function getHTML() {
		$this->html = $this->pl->getTemplate('tpl.menu.html');
		$eh = '';
		foreach ($this->object->getEntries() as $entry) {
			if ($entry->checkPermission()) {
				$type = 'ctrlmmEntry' . ctrlmmEntry::getClassAppendForValue($entry->getType()) . 'GUI';
				/**
				 * @var $entryGui ctrlmmEntryDropdownGUI
				 */
				$entryGui = new $type($entry);
				$eh .= $entryGui->prepareAndRenderEntry();
			}
		}
		$this->html->setVariable('ENTRIES', $eh);
		$this->html->setVariable('CSS_PREFIX', ctrlmmMenu::getCssPrefix());

		return $this->html->get();
	}
}

?>
