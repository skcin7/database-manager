<?php

namespace skcin7\DatabaseManager\Commands;

use BackupManager\Filesystems\Destination;
use Illuminate\Console\Command;
use BackupManager\Databases\DatabaseProvider;
use BackupManager\Procedures\BackupProcedure;
use BackupManager\Filesystems\FilesystemProvider;
use Carbon\Carbon;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class BackupCommand
 *
 * @package skcin7\DatabaseManager
 */
class BackupCommand extends Command {

    use HandlesCommands;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of a database and store it to the specified provider.';

    /**
     * BackupProcedure
     *
     * @var \BackupManager\Procedures\BackupProcedure
     */
    private $backupProcedure;

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
     * Arguments that are required for command execution.
     *
     * @var array
     */
    private $required_arguments = ['database', 'provider'];

    /**
     * Compression format to be used.
     *
     * @var string
     */
    private $compression = 'gzip';

    /**
     * @param BackupProcedure $backupProcedure
     * @param DatabaseProvider $databases
     * @param FilesystemProvider $filesystems
     */
    public function __construct(BackupProcedure $backupProcedure, DatabaseProvider $databases, FilesystemProvider $filesystems)
    {
        $this->backupProcedure = $backupProcedure;
        $this->databases = $databases;
        $this->filesystems = $filesystems;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Ensure all required arguments are set, and validate them.
        $this->promptUserForRequiredArguments();

//        if(! $this->validateArguments()) {
//            $this->error('Backup cancelled.');
//            return 1;
//        }

        try {
            // Store a list of destinations for where this backup to be stored to.
            $destinations = [];

            // Store a 'latest' copy which will always be the most up-to-date one:
            $destinations[] = new Destination(
                $this->option('provider'),
                $this->option('database') . '-latest.sql'
            );

            // Store a copy with the filename corresponding to the date/time it was backed up:
            $destinations[] = new Destination(
                $this->option('provider'),
                $this->option('database') . '-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql'
            );

            $this->info('Backing up ...');
            $this->backupProcedure->run(
                $this->option('database'),
                $destinations,
                $this->compression
            );
        }
        catch(\Exception $ex) {
            $this->error($ex->getMessage());
            return 1;
        }

        $this->info(sprintf('Successfully backed up! Database: `<comment>%s</comment>`, Provider: `<comment>%s</comment>`!',
            $this->option('database'),
            $this->option('provider')
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
        }
    }

    /**
     * Validate arguments.
     *
     * @throws \BackupManager\Config\ConfigFieldNotFound
     * @throws \BackupManager\Config\ConfigNotFoundForConnection
     * @return void
     */
    private function validateArguments() {
        //$root = $this->filesystems->getConfig($this->option('destination'), 'root');
        $this->info(sprintf('Database: `<comment>%s</comment>`, Provider: `<comment>%s</comment>`',
            $this->option('database'),
            $this->option('provider')
        ));
        return $this->confirm('Confirm Backup? [y/n]');
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
    }

}
