--TEST--
Runtime modules: calls use metadata from the runtime-resolved symbol
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

function phpt_call_metadata_function($value): void {}
function phpt_call_metadata_frame(): int { return 0; }

class PhptCallMetadataStatic {
    public static function mutate($value): void {}
}

class PhptCallMetadataConstructor {
    public function __construct($value) {}
}

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_call_metadata'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_15'
static function (): void {
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
}
MODULE_ENTRY_15
, get_defined_vars(), __DIR__);

var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_call_metadata'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_16'
function (): string {
    $value = 'before';
    phpt_call_metadata_function($value);
    return $value;
}
MODULE_ENTRY_16
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_call_metadata'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_17'
fn() => phpt_call_metadata_frame()
MODULE_ENTRY_17
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_call_metadata'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_18'
function (): string {
    $value = 'before';
    PhptCallMetadataStatic::mutate($value);
    return $value;
}
MODULE_ENTRY_18
, get_defined_vars(), __DIR__));
var_dump(($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_call_metadata'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_19'
function (): string {
    $value = 'before';
    new PhptCallMetadataConstructor($value);
    return $value;
}
MODULE_ENTRY_19
, get_defined_vars(), __DIR__));

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_call_metadata_top_level'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_20'
static function (): void {
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
}
MODULE_ENTRY_20
, get_defined_vars(), __DIR__);
?>
--EXPECT--
string(8) "function"
int(8)
string(6) "static"
string(11) "constructor"
string(6) "static"
string(11) "constructor"
