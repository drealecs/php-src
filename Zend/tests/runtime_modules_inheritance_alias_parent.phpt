--TEST--
Runtime modules: parent variance distinguishes an imported alias from a same-named local class
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['alias_parent_bridge'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_176'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['alias_parent_owner'] ??= new \RuntimeModule())
MODULE_ENTRY_176
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['alias_parent_consumer'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_177'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['alias_parent_bridge'] ??= new \RuntimeModule())
MODULE_ENTRY_177
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['alias_parent_owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_178'
static fn() => eval('class PhptAliasParentType {}')
MODULE_ENTRY_178
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['alias_parent_bridge'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_179'
static function (): void {
    class_alias('PhptAliasParentType', 'PhptAliasParentBase');
}
MODULE_ENTRY_179
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['alias_parent_consumer'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_180'
static fn() => eval(<<<'PHP'
    class PhptAliasParentType {}
    interface PhptAliasParentContract {
        public function value(): PhptAliasParentType;
    }
    trait PhptAliasParentTrait {
        public function value(): parent {
            return new parent();
        }
    }
    class PhptAliasParentConsumer extends PhptAliasParentBase implements PhptAliasParentContract {
        use PhptAliasParentTrait;
    }
PHP)
MODULE_ENTRY_180
, get_defined_vars(), __DIR__);
echo "unexpectedly linked\n";
?>
--EXPECTF--
Fatal error: Declaration of PhptAliasParentConsumer::value(): PhptAliasParentType must be compatible with PhptAliasParentContract::value(): PhptAliasParentType in %s on line %d
