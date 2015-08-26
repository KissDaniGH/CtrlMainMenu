<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntryGUI.php');
require_once('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');

/**
 * ctrlmmEntryAdvancedSelectionListDropdownGUI
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 *
 */
abstract class ctrlmmEntryAdvancedSelectionListDropdownGUI extends ctrlmmEntryGUI {

	/**
	 * @var ilAdvancedSelectionListGUI
	 */
	protected $selection = NULL;
	/**
	 * @var ilTemplate
	 */
	protected $html;


	/**
	 * @return string
	 */
	public function renderEntry() {
		$this->selection = new ilAdvancedSelectionListGUI();

		$this->selection->setSelectionHeaderClass(($this->isActive() ? $this->pl->conf()
			->getCssActive() : $this->pl->conf()->getCssInactive()));

		$this->selection->setSelectionHeaderSpanClass('MMSpan');
		$this->selection->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_LIGHT);
		$this->selection->setItemLinkClass('small');
		$this->selection->setUseImages(false);

		$this->customizeAdvancedSelectionList();

		$this->html = $this->pl->getTemplate('tpl.admin_entry.html', false, false);
		$this->html->setVariable('DROPDOWN', $this->selection->getHTML());
		$this->html->setVariable('CSS_PREFIX', ctrlmmMenu::getCssPrefix());

		return $this->html->get();
	}


	abstract protected function customizeAdvancedSelectionList();
}

?>
