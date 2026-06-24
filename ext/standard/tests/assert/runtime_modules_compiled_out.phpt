--TEST--
Runtime modules: compiled-out assertions do not evaluate their arguments
--INI--
zend.assertions=-1
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
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['compiled-out-assertions'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_415'
static fn() => eval($code)
MODULE_ENTRY_415
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['compiled-out-assertions'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_416'
static fn() => eval('namespace ModuleAssertions; ' . $code)
MODULE_ENTRY_416
, get_defined_vars(), __DIR__);
?>
--EXPECT--
int(0)
int(0)
int(0)
