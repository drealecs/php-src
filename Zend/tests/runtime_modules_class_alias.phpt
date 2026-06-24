--TEST--
Runtime modules: class_alias is module-local and follows visibility rules
--FILE--
<?php
function phpt_alias_error(Closure $callback): void {
    try {
        $callback();
    } catch (Throwable $e) {
        echo $e->getMessage(), "\n";
    }
}

$a = 'phpt_alias_a';
$b = 'phpt_alias_b';
module_run($a, fn() => eval('class PhptAliasTarget { public static function id(): string { return "A"; } }'));
module_run($b, fn() => eval('class PhptAliasTarget { public static function id(): string { return "B"; } }'));

module_run($a, fn() => class_alias('PhptAliasTarget', 'PhptAliasLocal'));
module_run($b, fn() => class_alias('PhptAliasTarget', 'PhptAliasLocal'));

$probe = fn() => PhptAliasLocal::id();
var_dump(module_run($a, $probe));
var_dump(module_run($b, $probe));
var_dump(class_exists('PhptAliasLocal', false));

$dependency = 'phpt_alias_dependency';
$main = 'phpt_alias_main';
module_run($dependency, fn() => eval('class PhptAliasVisible {}'));
module_run($main, fn() => eval('class PhptAliasMainSource {}'));
module_run($main, fn() => module_add_dependency($dependency));
phpt_alias_error(fn() => module_run($main, fn() => class_alias('PhptAliasMainSource', 'PhptAliasVisible')));
?>
--EXPECTF--
string(1) "A"
string(1) "B"
bool(false)
Cannot declare class alias phptaliasvisible in runtime module "phpt_alias_main": name conflicts with runtime module "phpt_alias_dependency"
