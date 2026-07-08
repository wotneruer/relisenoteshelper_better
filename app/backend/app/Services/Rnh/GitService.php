<?php

namespace App\Services\Rnh;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class GitService
{
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    public function test(): array
    {
        $git = (string)$this->settings->get('GitExecutablePath', 'git');
        $reposPath = (string)$this->settings->get('RepositoriesPath', '/app/data/repos');
        $timeout = (int)$this->settings->get('GitTimeoutSeconds', 30);

        $result = [
            'ok' => false,
            'git' => $git,
            'version' => null,
            'repositories_path' => $reposPath,
            'repositories_path_exists' => is_dir($reposPath),
            'repositories_found' => 0,
            'error' => null,
        ];

        try {
            $process = new Process([$git, '--version']);
            $process->setTimeout($timeout);
            $process->run();

            if (!$process->isSuccessful()) {
                $result['error'] = trim($process->getErrorOutput() ?: $process->getOutput());
                return $result;
            }

            $result['version'] = trim($process->getOutput());
            $result['repositories_found'] = $this->countRepositories($reposPath);
            $result['ok'] = true;

            return $result;
        } catch (Throwable $e) {
            $result['error'] = $e->getMessage();
            return $result;
        }
    }

    private function countRepositories(string $path): int
    {
        if (!is_dir($path)) {
            return 0;
        }

        $count = 0;

        foreach (File::directories($path) as $directory) {
            if (is_dir($directory . '/.git')) {
                $count++;
            }
        }

        return $count;
    }
}
