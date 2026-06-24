--TEST--
dl(): internal constants do not replace runtime-module constants
--SKIPIF--
<?php require dirname(__DIR__, 3) . "/dl_test/tests/skip.inc"; ?>
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

$module = 'phpt_dl_constant_collision';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_422'
fn() => define('DL_TEST_CONST', 'module')
MODULE_ENTRY_422
, get_defined_vars(), __DIR__);

$library = PHP_OS_FAMILY === 'Windows' ? 'php_dl_test.dll' : 'dl_test.so';
var_dump(dl($library));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_423'
fn() => DL_TEST_CONST
MODULE_ENTRY_423
, get_defined_vars(), __DIR__));
?>
--EXPECTF--
Warning: Constant DL_TEST_CONST already defined, this will be an error in PHP 9 in %s on line %d
bool(true)
string(6) "module"
