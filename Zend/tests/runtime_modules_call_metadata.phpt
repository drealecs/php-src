--TEST--
Runtime modules: calls use metadata from the runtime-resolved symbol
--FILE--
<?php
function phpt_call_metadata_function($value): void {}
function phpt_call_metadata_frame(): int { return 0; }

class PhptCallMetadataStatic {
    public static function mutate($value): void {}
}

class PhptCallMetadataConstructor {
    public function __construct($value) {}
}

$functionProbe = function (): string {
    $value = 'before';
    phpt_call_metadata_function($value);
    return $value;
};
$frameProbe = fn() => phpt_call_metadata_frame();
$staticProbe = function (): string {
    $value = 'before';
    PhptCallMetadataStatic::mutate($value);
    return $value;
};
$constructorProbe = function (): string {
    $value = 'before';
    new PhptCallMetadataConstructor($value);
    return $value;
};

module_run('phpt_call_metadata', static function (): void {
    eval(<<<'PHP'
        function phpt_call_metadata_function(&$value): void {
            $value = 'function';
        }
        function phpt_call_metadata_frame(): int {
            $v0 = new stdClass;
            $v1 = new stdClass;
            $v2 = new stdClass;
            $v3 = new stdClass;
            $v4 = new stdClass;
            $v5 = new stdClass;
            $v6 = new stdClass;
            $v7 = new stdClass;
            return count(get_defined_vars());
        }
        class PhptCallMetadataStatic {
            public static function mutate(&$value): void {
                $value = 'static';
            }
        }
        class PhptCallMetadataConstructor {
            public function __construct(&$value) {
                $value = 'constructor';
            }
        }
    PHP);
});

var_dump(module_run('phpt_call_metadata', $functionProbe));
var_dump(module_run('phpt_call_metadata', $frameProbe));
var_dump(module_run('phpt_call_metadata', $staticProbe));
var_dump(module_run('phpt_call_metadata', $constructorProbe));

module_run('phpt_call_metadata_top_level', static function (): void {
    eval(<<<'PHP'
        class PhptCallMetadataStatic {
            public static function mutate(&$value): void {
                $value = 'static';
            }
        }
        class PhptCallMetadataConstructor {
            public function __construct(&$value) {
                $value = 'constructor';
            }
        }

        $value = 'before';
        PhptCallMetadataStatic::mutate($value);
        var_dump($value);

        $value = 'before';
        new PhptCallMetadataConstructor($value);
        var_dump($value);
    PHP);
});
?>
--EXPECT--
string(8) "function"
int(8)
string(6) "static"
string(11) "constructor"
string(6) "static"
string(11) "constructor"
