--TEST--
Runtime modules: user stream filters eagerly resolve and retain their registration class
--FILE--
<?php
module_run('filter-a', static function (): void {
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
});

module_run('filter-b', static function (): void {
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
});
?>
--EXPECT--
autoload:A
registered
A
string(4) "data"
