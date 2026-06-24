--TEST--
Runtime modules: cyclic dependencies remain direct-only
--FILE--
<?php
$a = 'phpt_cycle_a';
$b = 'phpt_cycle_b';
$c = 'phpt_cycle_c';

module_run($a, fn() => module_add_dependency($b));
module_run($b, fn() => module_add_dependency($a));
module_run($b, fn() => module_add_dependency($c));

module_run($a, fn() => eval('class PhptCycleA {} function phpt_cycle_a() { return "A"; } const PHPT_CYCLE_A = "A";'));
module_run($b, fn() => eval('class PhptCycleB {} function phpt_cycle_b() { return "B"; } const PHPT_CYCLE_B = "B";'));
module_run($c, fn() => eval('class PhptCycleC {} function phpt_cycle_c() { return "C"; } const PHPT_CYCLE_C = "C";'));

var_dump(module_run($a, fn() => [
    class_exists('PhptCycleB', false),
    function_exists('phpt_cycle_b'),
    defined('PHPT_CYCLE_B'),
    class_exists('PhptCycleC', false),
    function_exists('phpt_cycle_c'),
    defined('PHPT_CYCLE_C'),
]));

var_dump(module_run($b, fn() => [
    class_exists('PhptCycleA', false),
    class_exists('PhptCycleC', false),
    phpt_cycle_a(),
    phpt_cycle_c(),
    PHPT_CYCLE_A,
    PHPT_CYCLE_C,
]));
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
