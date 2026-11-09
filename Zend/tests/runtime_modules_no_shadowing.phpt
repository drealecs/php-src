--TEST--
Runtime modules: no shadowing across visible symbols
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function show_error(Closure $callback): void {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e->getMessage(), "\n";
    }
}

$a = 'phpt_shadow_a';
$b = 'phpt_shadow_b';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_230'
fn() => eval('class PhptShadowConflict {} function phpt_shadow_conflict() {} const PHPT_SHADOW_CONFLICT = 1;')
MODULE_ENTRY_230
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_231'
fn() => eval('class PhptShadowConflict {}')
MODULE_ENTRY_231
, get_defined_vars(), __DIR__);
show_error(fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_232'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule())
MODULE_ENTRY_232
, ['a' => $a], __DIR__));

$c = 'phpt_shadow_c';
$d = 'phpt_shadow_d';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$c] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_233'
fn() => eval('function phpt_shadow_func_conflict() {}')
MODULE_ENTRY_233
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$d] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_234'
fn() => eval('function phpt_shadow_func_conflict() {}')
MODULE_ENTRY_234
, get_defined_vars(), __DIR__);
show_error(fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$d] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_235'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$c] ??= new \RuntimeModule())
MODULE_ENTRY_235
, ['c' => $c], __DIR__));

$e = 'phpt_shadow_e';
$f = 'phpt_shadow_f';
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$e] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_236'
fn() => eval('const PHPT_SHADOW_CONST_CONFLICT = 1;')
MODULE_ENTRY_236
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$f] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_237'
fn() => eval('const PHPT_SHADOW_CONST_CONFLICT = 2;')
MODULE_ENTRY_237
, get_defined_vars(), __DIR__);
show_error(fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$f] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_238'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$e] ??= new \RuntimeModule())
MODULE_ENTRY_238
, ['e' => $e], __DIR__));

$g = 'phpt_shadow_global_const';
show_error(fn() => ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$g] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_239'
fn() => define('PHP_VERSION', 'shadow')
MODULE_ENTRY_239
, get_defined_vars(), __DIR__));
?>
--EXPECTF--
Cannot add dependency #%d to runtime module #%d: class name "phptshadowconflict" conflicts with runtime module #%d
Cannot add dependency #%d to runtime module #%d: function name "phpt_shadow_func_conflict" conflicts with runtime module #%d
Cannot add dependency #%d to runtime module #%d: constant name "PHPT_SHADOW_CONST_CONFLICT" conflicts with runtime module #%d
Cannot declare constant PHP_VERSION in runtime module "#%d": name conflicts with an internal/builtin symbol
