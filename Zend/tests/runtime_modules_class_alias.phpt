--TEST--
Runtime modules: class_alias is module-local and follows visibility rules
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function phpt_alias_error(Closure $callback): void {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e->getMessage(), "\n";
    }
}

$a = 'phpt_alias_a';
$b = 'phpt_alias_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_29'
fn() => eval('class PhptAliasTarget { public static function id(): string { return "A"; } }')
MODULE_ENTRY_29
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_30'
fn() => eval('class PhptAliasTarget { public static function id(): string { return "B"; } }')
MODULE_ENTRY_30
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_31'
fn() => class_alias('PhptAliasTarget', 'PhptAliasLocal')
MODULE_ENTRY_31
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_32'
fn() => class_alias('PhptAliasTarget', 'PhptAliasLocal')
MODULE_ENTRY_32
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_33'
fn() => PhptAliasLocal::id()
MODULE_ENTRY_33
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_34'
fn() => PhptAliasLocal::id()
MODULE_ENTRY_34
, get_defined_vars(), __DIR__));
var_dump(class_exists('PhptAliasLocal', false));

$dependency = 'phpt_alias_dependency';
$main = 'phpt_alias_main';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_35'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule())
MODULE_ENTRY_35
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_36'
fn() => eval('class PhptAliasVisible {}')
MODULE_ENTRY_36
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_37'
fn() => eval('class PhptAliasMainSource {}')
MODULE_ENTRY_37
, get_defined_vars(), __DIR__);
phpt_alias_error(fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_38'
fn() => class_alias('PhptAliasMainSource', 'PhptAliasVisible')
MODULE_ENTRY_38
, get_defined_vars(), __DIR__));

$anonymousDependency = 'phpt_alias_anonymous_dependency';
$anonymousMain = 'phpt_alias_anonymous_main';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonymousDependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_39'
function (): void {
    $anonymous = new class {};
    class_alias($anonymous::class, 'PhptAliasAnonymousVisible');
}
MODULE_ENTRY_39
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonymousMain] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_40'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$anonymousDependency] ??= new \RuntimeModule())
MODULE_ENTRY_40
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonymousMain] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_41'
fn() => class_exists('PhptAliasAnonymousVisible', false)
MODULE_ENTRY_41
, get_defined_vars(), __DIR__));

$anonymousCollisionDependency = 'phpt_alias_anonymous_collision_dependency';
$anonymousCollisionMain = 'phpt_alias_anonymous_collision_main';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonymousCollisionDependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_42'
function (): void {
    $anonymous = new class {};
    class_alias($anonymous::class, 'PhptAliasAnonymousCollision');
}
MODULE_ENTRY_42
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonymousCollisionMain] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_43'
fn() => eval('class PhptAliasAnonymousCollision {}')
MODULE_ENTRY_43
, get_defined_vars(), __DIR__);
phpt_alias_error(fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$anonymousCollisionMain] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_44'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$anonymousCollisionDependency] ??= new \RuntimeModule())
MODULE_ENTRY_44
, ['anonymousCollisionDependency' => $anonymousCollisionDependency], __DIR__));
?>
--EXPECTF--
string(1) "A"
string(1) "B"
bool(false)
Cannot declare class alias phptaliasvisible in runtime module "#%d": name conflicts with runtime module "#%d"
bool(true)
Cannot add dependency #%d to runtime module #%d: class name "phptaliasanonymouscollision" conflicts with runtime module #%d
