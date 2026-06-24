--TEST--
Runtime modules: disabled assertions do not evaluate their arguments
--INI--
zend.assertions=0
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

$code = <<<'PHP'
    $count = 0;
    assert(++$count);
    \assert(++$count);
    var_dump($count);
PHP;
eval($code);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['disabled-assertions'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_417'
static fn() => eval($code)
MODULE_ENTRY_417
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['disabled-assertions'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_418'
static fn() => eval('namespace ModuleAssertions; ' . $code)
MODULE_ENTRY_418
, get_defined_vars(), __DIR__);
?>
--EXPECT--
int(0)
int(0)
int(0)
