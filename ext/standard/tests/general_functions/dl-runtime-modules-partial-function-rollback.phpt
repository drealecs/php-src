--TEST--
dl(): partial function registration removes visible entries by identity
--SKIPIF--
<?php require dirname(__DIR__, 3) . "/dl_test/tests/skip.inc"; ?>
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

$module = 'phpt_dl_partial_rollback';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_426'
fn() => null
MODULE_ENTRY_426
, get_defined_vars(), __DIR__);

function dl_test_test2(): string {
    return 'user';
}

$library = PHP_OS_FAMILY === 'Windows' ? 'php_dl_test.dll' : 'dl_test.so';
var_dump(@dl($library));
var_dump(function_exists('dl_test_test1'));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_427'
fn() => function_exists('dl_test_test1')
MODULE_ENTRY_427
, get_defined_vars(), __DIR__));
var_dump(dl_test_test2());
?>
--EXPECTF--
Warning: dl_test: Unable to register functions, unable to load in Unknown on line 0
bool(false)
bool(false)
bool(false)
string(4) "user"
