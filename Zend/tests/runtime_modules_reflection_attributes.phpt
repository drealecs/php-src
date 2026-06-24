--TEST--
Runtime modules: reflection attributes resolve through the owning module
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function show_module_attribute(array $attributes): void {
    echo count($attributes), ':', $attributes[0]->newInstance()->value, "\n";
}

$m = 'phpt_reflection_attributes';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_247'
fn() => eval('
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
')
MODULE_ENTRY_247
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_248'
function () {
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
}
MODULE_ENTRY_248
, get_defined_vars(), __DIR__);

$object = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_249'
fn() => new PhptAttributeTarget()
MODULE_ENTRY_249
, get_defined_vars(), __DIR__);
$class = new ReflectionClass($object);
$method = $class->getMethod('method');
$parameter = $method->getParameters()[0];
$function = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_250'
fn() => new ReflectionFunction('phpt_attribute_function')
MODULE_ENTRY_250
, get_defined_vars(), __DIR__);
$lazyObject = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_251'
fn() => new PhptLazyAttributeTarget()
MODULE_ENTRY_251
, get_defined_vars(), __DIR__);
$constant = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$m] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_252'
fn() => new ReflectionConstant('PHPT_CONST_LAZY_ATTR')
MODULE_ENTRY_252
, get_defined_vars(), __DIR__);

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
