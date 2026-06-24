--TEST--
dl(): internal symbols do not replace runtime-module symbols
--SKIPIF--
<?php require dirname(__DIR__, 3) . "/dl_test/tests/skip.inc"; ?>
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

$module = 'phpt_dl_collision';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_428'
fn() => eval(<<<'PHP'
    function dl_test_test1(): string { return 'module'; }
PHP)
MODULE_ENTRY_428
, get_defined_vars(), __DIR__);

$library = PHP_OS_FAMILY === 'Windows' ? 'php_dl_test.dll' : 'dl_test.so';
var_dump(@dl($library));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_429'
fn() => dl_test_test1()
MODULE_ENTRY_429
, get_defined_vars(), __DIR__));
?>
--EXPECTF--
Warning: dl_test: Unable to register functions, unable to load in Unknown on line 0
bool(false)
string(6) "module"
