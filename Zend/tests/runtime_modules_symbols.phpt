--TEST--
Runtime modules: duplicate unrelated symbols and dependency visibility
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$m1 = 'phpt_symbols_m1';
$m2 = 'phpt_symbols_m2';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_12'
fn() => eval('class PhptSymbolsThing { function id() { return 1; } } function phpt_symbols_value() { return 1; } const PHPT_SYMBOLS_VALUE = 1;')
MODULE_ENTRY_12
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_13'
fn() => eval('class PhptSymbolsThing { function id() { return 2; } } function phpt_symbols_value() { return 2; } const PHPT_SYMBOLS_VALUE = 2;')
MODULE_ENTRY_13
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_14'
fn() => [(new PhptSymbolsThing())->id(), phpt_symbols_value(), PHPT_SYMBOLS_VALUE]
MODULE_ENTRY_14
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m2] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_15'
fn() => [(new PhptSymbolsThing())->id(), phpt_symbols_value(), PHPT_SYMBOLS_VALUE]
MODULE_ENTRY_15
, get_defined_vars(), __DIR__));
var_dump(in_array('PhptSymbolsThing', ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_16'
fn() => get_declared_classes()
MODULE_ENTRY_16
, get_defined_vars(), __DIR__), true));
var_dump(in_array('phpt_symbols_value', ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m1] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_17'
fn() => get_defined_functions()
MODULE_ENTRY_17
, get_defined_vars(), __DIR__)['user'], true));

$dep = 'phpt_symbols_dep';
$main = 'phpt_symbols_main';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_18'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule())
MODULE_ENTRY_18
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_19'
fn() => eval('class PhptSymbolsDepClass { function id() { return 10; } } function phpt_symbols_dep_func() { return 10; } const PHPT_SYMBOLS_DEP_CONST = 10;')
MODULE_ENTRY_19
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_20'
fn() => [(new PhptSymbolsDepClass())->id(), phpt_symbols_dep_func(), PHPT_SYMBOLS_DEP_CONST]
MODULE_ENTRY_20
, get_defined_vars(), __DIR__));

class PhptSymbolsRootOnly {}
function phpt_symbols_root_only() { return 'root'; }
const PHPT_SYMBOLS_ROOT_ONLY = 'root';
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_21'
fn() => [
    class_exists('PhptSymbolsRootOnly', false),
    function_exists('phpt_symbols_root_only'),
    defined('PHPT_SYMBOLS_ROOT_ONLY'),
    class_exists('stdClass', false),
    function_exists('strlen'),
    defined('PHP_VERSION'),
]
MODULE_ENTRY_21
, get_defined_vars(), __DIR__));
?>
--EXPECT--
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(1)
  [2]=>
  int(1)
}
array(3) {
  [0]=>
  int(2)
  [1]=>
  int(2)
  [2]=>
  int(2)
}
bool(true)
bool(true)
array(3) {
  [0]=>
  int(10)
  [1]=>
  int(10)
  [2]=>
  int(10)
}
array(6) {
  [0]=>
  bool(false)
  [1]=>
  bool(false)
  [2]=>
  bool(false)
  [3]=>
  bool(true)
  [4]=>
  bool(true)
  [5]=>
  bool(true)
}
