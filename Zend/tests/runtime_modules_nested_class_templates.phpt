--TEST--
Runtime modules: independently compiled nested declarations retain their owner
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

const PHPT_NESTED_TEMPLATE_VALUE = 'root';

function phpt_nested_named_declaration(): void {
    class PhptNestedNamedDeclaration {
        public static function value(): string {
            return PHPT_NESTED_TEMPLATE_VALUE;
        }
    }
}

function phpt_nested_anonymous_declaration(): object {
    return new class {
        public function value(): string {
            return PHPT_NESTED_TEMPLATE_VALUE;
        }
    };
}

phpt_nested_named_declaration();
var_dump(PhptNestedNamedDeclaration::value());
var_dump(phpt_nested_anonymous_declaration()->value());

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_nested_templates'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_221'
static function (): void {
    define('PHPT_NESTED_TEMPLATE_VALUE', 'module');
}
MODULE_ENTRY_221
, get_defined_vars(), __DIR__);
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_nested_templates'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_222'
function(): void {
    class PhptNestedNamedDeclaration {
        public static function value(): string {
            return PHPT_NESTED_TEMPLATE_VALUE;
        }
    }
}
MODULE_ENTRY_222
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_nested_templates'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_223'
static fn() => PhptNestedNamedDeclaration::value()
MODULE_ENTRY_223
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_nested_templates'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_224'
function(): object {
    return new class {
        public function value(): string {
            return PHPT_NESTED_TEMPLATE_VALUE;
        }
    };
}
MODULE_ENTRY_224
, get_defined_vars(), __DIR__)->value());

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_nested_templates'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_225'
static function (): void {
    class PhptNestedTemplateOuter {
        public static function make(): object {
            return new class {
                public function value(): string {
                    return PHPT_NESTED_TEMPLATE_VALUE;
                }
            };
        }

        public static function declareFunction(): void {
            function phpt_nested_template_function(): string {
                return PHPT_NESTED_TEMPLATE_VALUE;
            }
        }
    }
}
MODULE_ENTRY_225
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_nested_templates'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_226'
static fn() => PhptNestedTemplateOuter::make()
MODULE_ENTRY_226
, get_defined_vars(), __DIR__)->value());
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_nested_templates'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_227'
static fn() => PhptNestedTemplateOuter::declareFunction()
MODULE_ENTRY_227
, get_defined_vars(), __DIR__);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_nested_templates'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_228'
static fn() => phpt_nested_template_function()
MODULE_ENTRY_228
, get_defined_vars(), __DIR__));

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_nested_trait_templates'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_229'
static function (): void {
    eval(<<<'PHP'
        trait PhptNestedTemplateTrait {
            public static Closure $factory = static function (): object {
                return new class {
                    public function value(): string {
                        return 'trait-root';
                    }
                };
            };
        }
    PHP);
}
MODULE_ENTRY_229
, get_defined_vars(), __DIR__);
\RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt_nested_trait_templates'] ??= new \RuntimeModule());
class PhptNestedTemplateTraitConsumer {
    use PhptNestedTemplateTrait;
}
var_dump((PhptNestedTemplateTraitConsumer::$factory)()->value());
?>
--EXPECT--
string(4) "root"
string(4) "root"
string(6) "module"
string(6) "module"
string(6) "module"
string(6) "module"
string(10) "trait-root"
