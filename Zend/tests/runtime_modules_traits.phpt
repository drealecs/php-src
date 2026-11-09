--TEST--
Runtime modules: traits resolve through the owning module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$m = 'phpt_traits';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_361'
fn() => eval('
    trait PhptTraitOne { function value() { return "one"; } }
    trait PhptTraitTwo { function value() { return "two"; } }
    class PhptTraitUser {
        use PhptTraitOne, PhptTraitTwo {
            PhptTraitOne::value insteadof PhptTraitTwo;
            PhptTraitTwo::value as valueTwo;
        }
    }

    trait PhptTraitSolo { function solo() { return "solo"; } }
    class PhptTraitAliasUser {
        use PhptTraitSolo { solo as soloAlias; }
    }
')
MODULE_ENTRY_361
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_362'
fn() => [
    (new PhptTraitUser())->value(),
    (new PhptTraitUser())->valueTwo(),
    (new PhptTraitAliasUser())->soloAlias(),
]
MODULE_ENTRY_362
, get_defined_vars(), __DIR__));

$object = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_363'
fn() => new PhptTraitAliasUser()
MODULE_ENTRY_363
, get_defined_vars(), __DIR__);
$aliases = (new ReflectionClass($object))->getTraitAliases();
ksort($aliases);
var_dump($aliases);
?>
--EXPECT--
array(3) {
  [0]=>
  string(3) "one"
  [1]=>
  string(3) "two"
  [2]=>
  string(4) "solo"
}
array(1) {
  ["soloAlias"]=>
  string(19) "PhptTraitSolo::solo"
}
