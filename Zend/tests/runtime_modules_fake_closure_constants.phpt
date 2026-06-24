--TEST--
Runtime modules: named functions used as closures retain dynamic constant lookup
--FILE--
<?php
const PHPT_FAKE_CLOSURE_CONSTANT = 'root';
class PhptFakeClosureConstantClass {
    public const VALUE = 'root';
}
function phpt_fake_closure_constants(): array {
    return [PHPT_FAKE_CLOSURE_CONSTANT, PhptFakeClosureConstantClass::VALUE];
}

module_run('phpt_fake_closure_constants', static function (): void {
    eval(<<<'PHP'
        const PHPT_FAKE_CLOSURE_CONSTANT = 'module';
        class PhptFakeClosureConstantClass {
            public const VALUE = 'module';
        }
    PHP);
});

var_dump(module_run('phpt_fake_closure_constants', phpt_fake_closure_constants(...)));
?>
--EXPECT--
array(2) {
  [0]=>
  string(6) "module"
  [1]=>
  string(6) "module"
}
