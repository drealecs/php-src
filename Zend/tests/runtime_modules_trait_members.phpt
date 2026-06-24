--TEST--
Runtime modules: flattened trait members retain their lexical owner
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$dependency = 'phpt_trait_member_dependency';
$decoy = 'phpt_trait_member_decoy';
$trait = 'phpt_trait_member_owner';
$consumer = 'phpt_trait_member_consumer';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$trait] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_321'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule())
MODULE_ENTRY_321
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$consumer] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_322'
function () use ($trait, $decoy): void {
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$trait] ??= new \RuntimeModule());
    \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$decoy] ??= new \RuntimeModule());
}
MODULE_ENTRY_322
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_323'
static fn() => eval(<<<'PHP'
    #[Attribute]
    class PhptTraitMemberAttribute {
        public function __construct(public string $value) {}
    }

    enum PhptTraitMemberType {
        case Value;
    }

    class PhptTraitMemberMarker {
        public const VALUE = 'owner';
    }

    class PhptTraitMemberParent {}
PHP)
MODULE_ENTRY_323
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$decoy] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_324'
static fn() => eval(<<<'PHP'
    #[Attribute]
    class PhptTraitMemberAttribute {
        public function __construct(public string $value) {}
    }

    enum PhptTraitMemberType {
        case Value;
    }

    class PhptTraitMemberMarker {
        public const VALUE = 'decoy';
    }

    class PhptTraitMemberParent {}
PHP)
MODULE_ENTRY_324
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$trait] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_325'
static fn() => eval(<<<'PHP'
    trait PhptTraitMemberTrait {
        #[PhptTraitMemberAttribute(PhptTraitMemberMarker::VALUE)]
        public PhptTraitMemberType $typed = PhptTraitMemberType::Value;

        #[PhptTraitMemberAttribute(PhptTraitMemberMarker::VALUE)]
        public const PhptTraitMemberType VALUE = PhptTraitMemberType::Value;

        public self $selfValue;
        public parent $parentValue;
    }
PHP)
MODULE_ENTRY_325
, get_defined_vars(), __DIR__);

$reflection = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$consumer] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_326'
static function (): ReflectionClass {
    eval(<<<'PHP'
        class PhptTraitMemberConsumer extends PhptTraitMemberParent {
            use PhptTraitMemberTrait;
        }
    PHP);

    return new ReflectionClass('PhptTraitMemberConsumer');
}
MODULE_ENTRY_326
, get_defined_vars(), __DIR__);

$property = $reflection->getProperty('typed');
$default = $property->getDefaultValue();
$constantReflection = $reflection->getReflectionConstant('VALUE');
$constant = $constantReflection->getValue();
$object = $reflection->newInstance();

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_327'
static fn() => $default === PhptTraitMemberType::Value
MODULE_ENTRY_327
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$decoy] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_328'
static fn() => $default === PhptTraitMemberType::Value
MODULE_ENTRY_328
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_329'
static fn() => $constant === PhptTraitMemberType::Value
MODULE_ENTRY_329
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$decoy] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_330'
static fn() => $constant === PhptTraitMemberType::Value
MODULE_ENTRY_330
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_331'
static fn() => $object->typed === PhptTraitMemberType::Value
MODULE_ENTRY_331
, get_defined_vars(), __DIR__));

echo $property->getAttributes()[0]->newInstance()->value, "\n";
echo $constantReflection->getAttributes()[0]->newInstance()->value, "\n";

$ownerValue = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dependency] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_332'
static fn() => PhptTraitMemberType::Value
MODULE_ENTRY_332
, get_defined_vars(), __DIR__);
$decoyValue = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$decoy] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_333'
static fn() => PhptTraitMemberType::Value
MODULE_ENTRY_333
, get_defined_vars(), __DIR__);
$object->typed = $ownerValue;
try {
    $object->typed = $decoyValue;
} catch (TypeError) {
    echo "foreign type rejected\n";
}

$object->selfValue = $object;
$object->parentValue = $reflection->getParentClass()->newInstance();
echo "self/parent accepted\n";
?>
--EXPECT--
bool(true)
bool(false)
bool(true)
bool(false)
bool(true)
owner
owner
foreign type rejected
self/parent accepted
