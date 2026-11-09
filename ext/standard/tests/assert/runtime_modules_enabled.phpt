--TEST--
Runtime modules: enabled assertions retain generated descriptions and module lookup
--INI--
zend.assertions=1
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['enabled-assertions'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_419'
static function (): void {
    define('ASSERTION_VALUE', false);
    try {
        eval('assert(ASSERTION_VALUE);');
    } catch (AssertionError $error) {
        echo $error->getMessage(), "\n";
    }
}
MODULE_ENTRY_419
, get_defined_vars(), __DIR__);
?>
--EXPECT--
assert(ASSERTION_VALUE)
