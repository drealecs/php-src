--TEST--
JIT runtime modules: tracing remains safe across requests
--EXTENSIONS--
opcache
--CONFLICTS--
server
--FILE--
<?php
$fixture = __DIR__ . '/runtime_modules_request_safety.php';
file_put_contents($fixture, <<<'PHP'
<?php

function runDirectMutationTrace(): string
{
    $result = 'missing';
    for ($i = 0; $i < 1000; $i++) {
        if ($i === 0) {
            module_add_dependency('phpt-direct-dependency');
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
            module_add_dependency('phpt-side-exit-dependency');
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
            module_add_dependency('phpt-linked-side-dependency');
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
        module_run('phpt-direct-dependency', static function () use ($value): void {
            eval('function phpt_direct_value(): string { return ' . $value . '; }');
        });
        echo "direct:", runDirectMutationTrace(), "\n";
        break;

    case 'entry':
        if (isset($_GET['value'])) {
            module_run('phpt-entry-dependency', static function () use ($value): void {
                eval('function phpt_entry_value(): string { return ' . $value . '; }');
            });
            module_add_dependency('phpt-entry-dependency');
        }
        echo "entry:", runRootDependencyEntryTrace(), "\n";
        break;

    case 'side-exit':
        module_run('phpt-side-exit-dependency', static function () use ($value): void {
            eval('function phpt_side_exit_value(): string { return ' . $value . '; }');
        });
        echo "side-exit:", runSideExitTrace(new SideExitMutator($mutate)), "\n";
        break;

    case 'linked-side':
        $target = module_run('phpt-linked-side-dependency', static function () use ($value): object {
            eval('class PhptLinkedSideTarget { public function value(): string { return ' . $value . '; } }');
            return new PhptLinkedSideTarget();
        });
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
