--TEST--
Runtime modules: catch type matching follows module class identity
--FILE--
<?php
$a = 'phpt_exception_a';
$b = 'phpt_exception_b';
$dep = 'phpt_exception_dep';

module_run($dep, function (): void {
    eval(<<<'PHP'
        class PhptDepException extends Exception {}
        function throw_dep_exception(): void { throw new PhptDepException('dep'); }
    PHP);
});

module_run($b, function (): void {
    eval(<<<'PHP'
        class PhptOwnException extends Exception {}
        function throw_b_exception(): void { throw new PhptOwnException('b'); }
    PHP);
});

$catchers = module_run($a, function () use ($dep): array {
    module_add_dependency($dep);
    eval(<<<'PHP'
        class PhptOwnException extends Exception {}
    PHP);

    return [
        function (Throwable $throwable): string {
            try {
                throw $throwable;
            } catch (MissingRuntimeModuleCatchClass $e) {
                return 'missing';
            } catch (PhptOwnException $e) {
                return 'module';
            } catch (Throwable $e) {
                return 'throwable';
            }
        },
        function (): string {
            try {
                throw_dep_exception();
            } catch (PhptDepException $e) {
                return 'dependency';
            } catch (Throwable $e) {
                return 'throwable';
            }
        },
    ];
});

$aException = module_run($a, fn() => new PhptOwnException('a'));
$bException = module_run($b, function () {
    try {
        throw_b_exception();
    } catch (Throwable $e) {
        return $e;
    }
});

var_dump($catchers[0]($aException));
var_dump($catchers[0]($bException));
var_dump($catchers[1]());
?>
--EXPECT--
string(6) "module"
string(9) "throwable"
string(10) "dependency"
