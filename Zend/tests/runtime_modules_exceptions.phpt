--TEST--
Runtime modules: catch type matching follows module class identity
--FILE--
<?php
require_once __DIR__ . '/runtime_modules/file_fixture.inc';

$a = 'phpt_exception_a';
$b = 'phpt_exception_b';
$dep = 'phpt_exception_dep';

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_146'
fn() => \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule())
MODULE_ENTRY_146
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$dep] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_147'
function (): void {
    eval(<<<'PHP'
        class PhptDepException extends Exception {}
        function throw_dep_exception(): void { throw new PhptDepException('dep'); }
    PHP);
}
MODULE_ENTRY_147
, get_defined_vars(), __DIR__);

($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_148'
function (): void {
    eval(<<<'PHP'
        class PhptOwnException extends Exception {}
        function throw_b_exception(): void { throw new PhptOwnException('b'); }
    PHP);
}
MODULE_ENTRY_148
, get_defined_vars(), __DIR__);

$catchers = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_149'
function (): array {
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
}
MODULE_ENTRY_149
, get_defined_vars(), __DIR__);

$aException = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$a] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_150'
fn() => new PhptOwnException('a')
MODULE_ENTRY_150
, get_defined_vars(), __DIR__);
$bException = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"][$b] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_151'
function () {
    try {
        throw_b_exception();
    } catch (Throwable $e) {
        return $e;
    }
}
MODULE_ENTRY_151
, get_defined_vars(), __DIR__);

var_dump($catchers[0]($aException));
var_dump($catchers[0]($bException));
var_dump($catchers[1]());
?>
--EXPECT--
string(6) "module"
string(9) "throwable"
string(10) "dependency"
