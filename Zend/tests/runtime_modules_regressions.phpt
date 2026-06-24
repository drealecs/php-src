--TEST--
Runtime modules: context and cache regressions
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function phpt_regression_global_probe(): bool {
    return class_exists('PhptRegressionContextThing', false);
}

$context = 'phpt_regression_context';
$globalProbe = phpt_regression_global_probe(...);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$context] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_258'
fn() => eval('class PhptRegressionContextThing {} function phpt_regression_module_caller(Closure $callback): bool { return $callback(); }')
MODULE_ENTRY_258
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$context] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_259'
fn() => phpt_regression_module_caller($globalProbe)
MODULE_ENTRY_259
, get_defined_vars(), __DIR__));
var_dump(class_exists('PhptRegressionContextThing', false));

$ceCache = 'phpt_regression_ce_cache';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$ceCache] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_260'
fn() => eval('class PhptRegressionCECache { public const X = 1; }')
MODULE_ENTRY_260
, get_defined_vars(), __DIR__);
var_dump(class_exists('PhptRegressionCECache', false));
var_dump(defined('PhptRegressionCECache::X'));

$staticA = 'phpt_regression_static_a';
$staticB = 'phpt_regression_static_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$staticA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_261'
fn() => eval('class PhptRegressionStaticThing { public static int $value = 1; }')
MODULE_ENTRY_261
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$staticB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_262'
fn() => eval('class PhptRegressionStaticThing { public static int $value = 2; }')
MODULE_ENTRY_262
, get_defined_vars(), __DIR__);
var_dump([($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$staticA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_263'
fn() => PhptRegressionStaticThing::$value
MODULE_ENTRY_263
, get_defined_vars(), __DIR__), ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$staticB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_264'
fn() => PhptRegressionStaticThing::$value
MODULE_ENTRY_264
, get_defined_vars(), __DIR__)]);

$nested = 'phpt_regression_nested';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$nested] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_265'
function (): void {
    function phpt_regression_nested_function(): string {
        return 'module';
    }
}
MODULE_ENTRY_265
, get_defined_vars(), __DIR__);
var_dump(function_exists('phpt_regression_nested_function'));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$nested] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_266'
fn() => phpt_regression_nested_function()
MODULE_ENTRY_266
, get_defined_vars(), __DIR__));

$closureModule = 'phpt_regression_returned_closure';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$closureModule] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_267'
fn() => eval('class PhptRegressionReturnedClosureThing {}')
MODULE_ENTRY_267
, get_defined_vars(), __DIR__);
$returnedClosure = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$closureModule] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_268'
fn() => fn() => class_exists('PhptRegressionReturnedClosureThing', false)
MODULE_ENTRY_268
, get_defined_vars(), __DIR__);
var_dump($returnedClosure());

$nestedA = 'phpt_regression_nested_a';
$nestedB = 'phpt_regression_nested_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$nestedA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_269'
fn() => eval('const PHPT_REGRESSION_NESTED_VALUE = "A";')
MODULE_ENTRY_269
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$nestedB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_270'
fn() => eval('const PHPT_REGRESSION_NESTED_VALUE = "B";')
MODULE_ENTRY_270
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$nestedA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_271'
function (): void {
    function phpt_regression_nested_duplicate(): string {
        return PHPT_REGRESSION_NESTED_VALUE;
    }
}
MODULE_ENTRY_271
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$nestedB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_272'
function (): void {
    function phpt_regression_nested_duplicate(): string {
        return PHPT_REGRESSION_NESTED_VALUE;
    }
}
MODULE_ENTRY_272
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$nestedA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_273'
fn() => phpt_regression_nested_duplicate()
MODULE_ENTRY_273
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$nestedB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_274'
fn() => phpt_regression_nested_duplicate()
MODULE_ENTRY_274
, get_defined_vars(), __DIR__));

$isAA = 'phpt_regression_is_a_a';
$isAB = 'phpt_regression_is_a_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$isAA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_275'
fn() => eval('class PhptRegressionIsAThing {}')
MODULE_ENTRY_275
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$isAB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_276'
fn() => eval('class PhptRegressionIsAThing {}')
MODULE_ENTRY_276
, get_defined_vars(), __DIR__);
$isAObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$isAA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_277'
fn() => new PhptRegressionIsAThing()
MODULE_ENTRY_277
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$isAB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_278'
fn() => [$isAObject instanceof PhptRegressionIsAThing, is_a($isAObject, 'PhptRegressionIsAThing')]
MODULE_ENTRY_278
, get_defined_vars(), __DIR__));

$categorizedConstants = 'phpt_regression_categorized_constants';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$categorizedConstants] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_279'
fn() => eval('const PHPT_REGRESSION_CATEGORIZED_CONSTANT = 1;')
MODULE_ENTRY_279
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$categorizedConstants] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_280'
fn() => array_key_exists('PHPT_REGRESSION_CATEGORIZED_CONSTANT', get_defined_constants(true)['user'] ?? [])
MODULE_ENTRY_280
, get_defined_vars(), __DIR__));

$autoloadA = 'phpt_regression_autoload_a';
$autoloadB = 'phpt_regression_autoload_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$autoloadB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_281'
function (): void {
    spl_autoload_register(function (string $class): void {
        if ($class === 'PhptRegressionAutoloadGuard') {
            eval('class PhptRegressionAutoloadGuard { public static function id(): string { return "B"; } }');
        }
    });
}
MODULE_ENTRY_281
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$autoloadA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_283'
function () use ($autoloadB): void {
    spl_autoload_register(function (string $class) use ($autoloadB): void {
        if ($class === 'PhptRegressionAutoloadGuard') {
            var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$autoloadB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_282'
fn() => class_exists('PhptRegressionAutoloadGuard')
MODULE_ENTRY_282
, get_defined_vars(), __DIR__));
            eval('class PhptRegressionAutoloadGuard { public static function id(): string { return "A"; } }');
        }
    });
}
MODULE_ENTRY_283
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$autoloadA] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_284'
fn() => PhptRegressionAutoloadGuard::id()
MODULE_ENTRY_284
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$autoloadB] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_285'
fn() => PhptRegressionAutoloadGuard::id()
MODULE_ENTRY_285
, get_defined_vars(), __DIR__));

$anonymous = 'phpt_regression_anonymous';
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonymous] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_286'
fn() => eval('return new class { public function id(): string { return "anon"; } };')
MODULE_ENTRY_286
, get_defined_vars(), __DIR__)->id());

$shutdown = 'phpt_regression_shutdown';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$shutdown] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_287'
function (): void {
    spl_autoload_register(function (string $class): void {});
    eval('class PhptRegressionShutdown { public static object $object; } PhptRegressionShutdown::$object = new stdClass();');
}
MODULE_ENTRY_287
, get_defined_vars(), __DIR__);
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
