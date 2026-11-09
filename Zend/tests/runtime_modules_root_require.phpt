--TEST--
Runtime modules: root file entry from module code restores its caller and owns declarations
--FILE--
<?php
$root = RuntimeModule::current();
$module = new RuntimeModule();
$GLOBALS['entry_root'] = $root;
$object = $module->require(__DIR__ . '/runtime_modules/root_caller.inc');
var_dump($object->context() === $root, $object instanceof RootEntryClass);
var_dump(RuntimeModule::current() === $root);
?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
