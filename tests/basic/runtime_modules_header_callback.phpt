--TEST--
Runtime modules: header callbacks retain their registration-time callable identity
--FILE--
<?php
foreach (['phpt_header_callback_a' => 'A', 'phpt_header_callback_b' => 'B'] as $module => $value) {
    module_run($module, static fn() => eval(<<<PHP
        define('PHPT_HEADER_CALLBACK_VALUE', '$value');
        class PhptHeaderCallback {
            public static function __callStatic(string \$name, array \$arguments): void {
                echo 'header:', PHPT_HEADER_CALLBACK_VALUE, "\n";
            }
        }
    PHP));
}

module_run('phpt_header_callback_a', static fn() => header_register_callback([
    'PhptHeaderCallback',
    'missing',
]));
?>
--EXPECT--
header:A
