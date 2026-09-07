--TEST--
Runtime modules: type resolution uses the owning module
--FILE--
<?php
$a = 'phpt_types_a';
$b = 'phpt_types_b';

enum PhptTypeEnum {
    case Root;
}

$code = <<<'PHP'
interface PhptTypeMarker {}

class PhptTypeThing implements PhptTypeMarker {
    public function __construct(public string $label) {}
}

class PhptTypeBox {
    public PhptTypeThing $thing;

    public function __construct(PhptTypeThing $thing) {
        $this->thing = $thing;
    }
}

enum PhptTypeEnum {
    case Value;
}

class PhptTypeEnumHolder {
    public const PhptTypeEnum VALUE = PhptTypeEnum::Value;
}

function phpt_types_accept(PhptTypeThing $x): PhptTypeThing {
    return $x;
}

function phpt_types_make(string $label): PhptTypeThing {
    return new PhptTypeThing($label);
}

function phpt_types_union(PhptTypeThing|stdClass $x): string {
    return $x instanceof PhptTypeThing ? $x->label : 'std';
}

function phpt_types_intersection(PhptTypeThing&PhptTypeMarker $x): string {
    return $x->label;
}
PHP;

module_run($a, fn() => eval($code));
module_run($b, fn() => eval($code));

$aObject = module_run($a, fn() => phpt_types_make('A'));
$bObject = module_run($b, fn() => phpt_types_make('B'));

var_dump(module_run($a, fn() => [
    phpt_types_accept($aObject)->label,
    phpt_types_make('A return')->label,
    phpt_types_union($aObject),
    phpt_types_intersection($aObject),
    PhptTypeEnumHolder::VALUE->name,
]));

var_dump(module_run($b, fn() => [
    phpt_types_accept($bObject)->label,
    phpt_types_union(new stdClass()),
]));

$box = module_run($a, fn() => new PhptTypeBox($aObject));
$box->thing = $aObject;
echo $box->thing->label, "\n";

try {
    module_run($a, fn() => phpt_types_accept($bObject));
} catch (TypeError) {
    echo "wrong parameter rejected\n";
}

try {
    $box->thing = $bObject;
} catch (TypeError) {
    echo "wrong property rejected\n";
}

$dep = 'phpt_types_dep';
$main = 'phpt_types_main';

module_run($main, fn() => module_add_dependency($dep));
module_run($dep, fn() => eval('
    class PhptTypeDependency {
        public function __construct(public string $label) {}
    }
'));
module_run($main, fn() => eval('
    function phpt_types_dep_accept(PhptTypeDependency $x): PhptTypeDependency {
        return $x;
    }
'));

$depObject = module_run($dep, fn() => new PhptTypeDependency('dep'));
var_dump(module_run($main, fn() => phpt_types_dep_accept($depObject)->label));
?>
--EXPECT--
array(5) {
  [0]=>
  string(1) "A"
  [1]=>
  string(8) "A return"
  [2]=>
  string(1) "A"
  [3]=>
  string(1) "A"
  [4]=>
  string(5) "Value"
}
array(2) {
  [0]=>
  string(1) "B"
  [1]=>
  string(3) "std"
}
A
wrong parameter rejected
wrong property rejected
string(3) "dep"
