--TEST--
Runtime modules: traits resolve through the owning module
--FILE--
<?php
$m = 'phpt_traits';

module_run($m, fn() => eval('
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
'));

var_dump(module_run($m, fn() => [
    (new PhptTraitUser())->value(),
    (new PhptTraitUser())->valueTwo(),
    (new PhptTraitAliasUser())->soloAlias(),
]));

$object = module_run($m, fn() => new PhptTraitAliasUser());
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
