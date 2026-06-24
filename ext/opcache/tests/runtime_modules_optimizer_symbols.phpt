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

module_run('phpt_optimizer_symbols', static function (): void {
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
});

var_dump(module_run('phpt_optimizer_symbols', phpt_optimizer_symbol_probe(...)));
var_dump(module_run('phpt_optimizer_symbols', PhptOptimizerLexicalChild::values(...)));
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
