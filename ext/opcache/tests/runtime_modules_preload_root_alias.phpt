--TEST--
Runtime modules: preloaded root aliases remain root-local
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.preload={PWD}/runtime_modules_preload_root_alias.inc
--EXTENSIONS--
opcache
--SKIPIF--
<?php
if (PHP_OS_FAMILY == 'Windows') die('skip Preloading is not supported on Windows');
?>
--FILE--
<?php
require_once __DIR__ . '/../../../Zend/tests/runtime_modules/file_fixture.inc';

$alias = 'PhptRuntimeModulesPreloadRootAlias';
$module = 'phpt_preload_root_alias';

var_dump(interface_exists($alias, false));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_402'
fn() => interface_exists($alias, false)
MODULE_ENTRY_402
, get_defined_vars(), __DIR__));

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_403'
fn() => eval('interface PhptRuntimeModulesPreloadRootAlias {}')
MODULE_ENTRY_403
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_404'
fn() => interface_exists($alias, false)
MODULE_ENTRY_404
, get_defined_vars(), __DIR__));
?>
--EXPECT--
bool(true)
bool(false)
bool(true)
