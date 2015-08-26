<#1>
<?php
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Menu/class.ctrlmmMenu.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntry.php');
$fields = array(
	'id' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'position' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'type' => array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => true
	),
	'title' => array(
		'type' => 'text',
		'length' => 128,
	),
	'link' => array(
		'type' => 'text',
		'length' => 256,
	),
	'data' => array(
		'type' => 'text',
		'length' => 1024,
	),
	'permission' => array(
		'type' => 'text',
		'length' => 256,
	),
	'permission_type' => array(
		'type' => 'integer',
		'length' => 4,
	),
	'parent' => array(
		'type' => 'integer',
		'length' => 1,
	)
);
/**
 * @var $ilDB ilDB
 */
if (! $ilDB->tableExists(ctrlmmEntry::TABLE_NAME)) {
	$ilDB->createTable(ctrlmmEntry::TABLE_NAME, $fields);
	$ilDB->addPrimaryKey(ctrlmmEntry::TABLE_NAME, array( 'id' ));
	//	if (! $ilDB->sequenceExists(ctrlmmEntry::TABLE_NAME)) {
	$ilDB->createSequence(ctrlmmEntry::TABLE_NAME);
	//	}
}

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/class.ilCtrlMainMenuPlugin.php');
$pl = new ilCtrlMainMenuPlugin();
$pl->getConfigObject()->initDB();
$pl->getConfigObject()->setValue('css_prefix', 'il');
$pl->getConfigObject()->setValue('css_active', 'MMActive');
$pl->getConfigObject()->setValue('css_inactive', 'MMInactive');

require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/class.ctrlmmData.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/Entry/class.ctrlmmEntry.php');
require_once('./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CtrlMainMenu/classes/class.ctrlmmTranslation.php');
$pl = new ctrlmmData();
$pl->initDB();

$pl = new ctrlmmTranslation();
$pl->initDB();


foreach (ctrlmmEntry::getAll() as $e) {
	$e->migrate();
}

$ilDB->dropTableColumn(ctrlmmEntry::TABLE_NAME, "title");
$ilDB->dropTableColumn(ctrlmmEntry::TABLE_NAME, "data");

ctrlmmEntry::includeAllEntryTypes();

$desktop = new ctrlmmEntryDesktop();
$desktop->setPosition(1);
$desktop->create();

$repo = new ctrlmmEntryRepository();
$repo->setPosition(2);
$repo->create();

$admin = new ctrlmmEntryAdmin();
$admin->setPosition(3);
$admin->create();
?>

<#2>
<?php
/**
 * @var $ilDB ilDB
 */
$q = "DELETE FROM ctrl_calls WHERE comp_prefix = " . $ilDB->quote('ui_uihk_ctrlmainmenu', 'text');
$ilDB->query($q);

$q = "DELETE FROM ctrl_classfile WHERE comp_prefix = " . $ilDB->quote('ui_uihk_ctrlmainmenu', 'text');
$ilDB->query($q);

$q = "UPDATE il_plugin SET plugin_id = " . $ilDB->quote('ctrlmm', 'text')
	. " WHERE name = " . $ilDB->quote('CtrlMainMenu', 'text');
$ilDB->query($q);
?>

