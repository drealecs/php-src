--TEST--
Runtime modules: named functions used as closures retain their declaration's constants
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

const PHPT_FAKE_CLOSURE_CONSTANT = 'root';
class PhptFakeClosureConstantClass {
    public const VALUE = 'root';
}
function phpt_fake_closure_constants(): array {
    return [PHPT_FAKE_CLOSURE_CONSTANT, PhptFakeClosureConstantClass::VALUE];
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_fake_closure_constants'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_152'
static function (): void {
    eval(<<<'PHP'
        const PHPT_FAKE_CLOSURE_CONSTANT = 'module';
        class PhptFakeClosureConstantClass {
            public const VALUE = 'module';
        }
    PHP);
}
MODULE_ENTRY_152
, get_defined_vars(), __DIR__);

$callback = phpt_fake_closure_constants(...);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_fake_closure_constants'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_153'
$callback
MODULE_ENTRY_153
, get_defined_vars(), __DIR__));
?>
--EXPECT--
array(2) {
  [0]=>
  string(4) "root"
  [1]=>
  string(4) "root"
}
