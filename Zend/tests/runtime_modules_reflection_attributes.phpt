--TEST--
Runtime modules: reflection attributes resolve through the owning module
--FILE--
<?php
function show_module_attribute(array $attributes): void {
    echo count($attributes), ':', $attributes[0]->newInstance()->value, "\n";
}

$m = 'phpt_reflection_attributes';

module_run($m, fn() => eval('
    #[Attribute]
    class PhptAttributeBase {}

    #[Attribute]
    class PhptAttributeChild extends PhptAttributeBase {
        public function __construct(public string $value) {}
    }

    #[PhptAttributeChild("class")]
    class PhptAttributeTarget {
        #[PhptAttributeChild("constant")]
        public const MARKER = 1;

        #[PhptAttributeChild("property")]
        public string $property;

        #[PhptAttributeChild("method")]
        public function method(#[PhptAttributeChild("parameter")] string $parameter): void {}
    }

    #[PhptAttributeChild("function")]
    function phpt_attribute_function(): void {}
'));

module_run($m, function () {
    spl_autoload_register(function (string $class): void {
        if ($class === 'PhptLazyAttributeBase' || $class === 'PhptLazyAttributeChild') {
            eval('
                #[Attribute]
                class PhptLazyAttributeBase {}

                #[Attribute]
                class PhptLazyAttributeChild extends PhptLazyAttributeBase {
                    public function __construct(public string $value) {}
                }
            ');
        } elseif ($class === 'PhptConstLazyAttributeBase' || $class === 'PhptConstLazyAttributeChild') {
            eval('
                #[Attribute]
                class PhptConstLazyAttributeBase {}

                #[Attribute]
                class PhptConstLazyAttributeChild extends PhptConstLazyAttributeBase {
                    public function __construct(public string $value) {}
                }
            ');
        }
    });

    eval('#[PhptLazyAttributeChild("lazy")] class PhptLazyAttributeTarget {}');
    eval('#[PhptConstLazyAttributeChild("global constant")] const PHPT_CONST_LAZY_ATTR = 1;');
});

$object = module_run($m, fn() => new PhptAttributeTarget());
$class = new ReflectionClass($object);
$method = $class->getMethod('method');
$parameter = $method->getParameters()[0];
$function = module_run($m, fn() => new ReflectionFunction('phpt_attribute_function'));
$lazyObject = module_run($m, fn() => new PhptLazyAttributeTarget());
$constant = module_run($m, fn() => new ReflectionConstant('PHPT_CONST_LAZY_ATTR'));

show_module_attribute($class->getAttributes('PhptAttributeBase', ReflectionAttribute::IS_INSTANCEOF));
show_module_attribute($class->getReflectionConstant('MARKER')->getAttributes('PhptAttributeBase', ReflectionAttribute::IS_INSTANCEOF));
show_module_attribute($class->getProperty('property')->getAttributes('PhptAttributeBase', ReflectionAttribute::IS_INSTANCEOF));
show_module_attribute($method->getAttributes('PhptAttributeBase', ReflectionAttribute::IS_INSTANCEOF));
show_module_attribute($parameter->getAttributes('PhptAttributeBase', ReflectionAttribute::IS_INSTANCEOF));
show_module_attribute($function->getAttributes('PhptAttributeBase', ReflectionAttribute::IS_INSTANCEOF));
show_module_attribute((new ReflectionClass($lazyObject))->getAttributes('PhptLazyAttributeBase', ReflectionAttribute::IS_INSTANCEOF));
show_module_attribute($constant->getAttributes('PhptConstLazyAttributeBase', ReflectionAttribute::IS_INSTANCEOF));
?>
--EXPECT--
1:class
1:constant
1:property
1:method
1:parameter
1:function
1:lazy
1:global constant
