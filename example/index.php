<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Nodino\ScriptRunner;
// use Cliche\Command\Node\NodeCommandPath;
// use Cliche\Exec\ProcOpenCommandExecutor;

$node = new ScriptRunner([
  'jsDirs' => [__DIR__ . '/js', __DIR__ . '/js/subdir/'],
  // 'node' => new NodeCommandPath(),
  // 'executor' => new ProcOpenCommandExecutor(),
]);
$result = $node->run('script', ['a', 'b', 'c']);

echo PHP_EOL;
echo json_encode(['result' => $result], JSON_PRETTY_PRINT);
echo PHP_EOL;

echo PHP_EOL;
