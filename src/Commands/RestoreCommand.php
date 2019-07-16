<?php

namespace skcin7\DatabaseManager\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use BackupManager\Databases\DatabaseProvider;
use BackupManager\Procedures\RestoreProcedure;
use BackupManager\Filesystems\FilesystemProvider;

/**
 * Class RestoreCommand
 *
 * @package skcin7\DatabaseManager
 */
class RestoreCommand extends Command {

    use HandlesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore a database backup.';

    /**
     * RestoreProcedure
     *
     * @var \BackupManager\Procedures\RestoreProcedure
     */
    private $restoreProcedure;

    /**
     * DatabaseProvider
     *
     * @var \BackupManager\Databases\DatabaseProvider
     */
    private $databases;

    /**
     * FilesystemProvider
     *
     * @var \BackupManager\Filesystems\FilesystemProvider
     */
    private $filesystems;

    /**
     * The required arguments.
     *
     * @var array
     */
    private $required_arguments = ['database', 'provider', 'sourcePath'];

    /**
     * Compression format to be used.
     *
     * @var string
     */
    private $compression = 'gzip';

    /**
     * @param \BackupManager\Procedures\RestoreProcedure $restore
     * @param \BackupManager\Filesystems\FilesystemProvider $filesystems
     * @param \BackupManager\Databases\DatabaseProvider $databases
     */
    public function __construct(RestoreProcedure $restoreProcedure, FilesystemProvider $filesystems, DatabaseProvider $databases) {
        $this->restoreProcedure = $restoreProcedure;
        $this->filesystems = $filesystems;
        $this->databases = $databases;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        // Ensure all required arguments are set, and validate them.
        $this->promptUserForRequiredArguments();

        try {
            $this->info('Restoring backup ...');
            $this->restoreProcedure->run(
                $this->option('provider'),
                $this->option('sourcePath'),
                $this->option('database'),
                $this->compression
            );
        }
        catch(\Exception $ex) {
            $this->error($ex->getMessage());
            return 1;
        }

        $this->info(sprintf('Successfully restored! <comment>%s</comment> from <comment>%s</comment> to database <comment>%s</comment>.',
            $this->option('sourcePath'),
            $this->option('provider'),
            $this->option('database')
        ));
    }

    /**
     * Prompt user for required arguments if they are missing.
     */
    private function promptUserForRequiredArguments()
    {
        foreach($this->required_arguments as $required_argument)
        {
            // Handle 'database' argument:
            if(! $this->option($required_argument) && $required_argument === 'database') {
                $providers = $this->databases->getAvailableProviders();
                $formatted = implode(', ', $providers);
                $this->info("Available databases: <comment>{$formatted}</comment>");
                $database = $this->autocomplete("Which database?", $providers);
                $this->input->setOption('database', $database);
                $this->line('');
            }

            // Handle 'provider' argument:
            if(! $this->option($required_argument) && $required_argument === 'provider') {
                $providers = $this->filesystems->getAvailableProviders();
                $formatted = implode(', ', $providers);
                $this->info("Available providers: <comment>{$formatted}</comment>");
                $provider = $this->autocomplete("Which provider?", $providers);
                $this->input->setOption('provider', $provider);
                $this->line('');
            }

            // Handle 'sourcePath' argument:
            if(! $this->option($required_argument) && $required_argument === 'sourcePath') {
                // ask path
                $root = $this->filesystems->getConfig($this->option('provider'), 'root');
                $path = $this->ask("From which path do you want to select?<comment> {$root}</comment>");
                $this->line('');

                // ask file
                $filesystem = $this->filesystems->get($this->option('provider'));
                $contents = $filesystem->listContents($path);

                $files = [];

                foreach($contents as $file) {
                    if ($file['type'] == 'dir') continue;
                    $files[] = $file['basename'];
                }

                if(empty($files)) {
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
        }
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
            ['sourcePath', null, InputOption::VALUE_OPTIONAL, 'Path (filename) of the source', null],
        ];
    }


}
