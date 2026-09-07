--TEST--
Runtime modules: named calls map arguments using the runtime-resolved symbol
--FILE--
<?php
function phpt_named_call_metadata($root): string {
    return 'root';
}

$probe = fn() => phpt_named_call_metadata(root: 'value');

module_run('phpt_named_call_metadata', static function (): void {
    eval(<<<'PHP'
        function phpt_named_call_metadata($module): string {
            return 'module';
        }
    PHP);
});

try {
    module_run('phpt_named_call_metadata', $probe);
} catch (Error $e) {
    echo $e->getMessage(), "\n";
}
?>
--EXPECT--
Unknown named parameter $root
