--TEST--
Runtime modules: returned callables keep separate caches across alternating invocations
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_shared_call_site_a';
$b = 'phpt_shared_call_site_b';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_313'
function (): void {
    eval(<<<'PHP'
        class PhptSharedCallSiteThing {
            public const VALUE = 'class-A';
            public static function id(): string { return 'class-A'; }
        }
        function phpt_shared_call_site_function(): string { return 'function-A'; }
        const PHPT_SHARED_CALL_SITE_CONSTANT = 'constant-A';
    PHP);
}
MODULE_ENTRY_313
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_314'
function (): void {
    eval(<<<'PHP'
        class PhptSharedCallSiteThing {
            public const VALUE = 'class-B';
            public static function id(): string { return 'class-B'; }
        }
        function phpt_shared_call_site_function(): string { return 'function-B'; }
        const PHPT_SHARED_CALL_SITE_CONSTANT = 'constant-B';
    PHP);
}
MODULE_ENTRY_314
, get_defined_vars(), __DIR__);

$probeA = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_315'
fn() => fn() => [
    (new PhptSharedCallSiteThing())::id(),
    PhptSharedCallSiteThing::VALUE,
    phpt_shared_call_site_function(),
    PHPT_SHARED_CALL_SITE_CONSTANT,
    constant('PHPT_SHARED_CALL_SITE_CONSTANT'),
]
MODULE_ENTRY_315
, get_defined_vars(), __DIR__);
$probeB = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_316'
fn() => fn() => [
    (new PhptSharedCallSiteThing())::id(),
    PhptSharedCallSiteThing::VALUE,
    phpt_shared_call_site_function(),
    PHPT_SHARED_CALL_SITE_CONSTANT,
    constant('PHPT_SHARED_CALL_SITE_CONSTANT'),
]
MODULE_ENTRY_316
, get_defined_vars(), __DIR__);
var_dump($probeA(), $probeB(), $probeA(), $probeB());
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
