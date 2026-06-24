--TEST--
Runtime modules: nested class templates remain reusable across contexts
--FILE--
<?php
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

module_run('phpt_nested_templates', static function (): void {
    define('PHPT_NESTED_TEMPLATE_VALUE', 'module');
});
module_run('phpt_nested_templates', phpt_nested_named_declaration(...));
var_dump(module_run('phpt_nested_templates', static fn() => PhptNestedNamedDeclaration::value()));
var_dump(module_run('phpt_nested_templates', phpt_nested_anonymous_declaration(...))->value());

$declareOuter = static function (): void {
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
};
module_run('phpt_nested_templates', $declareOuter);
var_dump(module_run('phpt_nested_templates', static fn() => PhptNestedTemplateOuter::make())->value());
module_run('phpt_nested_templates', static fn() => PhptNestedTemplateOuter::declareFunction());
var_dump(module_run('phpt_nested_templates', static fn() => phpt_nested_template_function()));

module_run('phpt_nested_trait_templates', static function (): void {
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
});
module_add_dependency('phpt_nested_trait_templates');
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
