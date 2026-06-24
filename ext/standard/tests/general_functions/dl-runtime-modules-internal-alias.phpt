--TEST--
dl(): internal symbols are visible to existing and future runtime modules
--SKIPIF--
<?php require dirname(__DIR__, 3) . "/dl_test/tests/skip.inc"; ?>
--FILE--
<?php
module_run('phpt_dl_alias_before', fn() => null);

$library = PHP_OS_FAMILY === 'Windows' ? 'php_dl_test.dll' : 'dl_test.so';
var_dump(dl($library));
foreach (['phpt_dl_alias_before', 'phpt_dl_alias_after'] as $module) {
    echo module_run($module, fn() => sprintf('%d%d%d%d',
        class_exists('DlTest', false),
        class_exists('DlTestAlias', false),
        function_exists('dl_test_test1'),
        defined('DL_TEST_CONST'),
    )), "\n";
}
?>
--EXPECT--
bool(true)
1111
1111
