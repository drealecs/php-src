--TEST--
Runtime modules: optimizer preserves context-sensitive user symbols and lexical class names
--INI--
opcache.enable=1
opcache.enable_cli=1
opcache.file_update_protection=0
opcache.optimization_level=0x7FFEBFFF
opcache.jit=disable
--EXTENSIONS--
opcache
--FILE--
<?php
require_once __DIR__ . '/../../../Zend/tests/runtime_modules/file_fixture.inc';

const PHPT_OPTIMIZER_EXPLICIT_CONSTANT = 'root';

class PhptOptimizerExplicitClass {
    public const VALUE = 'root class';

    public function value(): string {
        return self::VALUE;
    }
}

class PhptOptimizerLexicalParent {
    public const VALUE = 'root parent';
}

final class PhptOptimizerLexicalChild extends PhptOptimizerLexicalParent {
    public const VALUE = 'root self';

    public static function values(): array {
        return [self::VALUE, parent::VALUE, static::VALUE];
    }
}

function phpt_optimizer_symbol_probe(): array {
    $object = new PhptOptimizerExplicitClass();
    return [
        PHPT_OPTIMIZER_EXPLICIT_CONSTANT,
        PhptOptimizerExplicitClass::VALUE,
        $object->value(),
        PHP_VERSION_ID > 0,
    ];
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_optimizer_symbols'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_399'
static function (): void {
    eval(<<<'PHP'
        const PHPT_OPTIMIZER_EXPLICIT_CONSTANT = 'module';

        class PhptOptimizerExplicitClass {
            public const VALUE = 'module class';

            public function value(): string {
                return self::VALUE;
            }
        }

        class PhptOptimizerLexicalParent {
            public const VALUE = 'module parent';
        }

        final class PhptOptimizerLexicalChild extends PhptOptimizerLexicalParent {
            public const VALUE = 'module self';
        }
    PHP);
}
MODULE_ENTRY_399
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_optimizer_symbols'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_400'
function(): array {
    $object = new PhptOptimizerExplicitClass();
    return [
        PHPT_OPTIMIZER_EXPLICIT_CONSTANT,
        PhptOptimizerExplicitClass::VALUE,
        $object->value(),
        PHP_VERSION_ID > 0,
    ];
}
MODULE_ENTRY_400
, get_defined_vars(), __DIR__));
$lexical = PhptOptimizerLexicalChild::values(...);
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_optimizer_symbols'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_401'
$lexical
MODULE_ENTRY_401
, get_defined_vars(), __DIR__));
?>
--EXPECT--
array(4) {
  [0]=>
  string(6) "module"
  [1]=>
  string(12) "module class"
  [2]=>
  string(12) "module class"
  [3]=>
  bool(true)
}
array(3) {
  [0]=>
  string(9) "root self"
  [1]=>
  string(11) "root parent"
  [2]=>
  string(9) "root self"
}
