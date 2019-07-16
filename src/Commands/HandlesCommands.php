<?php

namespace skcin7\DatabaseManager\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * Class HandlesCommands
 *
 * @package skcin7\DatabaseManager
 */
trait HandlesCommands
{


    














    /**
     * Ask user for the storage source.
     */
    private function askSource() {
        $providers = $this->filesystems->getAvailableProviders();
        $formatted = implode(', ', $providers);
        $this->info("Available storage services: <comment>{$formatted}</comment>");
        $source = $this->autocomplete("From which storage service do you want to choose?", $providers);
        $this->input->setOption('source', $source);
    }

    private function askDestinationPath() {
        $root = $this->filesystems->getConfig($this->option('destination'), 'root');
        $path = $this->ask("How do you want to name the backup?<comment> {$root}</comment>");
        $this->input->setOption('destinationPath', $path);
    }






    /**
     * Ask user for path.
     */
    private function askSourcePath() {
        // ask path
        $root = $this->filesystems->getConfig($this->option('source'), 'root');
        $path = $this->ask("From which path do you want to select?<comment> {$root}</comment>");
        $this->line('');

        // ask file
        $filesystem = $this->filesystems->get($this->option('source'));
        $contents = $filesystem->listContents($path);

        $files = [];

        foreach ($contents as $file) {
            if ($file['type'] == 'dir') continue;
            $files[] = $file['basename'];
        }

        if (empty($files)) {
            $this->info('No backups were found at this path.');
            return;
        }

        $rows = [];
        foreach ($contents as $file) {
            if($file['type'] == 'dir') continue;
            $rows[] = [
                $file['basename'],
                key_exists('extension', $file) ? $file['extension'] : null,
                $this->formatBytes($file['size']),
                date('D j Y  H:i:s', $file['timestamp'])
            ];
        }
        $this->info('Available database dumps:');
        $this->table(['Name', 'Extension', 'Size', 'Created'], $rows);
        $filename = $this->autocomplete("Which database dump do you want to restore?", $files);
        $this->input->setOption('sourcePath', "{$path}/{$filename}");
    }









    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'Database configuration name', null],
            ['provider', null, InputOption::VALUE_OPTIONAL, 'Provider to be used to store the backup', null],
        ];

//        return [
//            ['database', null, InputOption::VALUE_OPTIONAL, 'Database configuration name', null],
//            ['destination', null, InputOption::VALUE_OPTIONAL, 'Destination configuration name', null],
//            ['destinationPath', null, InputOption::VALUE_OPTIONAL, 'File destination path', null],
//            ['compression', null, InputOption::VALUE_OPTIONAL, 'Compression type', null],
//        ];
    }








    /**
     * Format bytes amount into easy human-readable string.
     *
     * @param $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }




    /**
     * @param $dialog
     * @param array $list
     * @param null $default
     * @throws \LogicException
     * @throws InvalidArgumentException
     * @internal param $question
     * @return mixed
     */
    public function autocomplete($dialog, array $list, $default = null) {
        $validation = function ($item) use ($list) {
            if(! in_array($item, array_values($list))) {
                throw new \InvalidArgumentException("{$item} does not exist.");
            }
            return $item;
        };

        try {
            return $this->useSymfontDialog($dialog, $list, $default, $validation);
        }
        catch(InvalidArgumentException $ex) {
            //
        }
        return $this->useSymfonyQuestion($dialog, $default, $validation);
    }

    /**
     * @param $dialog
     * @param array $list
     * @param null $default
     * @return mixed
     */
    protected function useSymfontDialog($dialog, array $list, $default = null, $validation) {
        $helper = $this->getHelperSet()->get('dialog');

        return $helper->askAndValidate(
            $this->output, "<question>{$dialog}</question>", $validation, false, $default, $list
        );
    }

    /**
     * @param $dialog
     * @param null $default
     * @return mixed
     */
    protected function useSymfonyQuestion($dialog, $default = null, $validation) {
        $question = new Question($dialog . ' ', $default);
        $question->setValidator($validation);
        $helper = $this->getHelper('question');

        return $helper->ask($this->input, $this->output, $question);
    }
}
