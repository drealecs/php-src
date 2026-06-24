--TEST--
JIT runtime modules: tracing remains safe across requests
--EXTENSIONS--
opcache
--CONFLICTS--
server
--FILE--
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

$fixture = __DIR__ . '/runtime_modules_request_safety.php';
file_put_contents($fixture, <<<'PHP'
<?php
require_once __DIR__ . '/../../../../Zend/tests/runtime_modules/file_fixture.inc';

function runDirectMutationTrace(): string
{
    $result = 'missing';
    for ($i = 0; $i < 1000; $i++) {
        if ($i === 0) {
            \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt-direct-dependency'] ??= new \RuntimeModule());
        }
        $result = phpt_direct_value();
    }
    return $result;
}

function runRootDependencyEntryTrace(): string
{
    $result = 'missing';
    for ($i = 0; $i < 1000; $i++) {
        if (function_exists('phpt_entry_value')) {
            $result = phpt_entry_value();
        } else {
            $result = 'missing';
        }
    }
    return $result;
}

class SideExitMutator
{
    public function __construct(private bool $mutate)
    {
    }

    public function __get(string $name): mixed
    {
        if ($this->mutate) {
            \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt-side-exit-dependency'] ??= new \RuntimeModule());
        }
        return $this->mutate;
    }
}

function runSideExitTrace(object $mutator): string
{
    $result = 'missing';
    for ($i = 0; $i < 1000; $i++) {
        if ($mutator->missing) {
            $result = phpt_side_exit_value();
        } else {
            $result = 'missing';
        }
    }
    return $result;
}

class LinkedSideMutator
{
    public function __construct(private bool $mutate)
    {
    }

    public function __get(string $name): mixed
    {
        if ($this->mutate) {
            \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt-linked-side-dependency'] ??= new \RuntimeModule());
        }
        return false;
    }
}

function runLinkedSideTrace(object $mutator, object $target): string
{
    $result = 'missing';
    for ($i = 0; $i < 1000; $i++) {
        if ($i === 0) {
            $mutator->missing;
        } else {
            $result = $target->value();
        }
    }
    return $result;
}

$case = $_GET['case'];
$value = var_export($_GET['value'] ?? 'first', true);
$mutate = isset($_GET['mutate']);

switch ($case) {
    case 'direct':
        ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt-direct-dependency'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_392'
static function () use ($value): void {
            eval('function phpt_direct_value(): string { return ' . $value . '; }');
        }
MODULE_ENTRY_392
, get_defined_vars(), __DIR__);
        echo "direct:", runDirectMutationTrace(), "\n";
        break;

    case 'entry':
        if (isset($_GET['value'])) {
            ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt-entry-dependency'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_393'
static function () use ($value): void {
                eval('function phpt_entry_value(): string { return ' . $value . '; }');
            }
MODULE_ENTRY_393
, get_defined_vars(), __DIR__);
            \RuntimeModule::current()->addDependency($GLOBALS["runtime_modules"]['phpt-entry-dependency'] ??= new \RuntimeModule());
        }
        echo "entry:", runRootDependencyEntryTrace(), "\n";
        break;

    case 'side-exit':
        ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt-side-exit-dependency'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_394'
static function () use ($value): void {
            eval('function phpt_side_exit_value(): string { return ' . $value . '; }');
        }
MODULE_ENTRY_394
, get_defined_vars(), __DIR__);
        echo "side-exit:", runSideExitTrace(new SideExitMutator($mutate)), "\n";
        break;

    case 'linked-side':
        $target = ($GLOBALS["module_fixture"])(($GLOBALS["runtime_modules"]['phpt-linked-side-dependency'] ??= new \RuntimeModule()), <<<'MODULE_ENTRY_395'
static function () use ($value): object {
            eval('class PhptLinkedSideTarget { public function value(): string { return ' . $value . '; } }');
            return new PhptLinkedSideTarget();
        }
MODULE_ENTRY_395
, get_defined_vars(), __DIR__);
        echo "linked-side:", runLinkedSideTrace(new LinkedSideMutator($mutate), $target), "\n";
        break;
}
PHP);

include dirname(__DIR__) . '/php_cli_server.inc';
$ini = trim((string) getenv('TEST_PHP_EXTRA_ARGS'));
$ini .= ($ini !== '' ? ' ' : '') . implode(' ', [
    '-d opcache.enable=1',
    '-d opcache.enable_cli=1',
    '-d opcache.file_update_protection=0',
    '-d opcache.protect_memory=1',
    '-d opcache.jit=tracing',
    '-d opcache.jit_buffer_size=16M',
    '-d opcache.jit_hot_loop=1',
    '-d opcache.jit_hot_func=0',
    '-d opcache.jit_hot_side_exit=1',
]);
php_cli_server_start($ini);

$base = 'http://' . PHP_CLI_SERVER_ADDRESS . '/jit/runtime_modules_request_safety.php?case=';
echo file_get_contents($base . 'direct&value=first');
echo file_get_contents($base . 'direct&value=second');
echo file_get_contents($base . 'entry');
echo file_get_contents($base . 'entry&value=second');
echo file_get_contents($base . 'side-exit&value=first');
echo file_get_contents($base . 'side-exit&value=second&mutate=1');
echo file_get_contents($base . 'linked-side&value=first');
echo file_get_contents($base . 'linked-side&value=second&mutate=1');
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/runtime_modules_request_safety.php');
?>
--EXPECT--
direct:first
direct:second
entry:missing
entry:second
side-exit:missing
side-exit:second
linked-side:first
linked-side:second
