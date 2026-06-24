--TEST--
Runtime modules: root context preserves legacy symbol lookup behavior
--EXTENSIONS--
spl
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_root_compatibility'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_291'
fn() => null
MODULE_ENTRY_291
, get_defined_vars(), __DIR__);

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

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_arbitrary_constant_owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_292'
function (): void {
    define('', 'module-empty');
    define("\0module-constant", 'module-nul');
}
MODULE_ENTRY_292
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_arbitrary_constant_dependent'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_293'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt_arbitrary_constant_owner'] ??= new \RuntimeModule())
MODULE_ENTRY_293
, get_defined_vars(), __DIR__);
foreach (['phpt_arbitrary_constant_owner', 'phpt_arbitrary_constant_dependent'] as $module) {
    var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_294'
fn() => constant('')
MODULE_ENTRY_294
, get_defined_vars(), __DIR__));
    var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$module] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_295'
fn() => constant("\0module-constant")
MODULE_ENTRY_295
, get_defined_vars(), __DIR__));
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
