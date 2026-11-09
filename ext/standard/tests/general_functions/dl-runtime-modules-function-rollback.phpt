--TEST--
dl(): failed function registration preserves the existing function
--SKIPIF--
<?php require dirname(__DIR__, 3) . "/dl_test/tests/skip.inc"; ?>
--FILE--
<?php
function dl_test_test1(): string {
    return 'user';
}

$library = PHP_OS_FAMILY === 'Windows' ? 'php_dl_test.dll' : 'dl_test.so';
var_dump(@dl($library));
var_dump(function_exists('dl_test_test1'));
var_dump(dl_test_test1());
?>
--EXPECTF--
Warning: dl_test: Unable to register functions, unable to load in Unknown on line 0
bool(false)
bool(true)
string(4) "user"
