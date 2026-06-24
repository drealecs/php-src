--TEST--
Runtime modules: no shadowing across visible symbols
--FILE--
<?php
function show_error(Closure $callback): void {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e->getMessage(), "\n";
    }
}

$a = 'phpt_shadow_a';
$b = 'phpt_shadow_b';
module_run($a, fn() => eval('class PhptShadowConflict {} function phpt_shadow_conflict() {} const PHPT_SHADOW_CONFLICT = 1;'));
module_run($b, fn() => eval('class PhptShadowConflict {}'));
show_error(fn() => module_run($b, fn() => module_add_dependency($a)));

$c = 'phpt_shadow_c';
$d = 'phpt_shadow_d';
module_run($c, fn() => eval('function phpt_shadow_func_conflict() {}'));
module_run($d, fn() => eval('function phpt_shadow_func_conflict() {}'));
show_error(fn() => module_run($d, fn() => module_add_dependency($c)));

$e = 'phpt_shadow_e';
$f = 'phpt_shadow_f';
module_run($e, fn() => eval('const PHPT_SHADOW_CONST_CONFLICT = 1;'));
module_run($f, fn() => eval('const PHPT_SHADOW_CONST_CONFLICT = 2;'));
show_error(fn() => module_run($f, fn() => module_add_dependency($e)));

$g = 'phpt_shadow_global_const';
show_error(fn() => module_run($g, fn() => define('PHP_VERSION', 'shadow')));
?>
--EXPECTF--
Cannot add dependency "phpt_shadow_a" to runtime module "phpt_shadow_b": class name "phptshadowconflict" conflicts with runtime module "phpt_shadow_b"
Cannot add dependency "phpt_shadow_c" to runtime module "phpt_shadow_d": function name "phpt_shadow_func_conflict" conflicts with runtime module "phpt_shadow_d"
Cannot add dependency "phpt_shadow_e" to runtime module "phpt_shadow_f": constant name "PHPT_SHADOW_CONST_CONFLICT" conflicts with runtime module "phpt_shadow_f"
Cannot declare constant PHP_VERSION in runtime module "phpt_shadow_global_const": name conflicts with an internal/builtin symbol
