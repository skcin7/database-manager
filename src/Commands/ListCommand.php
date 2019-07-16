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
    private $required_arguments = ['provider'];

    /**
     * @param FilesystemProvider $filesystems
     */
    public function __construct(FilesystemProvider $filesystems) {
        $this->filesystems = $filesystems;
        parent::__construct();
    }

    /**
     * @throws \BackupManager\Config\ConfigNotFoundForConnection
     * @throws \BackupManager\Filesystems\FilesystemTypeNotSupported
     */
    public function handle() {
        // Ensure all required arguments are set, and validate them.
        $this->promptUserForRequiredArguments();

        $filesystem = $this->filesystems->get($this->option('provider'));
        $contents = $filesystem->listContents('/');
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


    /**
     * Prompt user for required arguments if they are missing.
     */
    private function promptUserForRequiredArguments()
    {
        foreach($this->required_arguments as $required_argument)
        {
            // Handle 'provider' argument:
            if(! $this->option($required_argument) && $required_argument === 'provider') {
                $providers = $this->filesystems->getAvailableProviders();
                $formatted = implode(', ', $providers);
                $this->info("Available providers: <comment>{$formatted}</comment>");
                $provider = $this->autocomplete("Which provider?", $providers);
                $this->input->setOption('provider', $provider);
                $this->line('');
            }
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['provider', null, InputOption::VALUE_OPTIONAL, 'Provider to list backups for', null],
        ];
    }


} 
