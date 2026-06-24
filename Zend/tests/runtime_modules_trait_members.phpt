--TEST--
Runtime modules: flattened trait members retain their lexical owner
--FILE--
<?php
$dependency = 'phpt_trait_member_dependency';
$decoy = 'phpt_trait_member_decoy';
$trait = 'phpt_trait_member_owner';
$consumer = 'phpt_trait_member_consumer';

module_run($trait, fn() => module_add_dependency($dependency));
module_run($consumer, function () use ($trait, $decoy): void {
    module_add_dependency($trait);
    module_add_dependency($decoy);
});

module_run($dependency, static fn() => eval(<<<'PHP'
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
PHP));

module_run($decoy, static fn() => eval(<<<'PHP'
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
PHP));

module_run($trait, static fn() => eval(<<<'PHP'
    trait PhptTraitMemberTrait {
        #[PhptTraitMemberAttribute(PhptTraitMemberMarker::VALUE)]
        public PhptTraitMemberType $typed = PhptTraitMemberType::Value;

        #[PhptTraitMemberAttribute(PhptTraitMemberMarker::VALUE)]
        public const PhptTraitMemberType VALUE = PhptTraitMemberType::Value;

        public self $selfValue;
        public parent $parentValue;
    }
PHP));

$reflection = module_run($consumer, static function (): ReflectionClass {
    eval(<<<'PHP'
        class PhptTraitMemberConsumer extends PhptTraitMemberParent {
            use PhptTraitMemberTrait;
        }
    PHP);

    return new ReflectionClass('PhptTraitMemberConsumer');
});

$property = $reflection->getProperty('typed');
$default = $property->getDefaultValue();
$constantReflection = $reflection->getReflectionConstant('VALUE');
$constant = $constantReflection->getValue();
$object = $reflection->newInstance();

var_dump(module_run($dependency, static fn() => $default === PhptTraitMemberType::Value));
var_dump(module_run($decoy, static fn() => $default === PhptTraitMemberType::Value));
var_dump(module_run($dependency, static fn() => $constant === PhptTraitMemberType::Value));
var_dump(module_run($decoy, static fn() => $constant === PhptTraitMemberType::Value));
var_dump(module_run($dependency, static fn() => $object->typed === PhptTraitMemberType::Value));

echo $property->getAttributes()[0]->newInstance()->value, "\n";
echo $constantReflection->getAttributes()[0]->newInstance()->value, "\n";

$ownerValue = module_run($dependency, static fn() => PhptTraitMemberType::Value);
$decoyValue = module_run($decoy, static fn() => PhptTraitMemberType::Value);
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
