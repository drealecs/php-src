--TEST--
Runtime modules: context and cache regressions
--FILE--
<?php
function phpt_regression_global_probe(): bool {
    return class_exists('PhptRegressionContextThing', false);
}

$context = 'phpt_regression_context';
$globalProbe = phpt_regression_global_probe(...);
module_run($context, fn() => eval('class PhptRegressionContextThing {} function phpt_regression_module_caller(Closure $callback): bool { return $callback(); }'));
var_dump(module_run($context, fn() => phpt_regression_module_caller($globalProbe)));
var_dump(class_exists('PhptRegressionContextThing', false));

$ceCache = 'phpt_regression_ce_cache';
module_run($ceCache, fn() => eval('class PhptRegressionCECache { public const X = 1; }'));
var_dump(class_exists('PhptRegressionCECache', false));
var_dump(defined('PhptRegressionCECache::X'));

$staticA = 'phpt_regression_static_a';
$staticB = 'phpt_regression_static_b';
module_run($staticA, fn() => eval('class PhptRegressionStaticThing { public static int $value = 1; }'));
module_run($staticB, fn() => eval('class PhptRegressionStaticThing { public static int $value = 2; }'));
$staticProbe = fn() => PhptRegressionStaticThing::$value;
var_dump([module_run($staticA, $staticProbe), module_run($staticB, $staticProbe)]);

$nested = 'phpt_regression_nested';
$declareNested = function (): void {
    function phpt_regression_nested_function(): string {
        return 'module';
    }
};
module_run($nested, $declareNested);
var_dump(function_exists('phpt_regression_nested_function'));
var_dump(module_run($nested, fn() => phpt_regression_nested_function()));

$closureModule = 'phpt_regression_returned_closure';
module_run($closureModule, fn() => eval('class PhptRegressionReturnedClosureThing {}'));
$returnedClosure = module_run($closureModule, fn() => fn() => class_exists('PhptRegressionReturnedClosureThing', false));
var_dump($returnedClosure());

$nestedA = 'phpt_regression_nested_a';
$nestedB = 'phpt_regression_nested_b';
module_run($nestedA, fn() => eval('const PHPT_REGRESSION_NESTED_VALUE = "A";'));
module_run($nestedB, fn() => eval('const PHPT_REGRESSION_NESTED_VALUE = "B";'));
$declareNestedDuplicate = function (): void {
    function phpt_regression_nested_duplicate(): string {
        return PHPT_REGRESSION_NESTED_VALUE;
    }
};
module_run($nestedA, $declareNestedDuplicate);
module_run($nestedB, $declareNestedDuplicate);
var_dump(module_run($nestedA, fn() => phpt_regression_nested_duplicate()));
var_dump(module_run($nestedB, fn() => phpt_regression_nested_duplicate()));

$isAA = 'phpt_regression_is_a_a';
$isAB = 'phpt_regression_is_a_b';
module_run($isAA, fn() => eval('class PhptRegressionIsAThing {}'));
module_run($isAB, fn() => eval('class PhptRegressionIsAThing {}'));
$isAObject = module_run($isAA, fn() => new PhptRegressionIsAThing());
var_dump(module_run($isAB, fn() => [$isAObject instanceof PhptRegressionIsAThing, is_a($isAObject, 'PhptRegressionIsAThing')]));

$categorizedConstants = 'phpt_regression_categorized_constants';
module_run($categorizedConstants, fn() => eval('const PHPT_REGRESSION_CATEGORIZED_CONSTANT = 1;'));
var_dump(module_run($categorizedConstants, fn() => array_key_exists('PHPT_REGRESSION_CATEGORIZED_CONSTANT', get_defined_constants(true)['user'] ?? [])));

$autoloadA = 'phpt_regression_autoload_a';
$autoloadB = 'phpt_regression_autoload_b';
module_run($autoloadB, function (): void {
    spl_autoload_register(function (string $class): void {
        if ($class === 'PhptRegressionAutoloadGuard') {
            eval('class PhptRegressionAutoloadGuard { public static function id(): string { return "B"; } }');
        }
    });
});
module_run($autoloadA, function () use ($autoloadB): void {
    spl_autoload_register(function (string $class) use ($autoloadB): void {
        if ($class === 'PhptRegressionAutoloadGuard') {
            var_dump(module_run($autoloadB, fn() => class_exists('PhptRegressionAutoloadGuard')));
            eval('class PhptRegressionAutoloadGuard { public static function id(): string { return "A"; } }');
        }
    });
});
var_dump(module_run($autoloadA, fn() => PhptRegressionAutoloadGuard::id()));
var_dump(module_run($autoloadB, fn() => PhptRegressionAutoloadGuard::id()));

$anonymous = 'phpt_regression_anonymous';
var_dump(module_run($anonymous, fn() => eval('return new class { public function id(): string { return "anon"; } };'))->id());

$shutdown = 'phpt_regression_shutdown';
module_run($shutdown, function (): void {
    spl_autoload_register(function (string $class): void {});
    eval('class PhptRegressionShutdown { public static object $object; } PhptRegressionShutdown::$object = new stdClass();');
});
echo "shutdown roots set\n";
?>
--EXPECT--
bool(false)
bool(false)
bool(false)
bool(false)
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(2)
}
bool(false)
string(6) "module"
bool(true)
string(1) "A"
string(1) "B"
array(2) {
  [0]=>
  bool(false)
  [1]=>
  bool(false)
}
bool(true)
bool(true)
string(1) "A"
string(1) "B"
string(4) "anon"
shutdown roots set
