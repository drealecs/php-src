--TEST--
Runtime modules: shared call sites resolve module-local symbols repeatedly
--FILE--
<?php
$a = 'phpt_shared_call_site_a';
$b = 'phpt_shared_call_site_b';

module_run($a, function (): void {
    eval(<<<'PHP'
        class PhptSharedCallSiteThing {
            public const VALUE = 'class-A';
            public static function id(): string { return 'class-A'; }
        }
        function phpt_shared_call_site_function(): string { return 'function-A'; }
        const PHPT_SHARED_CALL_SITE_CONSTANT = 'constant-A';
    PHP);
});

module_run($b, function (): void {
    eval(<<<'PHP'
        class PhptSharedCallSiteThing {
            public const VALUE = 'class-B';
            public static function id(): string { return 'class-B'; }
        }
        function phpt_shared_call_site_function(): string { return 'function-B'; }
        const PHPT_SHARED_CALL_SITE_CONSTANT = 'constant-B';
    PHP);
});

$probe = fn() => [
    (new PhptSharedCallSiteThing())::id(),
    PhptSharedCallSiteThing::VALUE,
    phpt_shared_call_site_function(),
    PHPT_SHARED_CALL_SITE_CONSTANT,
    constant('PHPT_SHARED_CALL_SITE_CONSTANT'),
];

var_dump(module_run($a, $probe));
var_dump(module_run($b, $probe));
var_dump(module_run($a, $probe));
var_dump(module_run($b, $probe));
?>
--EXPECT--
array(5) {
  [0]=>
  string(7) "class-A"
  [1]=>
  string(7) "class-A"
  [2]=>
  string(10) "function-A"
  [3]=>
  string(10) "constant-A"
  [4]=>
  string(10) "constant-A"
}
array(5) {
  [0]=>
  string(7) "class-B"
  [1]=>
  string(7) "class-B"
  [2]=>
  string(10) "function-B"
  [3]=>
  string(10) "constant-B"
  [4]=>
  string(10) "constant-B"
}
array(5) {
  [0]=>
  string(7) "class-A"
  [1]=>
  string(7) "class-A"
  [2]=>
  string(10) "function-A"
  [3]=>
  string(10) "constant-A"
  [4]=>
  string(10) "constant-A"
}
array(5) {
  [0]=>
  string(7) "class-B"
  [1]=>
  string(7) "class-B"
  [2]=>
  string(10) "function-B"
  [3]=>
  string(10) "constant-B"
  [4]=>
  string(10) "constant-B"
}
