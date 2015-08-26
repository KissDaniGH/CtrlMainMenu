<?php

/**
 * Class ctrlmmChecker
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ctrlmmChecker {

	/**
	 * @var array
	 */
	protected $classes = array();


	/**
	 * @param $gui_classes
	 */
	public static function check($gui_classes) {
		new self($gui_classes);
	}


	/**
	 * @param $gui_classes
	 */
	private function __construct($gui_classes) {
		$this->initILIAS();
		$this->setClasses(explode(',', $gui_classes));
		$this->printJson();
	}


	protected function printJson() {
		global $ilCtrl;
		/**
		 * @var $ilCtrl ilCtrl
		 */
		header('Content-Type: application/json');
		echo json_encode(array( 'status' => $ilCtrl->checkTargetClass($this->getClasses()) ));
	}


	//
	// Setter & Getter
	//
	/**
	 * @param array $classes
	 */
	public function setClasses($classes) {
		$this->classes = $classes;
	}


	/**
	 * @return array
	 */
	public function getClasses() {
		return $this->classes;
	}


	//
	// Helpers
	//
	private function initILIAS() {
		switch (trim(shell_exec('hostname'))) {
			case 'ilias-webt1':
			case 'ilias-webn1':
			case 'ilias-webn2':
			case 'ilias-webn3':
				$path = '/var/www/ilias-4.3.x';
				break;
			default:
				$path = substr(__FILE__, 0, strpos(__FILE__, 'Customizing'));
				break;
		}
		chdir($path);
		require_once('include/inc.header.php');
		self::includes();
	}


	private static function includes() {
	}
}

?>