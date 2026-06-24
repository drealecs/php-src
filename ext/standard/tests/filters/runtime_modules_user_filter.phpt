--TEST--
Runtime modules: user stream filters eagerly resolve and retain their registration class
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['filter-a'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_420'
static function (): void {
    define('RuntimeModuleUserFilterOwner', "A\n");
    spl_autoload_register(static function (string $class): void {
        if ($class === 'RuntimeModuleUserFilter') {
            echo "autoload:A\n";
            eval(<<<'PHP'
                class RuntimeModuleUserFilter extends php_user_filter {
                    public function onCreate(): bool {
                        echo RuntimeModuleUserFilterOwner;
                        return true;
                    }

                    public function filter($in, $out, &$consumed, bool $closing): int {
                        while ($bucket = stream_bucket_make_writeable($in)) {
                            $consumed += $bucket->datalen;
                            stream_bucket_append($out, $bucket);
                        }
                        return PSFS_PASS_ON;
                    }
                }
                PHP);
        }
    });
    stream_filter_register('runtime-module-filter', 'RuntimeModuleUserFilter');
    echo "registered\n";
}
MODULE_ENTRY_420
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['filter-b'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_421'
static function (): void {
    define('RuntimeModuleUserFilterOwner', "B\n");
    eval(<<<'PHP'
        class RuntimeModuleUserFilter extends php_user_filter {
            public function onCreate(): bool {
                echo "B\n";
                return true;
            }

            public function filter($in, $out, &$consumed, bool $closing): int {
                return PSFS_ERR_FATAL;
            }
        }
        PHP);

    $stream = fopen('php://memory', 'w+');
    stream_filter_append($stream, 'runtime-module-filter', STREAM_FILTER_WRITE);
    fwrite($stream, 'data');
    rewind($stream);
    var_dump(stream_get_contents($stream));
}
MODULE_ENTRY_421
, get_defined_vars(), __DIR__);
?>
--EXPECT--
autoload:A
registered
A
string(4) "data"
