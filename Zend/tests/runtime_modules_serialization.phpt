--TEST--
Runtime modules: serialize keeps bare names and unserialize resolves by module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$m1 = 'phpt_serialize_m1';
$m2 = 'phpt_serialize_m2';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_308'
fn() => eval('class PhptSerialThing { function id() { return 1; } }')
MODULE_ENTRY_308
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_309'
fn() => eval('class PhptSerialThing { function id() { return 2; } }')
MODULE_ENTRY_309
, get_defined_vars(), __DIR__);
$serialized = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_310'
fn() => serialize(new PhptSerialThing())
MODULE_ENTRY_310
, get_defined_vars(), __DIR__);
var_dump(str_contains($serialized, '"PhptSerialThing"'));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_311'
fn() => unserialize($serialized)->id()
MODULE_ENTRY_311
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_312'
fn() => unserialize($serialized)->id()
MODULE_ENTRY_312
, get_defined_vars(), __DIR__));
?>
--EXPECT--
bool(true)
int(1)
int(2)
