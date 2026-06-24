--TEST--
Runtime modules: root dependency visibility invalidates negative and silent lookups
--FILE--
<?php
$dep = 'phpt_root_dependency_caches_dep';

function phpt_root_dependency_probe(): array {
    return [
        class_exists('PhptRootDependencyCacheClass', false),
        function_exists('phpt_root_dependency_cache_function'),
        defined('PHPT_ROOT_DEPENDENCY_CACHE_CONSTANT'),
    ];
}

function phpt_root_dependency_catch(Throwable $throwable): string {
    try {
        throw $throwable;
    } catch (PhptRootDependencyCacheException $e) {
        return 'dependency';
    } catch (Throwable $e) {
        return 'throwable';
    }
}

module_run($dep, function (): void {
    eval(<<<'PHP'
        class PhptRootDependencyCacheClass {}
        class PhptRootDependencyCacheException extends Exception {}
        function phpt_root_dependency_cache_function(): string { return 'function'; }
        const PHPT_ROOT_DEPENDENCY_CACHE_CONSTANT = 'constant';
    PHP);
});

$exception = module_run($dep, fn() => new PhptRootDependencyCacheException('dep'));

var_dump(phpt_root_dependency_probe());
var_dump(phpt_root_dependency_catch($exception));

module_add_dependency($dep);

var_dump(phpt_root_dependency_probe());
var_dump(phpt_root_dependency_cache_function());
var_dump(PHPT_ROOT_DEPENDENCY_CACHE_CONSTANT);
var_dump(phpt_root_dependency_catch($exception));
?>
--EXPECT--
array(3) {
  [0]=>
  bool(false)
  [1]=>
  bool(false)
  [2]=>
  bool(false)
}
string(9) "throwable"
array(3) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
}
string(8) "function"
string(8) "constant"
string(10) "dependency"
