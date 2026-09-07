--TEST--
dl(): internal symbols do not replace runtime-module symbols
--SKIPIF--
<?php require dirname(__DIR__, 3) . "/dl_test/tests/skip.inc"; ?>
--FILE--
<?php
$module = 'phpt_dl_collision';
module_run($module, fn() => eval(<<<'PHP'
    function dl_test_test1(): string { return 'module'; }
PHP));

$library = PHP_OS_FAMILY === 'Windows' ? 'php_dl_test.dll' : 'dl_test.so';
var_dump(@dl($library));
var_dump(module_run($module, fn() => dl_test_test1()));
?>
--EXPECTF--
Warning: dl_test: Unable to register functions, unable to load in Unknown on line 0
bool(false)
string(6) "module"
