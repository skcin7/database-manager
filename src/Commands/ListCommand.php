<?php

namespace skcin7\DatabaseManager\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use BackupManager\Filesystems\FilesystemProvider;

/**
 * Class ListCommand
 *
 * @package skcin7\DatabaseManager
 */
class ListCommand extends Command {

    use HandlesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get a list of database backups.';

    /**
     * @var \BackupManager\Filesystems\FilesystemProvider
     */
    private $filesystems;

    /**
     * The required arguments.
     *
     * @var array
     */
    private $required = ['source', 'path'];

    /**
     * The missing arguments.
     *
     * @var array
     */
    private $missingArguments;

    /**
     * @param FilesystemProvider $filesystems
     */
    public function __construct(FilesystemProvider $filesystems) {
        parent::__construct();
        $this->filesystems = $filesystems;
    }

    /**
     * Execute the console command.
     *
     * @throws \LogicException
     * @throws \BackupManager\Filesystems\FilesystemTypeNotSupported
     * @throws \BackupManager\Config\ConfigFieldNotFound
     * @throws \BackupManager\Config\ConfigNotFoundForConnection
     * @return void
     */
    public function handle() {
        if($this->isMissingArguments()) {
            $this->displayMissingArguments();
            $this->promptForMissingArgumentValues();
            $this->validateArguments();
        }

        $filesystem = $this->filesystems->get($this->option('source'));
        $contents = $filesystem->listContents($this->option('path'));
        $rows = [];
        foreach ($contents as $file) {
            if ($file['type'] == 'dir') continue;
            $rows[] = [
                $file['basename'],
                key_exists('extension', $file) ? $file['extension'] : null,
                $this->formatBytes($file['size']),
                date('D j Y  H:i:s', $file['timestamp'])
            ];
        }
        $this->table(['Name', 'Extension', 'Size', 'Created'], $rows);
    }






//    /**
//     * @return void
//     */
//    private function validateArguments() {
//        $root = $this->filesystems->getConfig($this->option('source'), 'root');
//        $this->info('Just to be sure...');
//        $this->info(sprintf('Do you want to list files from <comment>%s</comment> on <comment>%s</comment>?',
//            $root . $this->option('path'),
//            $this->option('source')
//        ));
//        $this->line('');
//        $confirmation = $this->confirm('Are these correct? [Y/n]');
//        if ( ! $confirmation) {
//            $this->reaskArguments();
//        }
//    }

    /**
     * Get the console command options.
     *
     * @return void
     */
    private function reaskArguments() {
        $this->line('');
        $this->info('Answers have been reset and re-asking questions.');
        $this->line('');
        $this->promptForMissingArgumentValues();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['source', null, InputOption::VALUE_OPTIONAL, 'Source configuration name', null],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Directory path', null],
        ];
    }


} 
