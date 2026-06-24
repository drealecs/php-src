--TEST--
Runtime modules: lookup APIs use current module visibility without autoload leakage
--FILE--
<?php
$a = 'phpt_lookup_apis_a';
$b = 'phpt_lookup_apis_b';
$dep = 'phpt_lookup_apis_dep';
$main = 'phpt_lookup_apis_main';
$autoloadEvents = [];

module_run($a, function (): void {
    eval(<<<'PHP'
        class PhptLookupApisClass { public static function id(): string { return 'A'; } }
        interface PhptLookupApisInterface {}
        enum PhptLookupApisEnum { case A; }
    PHP);
});

module_run($b, function (): void {
    eval(<<<'PHP'
        class PhptLookupApisClass { public static function id(): string { return 'B'; } }
        interface PhptLookupApisInterface {}
        enum PhptLookupApisEnum { case B; }
    PHP);
});

module_run($dep, function (): void {
    eval(<<<'PHP'
        class PhptLookupApisDepClass {}
        interface PhptLookupApisDepInterface {}
        enum PhptLookupApisDepEnum { case Dep; }
    PHP);
});

module_run($main, function () use ($dep, &$autoloadEvents): void {
    module_add_dependency($dep);
    spl_autoload_register(function (string $class) use (&$autoloadEvents): void {
        $autoloadEvents[] = $class;
    });
});

var_dump(module_run($a, fn() => [
    class_exists('PhptLookupApisClass', false),
    interface_exists('PhptLookupApisInterface', false),
    enum_exists('PhptLookupApisEnum', false),
    PhptLookupApisClass::id(),
]));

var_dump(module_run($b, fn() => [
    class_exists('PhptLookupApisClass', false),
    interface_exists('PhptLookupApisInterface', false),
    enum_exists('PhptLookupApisEnum', false),
    PhptLookupApisClass::id(),
]));

var_dump(module_run($main, fn() => [
    class_exists('PhptLookupApisDepClass', false),
    interface_exists('PhptLookupApisDepInterface', false),
    enum_exists('PhptLookupApisDepEnum', false),
    class_exists('PhptLookupApisMissingClass', false),
]));

var_dump($autoloadEvents);
?>
--EXPECT--
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
  [3]=>
  string(1) "A"
}
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
  [3]=>
  string(1) "B"
}
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
  [3]=>
  bool(false)
}
array(0) {
}
