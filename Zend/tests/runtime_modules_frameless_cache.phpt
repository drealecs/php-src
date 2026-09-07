--TEST--
Runtime modules: namespaced frameless fallback caches are context-sensitive
--FILE--
<?php
namespace PhptFramelessCache;

$probe = fn() => trim(' value ');

var_dump($probe());

\module_run('phpt_frameless_cache', static function (): void {
    eval(<<<'PHP'
        namespace PhptFramelessCache;
        function trim(string $value): string { return 'module'; }
    PHP);
});

var_dump(\module_run('phpt_frameless_cache', $probe));
?>
--EXPECT--
string(5) "value"
string(6) "module"
