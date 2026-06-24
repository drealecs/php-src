--TEST--
Runtime modules: output callbacks preserve internal registration context and user ownership
--FILE--
<?php
require_once __DIR__ . '/../../Zend/tests/runtime_modules/file_fixture.inc';

class RootOutputClass {}
const OUTPUT_LABEL = 'root';

$moduleCallback = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['output-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_452'
static function (): Closure {
    eval('class ModuleOutputClass {}');
    define('OUTPUT_LABEL', 'module');
    ob_start('class_exists');
    return static fn(string $buffer, int $phase): string => OUTPUT_LABEL . ':' . $buffer . "\n";
}
MODULE_ENTRY_452
, get_defined_vars(), __DIR__);
echo 'ModuleOutputClass';
ob_end_flush();
echo "module registration retained\n";

ob_start('class_exists');
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['output-trigger'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_453'
static function (): void {
    echo 'RootOutputClass';
    ob_end_flush();
}
MODULE_ENTRY_453
, get_defined_vars(), __DIR__);
echo "root registration retained\n";

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['output-trigger'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_454'
static fn() => ob_start($moduleCallback)
MODULE_ENTRY_454
, get_defined_vars(), __DIR__);
echo 'user callback';
ob_end_flush();

$rootCallback = static fn(string $buffer, int $phase): string => OUTPUT_LABEL . ':' . $buffer . "\n";
($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['output-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_455'
static fn() => ob_start($rootCallback)
MODULE_ENTRY_455
, get_defined_vars(), __DIR__);
echo 'user callback';
ob_end_flush();

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['output-owner'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_456'
static fn() => ob_start('class_exists')
MODULE_ENTRY_456
, get_defined_vars(), __DIR__);
echo 'ModuleOutputClass';
// The final buffer is flushed at request shutdown in the root context.
?>
--EXPECT--
module registration retained
root registration retained
module:user callback
root:user callback
