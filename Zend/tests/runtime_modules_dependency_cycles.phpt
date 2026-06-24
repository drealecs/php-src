--TEST--
Runtime modules: cyclic dependencies remain direct-only
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_cycle_a';
$b = 'phpt_cycle_b';
$c = 'phpt_cycle_c';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_134'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule())
MODULE_ENTRY_134
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_135'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule())
MODULE_ENTRY_135
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_136'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$c] ??= new \RuntimeModule())
MODULE_ENTRY_136
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_137'
fn() => eval('class PhptCycleA {} function phpt_cycle_a() { return "A"; } const PHPT_CYCLE_A = "A";')
MODULE_ENTRY_137
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_138'
fn() => eval('class PhptCycleB {} function phpt_cycle_b() { return "B"; } const PHPT_CYCLE_B = "B";')
MODULE_ENTRY_138
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$c] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_139'
fn() => eval('class PhptCycleC {} function phpt_cycle_c() { return "C"; } const PHPT_CYCLE_C = "C";')
MODULE_ENTRY_139
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_140'
fn() => [
    class_exists('PhptCycleB', false),
    function_exists('phpt_cycle_b'),
    defined('PHPT_CYCLE_B'),
    class_exists('PhptCycleC', false),
    function_exists('phpt_cycle_c'),
    defined('PHPT_CYCLE_C'),
]
MODULE_ENTRY_140
, get_defined_vars(), __DIR__));

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_141'
fn() => [
    class_exists('PhptCycleA', false),
    class_exists('PhptCycleC', false),
    phpt_cycle_a(),
    phpt_cycle_c(),
    PHPT_CYCLE_A,
    PHPT_CYCLE_C,
]
MODULE_ENTRY_141
, get_defined_vars(), __DIR__));
?>
--EXPECT--
array(6) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
  [3]=>
  bool(false)
  [4]=>
  bool(false)
  [5]=>
  bool(false)
}
array(6) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  string(1) "A"
  [3]=>
  string(1) "C"
  [4]=>
  string(1) "A"
  [5]=>
  string(1) "C"
}
