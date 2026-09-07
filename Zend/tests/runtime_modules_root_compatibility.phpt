--TEST--
Runtime modules: root context preserves legacy symbol lookup behavior
--EXTENSIONS--
spl
--FILE--
<?php
module_run('phpt_root_compatibility', fn() => null);

class PhptRootCompatibilityParent {}
class PhptRootCompatibilityChild extends PhptRootCompatibilityParent {}

interface PhptRootCompatibilityFactory {
    public function make(): PhptRootCompatibilityParent;
}

class PhptRootCompatibilityFactoryImpl implements PhptRootCompatibilityFactory {
    public function make(): PhptRootCompatibilityChild {
        return new PhptRootCompatibilityChild();
    }
}

echo (new PhptRootCompatibilityFactoryImpl())->make()::class, "\n";

define('', 'empty constant');
var_dump(constant(''));

module_run('phpt_arbitrary_constant_owner', function (): void {
    define('', 'module-empty');
    define("\0module-constant", 'module-nul');
});
module_run('phpt_arbitrary_constant_dependent', fn() => module_add_dependency('phpt_arbitrary_constant_owner'));
foreach (['phpt_arbitrary_constant_owner', 'phpt_arbitrary_constant_dependent'] as $module) {
    var_dump(module_run($module, fn() => constant('')));
    var_dump(module_run($module, fn() => constant("\0module-constant")));
}

$anonymous = new class {};
class_alias($anonymous::class, 'PhptRootCompatibilityAlias');
var_dump(class_exists('PhptRootCompatibilityAlias', false));

var_dump(class_parents('\\PhptRootCompatibilityChild', false));
?>
--EXPECT--
PhptRootCompatibilityChild
string(14) "empty constant"
string(12) "module-empty"
string(10) "module-nul"
string(12) "module-empty"
string(10) "module-nul"
bool(true)
array(1) {
  ["PhptRootCompatibilityParent"]=>
  string(27) "PhptRootCompatibilityParent"
}
