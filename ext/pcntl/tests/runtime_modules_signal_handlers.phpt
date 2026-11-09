--TEST--
Runtime modules: signal callback names resolve in their registration context
--EXTENSIONS--
pcntl
posix
--FILE--
<?php
require_once __DIR__ . '/../../../Zend/tests/runtime_modules/file_fixture.inc';

function module_signal_handler(int $signal, array $info): void {
    echo "root\n";
}
$module = new RuntimeModule();
($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_406'
static function (): void {
    eval('function module_signal_handler(int $signal, array $info): void { echo "module\\n"; }');
    pcntl_signal(SIGUSR1, 'module_signal_handler');
}
MODULE_ENTRY_406
, get_defined_vars(), __DIR__);
var_dump(pcntl_signal_get_handler(SIGUSR1));
posix_kill(getmypid(), SIGUSR1);
pcntl_signal_dispatch();

pcntl_signal(SIGUSR1, 'module_signal_handler');
($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_407'
static function (): void {
    posix_kill(getmypid(), SIGUSR1);
    pcntl_signal_dispatch();
}
MODULE_ENTRY_407
, get_defined_vars(), __DIR__);

$rootCallback = static function (int $signal, array $info): void { echo "root closure\n"; };
($GLOBALS["module_fixture"])($module, <<<'MODULE_ENTRY_408'
static fn() => pcntl_signal(SIGUSR1, $rootCallback)
MODULE_ENTRY_408
, get_defined_vars(), __DIR__);
posix_kill(getmypid(), SIGUSR1);
pcntl_signal_dispatch();
pcntl_signal(SIGUSR1, SIG_IGN);
var_dump(pcntl_signal_get_handler(SIGUSR1) === SIG_IGN);
?>
--EXPECT--
string(21) "module_signal_handler"
module
root
root closure
bool(true)
