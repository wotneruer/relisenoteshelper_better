<?php

namespace App\Services\Rnh;

use Symfony\Component\Process\Process;

class GitRunner
{
    public function run(array $args, ?string $cwd = null, int $timeout = 3600): array
    {
        $process = new Process($args, $cwd);
        $process->setTimeout($timeout);
        $process->run();

        return [
            'ok' => $process->isSuccessful(),
            'exit_code' => $process->getExitCode(),
            'command' => $process->getCommandLine(),
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput(),
        ];
    }

    public function version(): array
    {
        return $this->run(['git', '--version'], null, 30);
    }

    public function refs(string $repoPath): array
    {
        $tags = $this->run(['git', 'tag', '--sort=-creatordate'], $repoPath);
        $branches = $this->run(['git', 'branch', '-r', '--format=%(refname:short)'], $repoPath);

        return [
            'tags' => $this->splitLines($tags['stdout'] ?? ''),
            'branches' => $this->splitLines($branches['stdout'] ?? ''),
            'raw' => [
                'tags' => $tags,
                'branches' => $branches,
            ],
        ];
    }

    private function splitLines(string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $value) ?: [])));
    }
}
