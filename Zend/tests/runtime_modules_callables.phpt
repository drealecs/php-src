--TEST--
Runtime modules: callable lookup is module-aware
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_callables_a';
$b = 'phpt_callables_b';
$none = 'phpt_callables_none';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_21'
fn() => eval('
    function phpt_callable_value() { return "A function"; }
    class PhptCallableTarget {
        public static function value() { return "A static"; }
        public function instance() { return "A instance"; }
    }
')
MODULE_ENTRY_21
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_22'
fn() => eval('
    function phpt_callable_value() { return "B function"; }
    class PhptCallableTarget {
        public static function value() { return "B static"; }
        public function instance() { return "B instance"; }
    }
')
MODULE_ENTRY_22
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_23'
fn() => [
    is_callable('phpt_callable_value'),
    call_user_func('phpt_callable_value'),
    is_callable('PhptCallableTarget::value'),
    call_user_func('PhptCallableTarget::value'),
    is_callable(['PhptCallableTarget', 'value']),
    call_user_func(['PhptCallableTarget', 'value']),
]
MODULE_ENTRY_23
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_24'
fn() => [
    is_callable('phpt_callable_value'),
    call_user_func('phpt_callable_value'),
    is_callable('PhptCallableTarget::value'),
    call_user_func('PhptCallableTarget::value'),
    is_callable(['PhptCallableTarget', 'value']),
    call_user_func(['PhptCallableTarget', 'value']),
]
MODULE_ENTRY_24
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$none] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_25'
fn() => [
    is_callable('phpt_callable_value'),
    is_callable('PhptCallableTarget::value'),
]
MODULE_ENTRY_25
, get_defined_vars(), __DIR__));

$object = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_26'
fn() => new PhptCallableTarget()
MODULE_ENTRY_26
, get_defined_vars(), __DIR__);
var_dump(is_callable([$object, 'instance']));
var_dump(call_user_func([$object, 'instance']));
?>
--EXPECT--
array(6) {
  [0]=>
  bool(true)
  [1]=>
  string(10) "A function"
  [2]=>
  bool(true)
  [3]=>
  string(8) "A static"
  [4]=>
  bool(true)
  [5]=>
  string(8) "A static"
}
array(6) {
  [0]=>
  bool(true)
  [1]=>
  string(10) "B function"
  [2]=>
  bool(true)
  [3]=>
  string(8) "B static"
  [4]=>
  bool(true)
  [5]=>
  string(8) "B static"
}
array(2) {
  [0]=>
  bool(false)
  [1]=>
  bool(false)
}
bool(true)
string(10) "A instance"
