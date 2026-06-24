--TEST--
Runtime modules: header callbacks retain their registration-time callable identity
--FILE--
<?php
module_run('phpt_mutable_header_callback', static function (): void {
    eval(<<<'PHP'
        class PhptMutableHeaderCallbackA {
            public static function run(): void {
                echo PhptMutableHeaderCallbackValue;
            }
        }
        class PhptMutableHeaderCallbackB {
            public static function run(): void {
                echo "mutable-header:B\n";
            }
        }
        const PhptMutableHeaderCallbackValue = "mutable-header:A\n";
    PHP);
});

$class = 'PhptMutableHeaderCallbackA';
$callback = [&$class, 'run'];
module_run(
    'phpt_mutable_header_callback',
    static fn() => header_register_callback($callback),
);
$class = 'PhptMutableHeaderCallbackB';
?>
--EXPECT--
mutable-header:A
