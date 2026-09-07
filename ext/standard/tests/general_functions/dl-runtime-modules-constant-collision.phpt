--TEST--
dl(): internal constants do not replace runtime-module constants
--SKIPIF--
<?php require dirname(__DIR__, 3) . "/dl_test/tests/skip.inc"; ?>
--FILE--
<?php
$module = 'phpt_dl_constant_collision';
module_run($module, fn() => define('DL_TEST_CONST', 'module'));

$library = PHP_OS_FAMILY === 'Windows' ? 'php_dl_test.dll' : 'dl_test.so';
var_dump(dl($library));
var_dump(module_run($module, fn() => DL_TEST_CONST));
?>
--EXPECTF--
Warning: Constant DL_TEST_CONST already defined, this will be an error in PHP 9 in %s on line %d
bool(true)
string(6) "module"
