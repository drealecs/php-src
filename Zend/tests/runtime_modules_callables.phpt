--TEST--
Runtime modules: callable lookup is module-aware
--FILE--
<?php
$a = 'phpt_callables_a';
$b = 'phpt_callables_b';
$none = 'phpt_callables_none';

module_run($a, fn() => eval('
    function phpt_callable_value() { return "A function"; }
    class PhptCallableTarget {
        public static function value() { return "A static"; }
        public function instance() { return "A instance"; }
    }
'));

module_run($b, fn() => eval('
    function phpt_callable_value() { return "B function"; }
    class PhptCallableTarget {
        public static function value() { return "B static"; }
        public function instance() { return "B instance"; }
    }
'));

$probe = fn() => [
    is_callable('phpt_callable_value'),
    call_user_func('phpt_callable_value'),
    is_callable('PhptCallableTarget::value'),
    call_user_func('PhptCallableTarget::value'),
    is_callable(['PhptCallableTarget', 'value']),
    call_user_func(['PhptCallableTarget', 'value']),
];

var_dump(module_run($a, $probe));
var_dump(module_run($b, $probe));
var_dump(module_run($none, fn() => [
    is_callable('phpt_callable_value'),
    is_callable('PhptCallableTarget::value'),
]));

$object = module_run($a, fn() => new PhptCallableTarget());
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
