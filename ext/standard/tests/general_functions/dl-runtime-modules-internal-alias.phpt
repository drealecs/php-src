--TEST--
dl(): internal symbols are visible to existing and future runtime modules
--SKIPIF--
<?php require dirname(__DIR__, 3) . "/dl_test/tests/skip.inc"; ?>
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_dl_alias_before'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_424'
fn() => null
MODULE_ENTRY_424
, get_defined_vars(), __DIR__);

$library = PHP_OS_FAMILY === 'Windows' ? 'php_dl_test.dll' : 'dl_test.so';
var_dump(dl($library));
foreach (['phpt_dl_alias_before', 'phpt_dl_alias_after'] as $module) {
    echo ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_425'
fn() => sprintf('%d%d%d%d',
        class_exists('DlTest', false),
        class_exists('DlTestAlias', false),
        function_exists('dl_test_test1'),
        defined('DL_TEST_CONST'),
    )
MODULE_ENTRY_425
, get_defined_vars(), __DIR__), "\n";
}
?>
--EXPECT--
bool(true)
1111
1111
