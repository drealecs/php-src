--TEST--
dl(): partial function registration removes visible entries by identity
--SKIPIF--
<?php require dirname(__DIR__, 3) . "/dl_test/tests/skip.inc"; ?>
--FILE--
<?php
$module = 'phpt_dl_partial_rollback';
module_run($module, fn() => null);

function dl_test_test2(): string {
    return 'user';
}

$library = PHP_OS_FAMILY === 'Windows' ? 'php_dl_test.dll' : 'dl_test.so';
var_dump(@dl($library));
var_dump(function_exists('dl_test_test1'));
var_dump(module_run($module, fn() => function_exists('dl_test_test1')));
var_dump(dl_test_test2());
?>
--EXPECTF--
Warning: dl_test: Unable to register functions, unable to load in Unknown on line 0
bool(false)
bool(false)
bool(false)
string(4) "user"
