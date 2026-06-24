--TEST--
Runtime modules: returned closures keep module symbol visibility
--FILE--
<?php
$module = 'phpt_returned_closure_symbols';

$closure = module_run($module, function (): Closure {
    eval(<<<'PHP'
        class PhptReturnedClosureThing { public static function id(): string { return 'class'; } }
        function phpt_returned_closure_function(): string { return 'function'; }
        const PHPT_RETURNED_CLOSURE_CONSTANT = 'constant';
    PHP);

    return fn() => [
        PhptReturnedClosureThing::id(),
        phpt_returned_closure_function(),
        PHPT_RETURNED_CLOSURE_CONSTANT,
        class_exists('PhptReturnedClosureThing', false),
        function_exists('phpt_returned_closure_function'),
        defined('PHPT_RETURNED_CLOSURE_CONSTANT'),
    ];
});

var_dump($closure());
var_dump(class_exists('PhptReturnedClosureThing', false));
var_dump(function_exists('phpt_returned_closure_function'));
var_dump(defined('PHPT_RETURNED_CLOSURE_CONSTANT'));
?>
--EXPECT--
array(6) {
  [0]=>
  string(5) "class"
  [1]=>
  string(8) "function"
  [2]=>
  string(8) "constant"
  [3]=>
  bool(true)
  [4]=>
  bool(true)
  [5]=>
  bool(true)
}
bool(false)
bool(false)
bool(false)
