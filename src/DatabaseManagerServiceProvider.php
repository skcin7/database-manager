<?php

namespace skcin7\DatabaseManager;

use BackupManager\Databases;
use BackupManager\Filesystems;
use BackupManager\Compressors;
use Symfony\Component\Process\Process;
use Illuminate\Support\ServiceProvider;
use BackupManager\Config\Config;
use BackupManager\ShellProcessing\ShellProcessor;
use Illuminate\Support\Arr;

/**
 * Class DatabaseManagerServiceProvider
 *
 * @package skcin7\DatabaseManager
 */
class DatabaseManagerServiceProvider extends ServiceProvider {

    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $configPath = __DIR__ . '/../config/database-manager.php';
        $this->publishes([$configPath => config_path('database-manager.php')], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $configPath = __DIR__ . '/../config/database-manager.php';
        $this->mergeConfigFrom($configPath, 'database-manager');
        $this->registerFilesystemProvider();
        $this->registerDatabaseProvider();
        $this->registerCompressorProvider();
        $this->registerShellProcessor();
        $this->registerArtisanCommands();
    }

    /**
     * Merge the given configuration with the existing configuration.
     * https://medium.com/@koenhoeijmakers/properly-merging-configs-in-laravel-packages-a4209701746d
     *
     * @param  string  $path
     * @param  string  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        $config = $this->app['config']->get($key, []);
        $this->app['config']->set($key, $this->mergeConfig(require $path, $config));
    }

    /**
     * Merges the configs together and takes multi-dimensional arrays into account.
     *
     * @param  array  $original
     * @param  array  $merging
     * @return array
     */
    protected function mergeConfig(array $original, array $merging)
    {
        $array = array_merge($original, $merging);
        foreach ($original as $key => $value) {
            if(! is_array($value)) {
                continue;
            }
            if(! Arr::exists($merging, $key)) {
                continue;
            }
            if(is_numeric($key)) {
                continue;
            }
            $array[$key] = $this->mergeConfig($value, $merging[$key]);
        }
        return $array;
    }

    /**
     * Register the filesystem provider.
     *
     * @return void
     */
    private function registerFilesystemProvider() {
        $this->app->bind(\BackupManager\Filesystems\FilesystemProvider::class, function ($app) {
            $provider = new Filesystems\FilesystemProvider(new Config($app['config']['database-manager.providers']));
            $provider->add(new Filesystems\Awss3Filesystem);
            $provider->add(new Filesystems\GcsFilesystem);
            $provider->add(new Filesystems\DropboxFilesystem);
            $provider->add(new Filesystems\DropboxV2Filesystem);
            $provider->add(new Filesystems\FtpFilesystem);
            $provider->add(new Filesystems\LocalFilesystem);
            $provider->add(new Filesystems\RackspaceFilesystem);
            $provider->add(new Filesystems\SftpFilesystem);
            return $provider;
        });
    }

    /**
     * Register the database provider.
     *
     * @return void
     */
    private function registerDatabaseProvider() {
        $this->app->bind(\BackupManager\Databases\DatabaseProvider::class, function ($app) {
            $provider = new Databases\DatabaseProvider($this->getDatabaseConfig($app['config']['database.connections']));
            $provider->add(new Databases\MysqlDatabase);
            $provider->add(new Databases\PostgresqlDatabase);
            return $provider;
        });
    }

    /**
     * Register the compressor provider.
     *
     * @return void
     */
    private function registerCompressorProvider() {
        $this->app->bind(\BackupManager\Compressors\CompressorProvider::class, function () {
            $provider = new Compressors\CompressorProvider;
            $provider->add(new Compressors\GzipCompressor);
            $provider->add(new Compressors\NullCompressor);
            return $provider;
        });
    }

    /**
     * Register the shell processor.
     *
     * @return void
     */
    private function registerShellProcessor() {
        $this->app->bind(\BackupManager\ShellProcessing\ShellProcessor::class, function () {
            return new ShellProcessor(new Process('', null, null, null, null));
        });
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerArtisanCommands() {
        $this->commands([
            \skcin7\DatabaseManager\Commands\BackupCommand::class,
            \skcin7\DatabaseManager\Commands\ListCommand::class,
            \skcin7\DatabaseManager\Commands\RestoreCommand::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return [
            \BackupManager\Filesystems\FilesystemProvider::class,
            \BackupManager\Databases\DatabaseProvider::class,
            \BackupManager\ShellProcessing\ShellProcessor::class,
        ];
    }

    /**
     * Get database configurations.
     *
     * @param $connections
     * @return Config
     */
    private function getDatabaseConfig($connections) {
        $mapped = array_map(function ($connection) {
            if(! in_array($connection['driver'], ['mysql', 'pgsql'])) {
                return;
            }

            if(isset($connection['port'])) {
                $port = $connection['port'];
            }
            else {
                if ($connection['driver'] === 'mysql') {
                    $port = '3306';
                } elseif ($connection['driver'] === 'pgsql') {
                    $port = '5432';
                }
            }

            return [
                'type'     => $connection['driver'],
                'host'     => $connection['host'],
                'port'     => $port,
                'user'     => $connection['username'],
                'pass'     => $connection['password'],
                'database' => $connection['database'],
                'ignoreTables' => $connection['driver'] === 'mysql' && isset($connection['ignoreTables'])
                    ? $connection['ignoreTables'] : null,
            ];
        }, $connections);
        return new Config($mapped);
    }
}
