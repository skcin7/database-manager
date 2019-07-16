# Laravel Driver for the Database Backup Manager 1.3.1

Laravel package to manage your databases (including backups) easily in a Laravel project.

This package is based on [backup-manager/laravel](https://github.com/backup-manager/laravel) by Shawn McCool and Mitchell van Wijngaarden so credit to them for their great work.

> Note: This package is for Laravel integration only. For information about the framework-agnostic core package (or the Symfony driver) please see [the base package repository](https://github.com/backup-manager/backup-manager).

### Table of Contents

- [Stability Notice](#stability-notice)
- [Requirements](#requirements)
- [Installation](#installation)
- [Scheduling Backups](#scheduling-backups)
- [Contribution Guidelines](#contribution-guidelines)
- [Maintainers](#maintainers)
- [License](#license)

### Stability Notice

It's stable enough. You'll need to understand filesystem permissions.

I am actively developing/using this package, and I would appreciate all feedback/suggestions to improve it. [Please feel free to create an issue here on GitHub to submit feedback and ideas.](https://github.com/skcin7/database-manager)

### Requirements

- PHP 5.5
- Laravel
- MySQL support requires `mysqldump` and `mysql` command-line binaries
- PostgreSQL support requires `pg_dump` and `psql` command-line binaries
- Gzip support requires `gzip` and `gunzip` command-line binaries

### Installation

**Composer**

It's super easy.

1. Run the following to include this via Composer

```shell
composer require skcin7/database-manager
```

2. Publish the configuration file. After publishing, edit the configuration with your specific configuration.

```php
php artisan vendor:publish --provider="skcin7\DatabaseManager\DatabaseManagerServiceProvider"
```

This package makes use of your database configuration located in `config/database.php`. The package can easily backup and restore all connections listed in that file.

**IoC Resolution**

`BackupManager\Manager` can be automatically resolved through constructor injection thanks to Laravel's IoC container.

```php
use BackupManager\Manager;

public function __construct(Manager $manager) {
    $this->manager = $manager;
}
```

It can also be resolved manually from the container.

```php
$manager = App::make(\BackupManager\Manager::class);
```

**Artisan Commands**

After installation, there will be three Artisan commands available which are: `database-manager:create-backup`, `database-manager:list-backups`, and `database-manager:restore-backup`.

All will prompt you with simple questions to successfully execute the command.

**Example Command for 24hour scheduled cronjob**

```
php artisan db:backup --database=mysql --destination=dropbox --destinationPath=project --timestamp="d-m-Y" --compression=gzip
```

This command will backup your database to dropbox using mysql and gzip compresion in path /backups/project/DATE.gz (ex: /backups/project/31-7-2015.gz)

### Scheduling Backups

It's possible to schedule backups using Laravel's scheduler.

```PHP
/**
 * Define the application's command schedule.
 *
 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
 * @return void
 */
 protected function schedule(Schedule $schedule) {
     $environment = config('app.env');
     $schedule->command(
         "db:backup --database=mysql --destination=s3 --destinationPath=/{$environment}/projectname --timestamp="Y_m_d_H_i_s" --compression=gzip"
         )->twiceDaily(13,21);
 }
```

### Contribution Guidelines

We recommend using the vagrant configuration supplied with this package for development and contribution. Simply install VirtualBox, Vagrant, and Ansible then run `vagrant up` in the root folder. A virtualmachine specifically designed for development of the package will be built and launched for you.

When contributing please consider the following guidelines:

- please conform to the code style of the project, it's essentially PSR-2 with a few differences.
    1. The NOT operator when next to parenthesis should be surrounded by a single space. `if ( ! is_null(...)) {`.
    2. Interfaces should NOT be suffixed with `Interface`, Traits should NOT be suffixed with `Trait`.
- All methods and classes must contain docblocks.
- Ensure that you submit tests that have minimal 100% coverage.
- When planning a pull-request to add new functionality, it may be wise to [submit a proposal](https://github.com/backup-manager/laravel/issues/new) to ensure compatibility with the project's goals.

### Maintainers

This package is maintained by [Nick Morgan](http://nicholas-morgan.com).

### License

This package is licensed under the [MIT license](https://github.com/backup-manager/laravel/blob/master/LICENSE).
