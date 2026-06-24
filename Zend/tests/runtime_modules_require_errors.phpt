--TEST--
Runtime modules: file entry errors restore context and do not prevent subsequent execution
--FILE--
<?php
$root = RuntimeModule::current();
$module = new RuntimeModule();
foreach (['', "invalid\0path", [], __DIR__ . '/runtime_modules/missing-entry.inc'] as $path) {
    try {
        @$module->require($path);
    } catch (Throwable $error) {
        echo $error::class, "\n";
    }
    var_dump(RuntimeModule::current() === $root);
}
$file = __DIR__ . '/runtime_modules_require_errors.inc';
file_put_contents($file, '<?php invalid syntax here;');
try {
    $module->require($file);
} catch (ParseError) {
    echo "parse error\n";
} finally {
    unlink($file);
}
var_dump(RuntimeModule::current() === $root);
var_dump($module->require(__DIR__ . '/runtime_modules/entry_context.inc')[0] === $module);
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/runtime_modules_require_errors.inc');
?>
--EXPECT--
ValueError
bool(true)
ValueError
bool(true)
TypeError
bool(true)
Error
bool(true)
parse error
bool(true)
bool(true)
