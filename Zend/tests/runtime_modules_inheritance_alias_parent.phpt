--TEST--
Runtime modules: parent variance distinguishes an imported alias from a same-named local class
--FILE--
<?php
module_run('alias_parent_bridge', fn() => module_add_dependency('alias_parent_owner'));
module_run('alias_parent_consumer', fn() => module_add_dependency('alias_parent_bridge'));

module_run('alias_parent_owner', static fn() => eval('class PhptAliasParentType {}'));
module_run('alias_parent_bridge', static function (): void {
    class_alias('PhptAliasParentType', 'PhptAliasParentBase');
});

module_run('alias_parent_consumer', static fn() => eval(<<<'PHP'
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
PHP));
echo "unexpectedly linked\n";
?>
--EXPECTF--
Fatal error: Declaration of PhptAliasParentConsumer::value(): PhptAliasParentType must be compatible with PhptAliasParentContract::value(): PhptAliasParentType in %s on line %d
