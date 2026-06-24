--TEST--
Runtime modules: duplicate unrelated symbols and dependency visibility
--FILE--
<?php
$m1 = 'phpt_symbols_m1';
$m2 = 'phpt_symbols_m2';
module_run($m1, fn() => eval('class PhptSymbolsThing { function id() { return 1; } } function phpt_symbols_value() { return 1; } const PHPT_SYMBOLS_VALUE = 1;'));
module_run($m2, fn() => eval('class PhptSymbolsThing { function id() { return 2; } } function phpt_symbols_value() { return 2; } const PHPT_SYMBOLS_VALUE = 2;'));
$probe = fn() => [(new PhptSymbolsThing())->id(), phpt_symbols_value(), PHPT_SYMBOLS_VALUE];
var_dump(module_run($m1, $probe));
var_dump(module_run($m2, $probe));
var_dump(in_array('PhptSymbolsThing', module_run($m1, fn() => get_declared_classes()), true));
var_dump(in_array('phpt_symbols_value', module_run($m1, fn() => get_defined_functions())['user'], true));

$dep = 'phpt_symbols_dep';
$main = 'phpt_symbols_main';
module_run($dep, fn() => eval('class PhptSymbolsDepClass { function id() { return 10; } } function phpt_symbols_dep_func() { return 10; } const PHPT_SYMBOLS_DEP_CONST = 10;'));
module_run($main, fn() => module_add_dependency($dep));
var_dump(module_run($main, fn() => [(new PhptSymbolsDepClass())->id(), phpt_symbols_dep_func(), PHPT_SYMBOLS_DEP_CONST]));

class PhptSymbolsRootOnly {}
function phpt_symbols_root_only() { return 'root'; }
const PHPT_SYMBOLS_ROOT_ONLY = 'root';
var_dump(module_run($main, fn() => [
    class_exists('PhptSymbolsRootOnly', false),
    function_exists('phpt_symbols_root_only'),
    defined('PHPT_SYMBOLS_ROOT_ONLY'),
    class_exists('stdClass', false),
    function_exists('strlen'),
    defined('PHP_VERSION'),
]));
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
