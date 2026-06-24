--TEST--
Runtime modules: type resolution uses the owning module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

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

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_364'
fn() => eval($code)
MODULE_ENTRY_364
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_365'
fn() => eval($code)
MODULE_ENTRY_365
, get_defined_vars(), __DIR__);

$aObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_366'
fn() => phpt_types_make('A')
MODULE_ENTRY_366
, get_defined_vars(), __DIR__);
$bObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_367'
fn() => phpt_types_make('B')
MODULE_ENTRY_367
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_368'
fn() => [
    phpt_types_accept($aObject)->label,
    phpt_types_make('A return')->label,
    phpt_types_union($aObject),
    phpt_types_intersection($aObject),
    PhptTypeEnumHolder::VALUE->name,
]
MODULE_ENTRY_368
, get_defined_vars(), __DIR__));

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_369'
fn() => [
    phpt_types_accept($bObject)->label,
    phpt_types_union(new stdClass()),
]
MODULE_ENTRY_369
, get_defined_vars(), __DIR__));

$box = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_370'
fn() => new PhptTypeBox($aObject)
MODULE_ENTRY_370
, get_defined_vars(), __DIR__);
$box->thing = $aObject;
echo $box->thing->label, "\n";

try {
    ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_371'
fn() => phpt_types_accept($bObject)
MODULE_ENTRY_371
, get_defined_vars(), __DIR__);
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

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_372'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule())
MODULE_ENTRY_372
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_373'
fn() => eval('
    class PhptTypeDependency {
        public function __construct(public string $label) {}
    }
')
MODULE_ENTRY_373
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_374'
fn() => eval('
    function phpt_types_dep_accept(PhptTypeDependency $x): PhptTypeDependency {
        return $x;
    }
')
MODULE_ENTRY_374
, get_defined_vars(), __DIR__);

$depObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_375'
fn() => new PhptTypeDependency('dep')
MODULE_ENTRY_375
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$main] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_376'
fn() => phpt_types_dep_accept($depObject)->label
MODULE_ENTRY_376
, get_defined_vars(), __DIR__));
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
