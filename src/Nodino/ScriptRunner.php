<?php

/*
 * Copyright (c) 2021 Anton Bagdatyev (Tonix)
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Nodino;

use Cliche\Command\Node\NodeCommandPath;
use Cliche\Command\Node\NodeCommandPathInterface;
use Cliche\Exec\CommandExecutorInterface;
use Cliche\Exec\ProcOpenCommandExecutor;

/**
 * Nodino's script runner.
 *
 * @author Anton Bagdatyev (Tonix) <antonytuft@gmail.com>
 */
class ScriptRunner implements ScriptRunnerInterface {
  /**
   * @var NodeCommandPathInterface|null
   */
  protected $node;

  /**
   * @var CommandExecutorInterface|null
   */
  protected $executor;

  /**
   * @var string[]
   */
  protected $jsDirs;

  /**
   * @var array
   */
  protected $lookedUpScriptsMap;

  /**
   * Constructor.
   *
   * @param array An array of options with the following keys:
   *
   *                  - 'node' (NodeCommandPathInterface): Node.js command path. Defaults to {@link NodeCommandPath};
   *                  - 'executor' (CommandExecutorInterface): Command executor. Defaults to {@link ProcOpenCommandExecutor};
   *                  - 'jsDirs' (string[]): Array of directories where to look for JS files, in the given order;
   */
  public function __construct($options = []) {
    [
      'node' => $node,
      'executor' => $executor,
      'jsDirs' => $jsDirs,
    ] = $options + [
      'node' => null,
      'executor' => null,
      'jsDirs' => [],
    ];

    $this->node = $node;
    $this->executor = $executor;
    $this->jsDirs = $jsDirs;
    $this->lookedUpScriptsMap = [];
  }

  /**
   * {@inheritdoc}
   */
  public function run($script, $args) {
    $script = $this->lazyInit($script);

    $argsEscaped = array_map('escapeshellarg', $args);
    $command = sprintf(
      '%1$s %2$s %3$s',
      $this->node,
      $script,
      implode(' ', $argsEscaped)
    );
    $result = $this->executor->execute($command);
    return $result;
  }

  /**
   * Lazily init this Nodino instance.
   *
   * @param string $script The script to execute.
   * @return string The eventually resolved path of the script to execute or the same string as the one given as parameter.
   */
  protected function lazyInit($script) {
    if (empty($this->node)) {
      $this->node = new NodeCommandPath();
    }

    if (empty($this->executor)) {
      $this->executor = new ProcOpenCommandExecutor();
    }

    $extension = pathinfo($script, PATHINFO_EXTENSION);
    if (empty($extension)) {
      $script .= '.js';
    }
    if (!empty($this->lookedUpScriptsMap[$script])) {
      return $this->lookedUpScriptsMap[$script];
    } else {
      $this->lookedUpScriptsMap[$script] = $script;
      foreach ($this->jsDirs as $jsDir) {
        $rtrimmed = rtrim($jsDir, DIRECTORY_SEPARATOR);
        $path = implode(DIRECTORY_SEPARATOR, [$rtrimmed, $script]);
        if (file_exists($path)) {
          $this->lookedUpScriptsMap[$script] = $path;
          break;
        }
      }
      return $this->lookedUpScriptsMap[$script];
    }
  }
}
