<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntry.php');

/**
 * Application class for ctrlmmEntryCtrl Object.
 *
 * @author         Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version        2.0.02
 */
class ctrlmmEntryAdmin extends ctrlmmEntry {

	/**
	 * @var bool
	 */
	protected $restricted = true;


	public function __construct($primary_key = 0) {
		$this->setType(ctrlmmMenu::TYPE_ADMIN);

		parent::__construct($primary_key);
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		global $lng;

		return $this->title ? $this->title : $lng->txt('administration');
	}


	/**
	 * @return bool
	 */
	public function isActive() {
		global $ilMainMenu;

		return ($ilMainMenu->active == 'administration');
	}


//	/**
//	 * @return bool
//	 */
//	public function checkPermission() {
//		global $rbacsystem;
//
//		return $rbacsystem->checkAccess('visible', SYSTEM_FOLDER_ID);
//	}
}