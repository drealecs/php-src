--TEST--
Runtime modules: root aliases of internal classes remain root-local symbols
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

class_alias(stdClass::class, 'strlen');

var_dump(class_exists('strlen', false));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_root_internal_alias'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_300'
fn() => class_exists('strlen', false)
MODULE_ENTRY_300
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_root_internal_alias'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_301'
fn() => in_array(
    'strlen', get_declared_classes(), true
)
MODULE_ENTRY_301
, get_defined_vars(), __DIR__));

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_root_internal_alias'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_302'
fn() => eval('class strlen {}')
MODULE_ENTRY_302
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_root_internal_alias'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_303'
fn() => new strlen()
MODULE_ENTRY_303
, get_defined_vars(), __DIR__));
?>
--EXPECTF--
bool(true)
bool(false)
bool(false)
object(strlen)#%d (0) {
}
