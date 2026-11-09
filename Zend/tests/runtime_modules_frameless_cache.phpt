--TEST--
Runtime modules: namespaced frameless fallback caches observe new root dependencies
--FILE--
<?php
namespace PhptFramelessCache;
require_once __DIR__ . '/runtime_modules/file_fixture.inc';


$probe = fn() => trim(' value ');

var_dump($probe());

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt_frameless_cache'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_157'
static function (): void {
    eval(<<<'PHP'
        namespace PhptFramelessCache;
        function trim(string $value): string { return 'module'; }
    PHP);
}
MODULE_ENTRY_157
, get_defined_vars(), __DIR__);

\RuntimeModule::current()->addDependency($GLOBALS['runtime_modules']['phpt_frameless_cache']);
var_dump($probe());
?>
--EXPECT--
string(5) "value"
string(6) "module"
