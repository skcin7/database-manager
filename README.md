# Database Manager - Laravel Package

Laravel package to manage your databases (including backups) easily in a Laravel project.

> Note: This package makes use of the framework-agnostic [backup-manager/backup-manager](https://github.com/backup-manager/backup-manager) package which this one is a dependency of. For more information (including Symfony driver) see that package.

> Credit: This package is based on [backup-manager/laravel](https://github.com/backup-manager/laravel) by [Shawn McCool](http://shawnmc.cool) and [Mitchell van Wijngaarden](http://kooding.nl). Credit to them for their great work.

### Table of Contents

- [Stability Notice](#stability-notice)
- [Requirements](#requirements)
- [Installation](#installation)
- [Scheduling Backups](#scheduling-backups)
- [Contribution Guidelines](#contribution-guidelines)
- [Maintainers](#maintainers)
- [License](#license)

### Stability Notice

It's stable.

I'm actively using this package in my own Laravel projects for managing backups. I would appreciate all feedback/suggestions you may have by [opening a GitHub issue](https://github.com/skcin7/database-manager/issues/new).

### Requirements

- PHP 5.5
- Laravel
- MySQL support requires `mysqldump` and `mysql` command-line binaries
- PostgreSQL support requires `pg_dump` and `psql` command-line binaries
- Gzip support requires `gzip` and `gunzip` command-line binaries

### Installation

**Use Composer**

It's super easy.

1. Run the following command: `composer require skcin7/database-manager`.

2. Publish the configuration file.

```php
php artisan vendor:publish --provider="skcin7\DatabaseManager\DatabaseManagerServiceProvider"
```

After publishing, edit this configuration file (which will be located in `config/database-manager.php`) to your specific configuration needs.

This package makes use of your database configurations in `config/database.php`. The package manages your database connections listed in that file.

**Artisan Commands**

After installation, there will be 3 new Artisan commands available in your project which are: `database-manager:create-backup`, `database-manager:list-backups`, and `database-manager:restore-backup`.

### Scheduling Backups

Now you can schedule your Laravel database backups (such as a daily backup) using Laravel Task Scheduling.

Inside `app/Console/Kernel.php`:

```PHP
/**
 * Define the application's command schedule.
 *
 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
 * @return void
 */
 protected function schedule(Schedule $schedule)
 {
     $schedule->command('database-manager:create-backup')->daily();
 }
```

### Contribution Guidelines

// TODO

### Maintainers

This package is maintained by [Nick Morgan](http://nicholas-morgan.com).

### License

This package is licensed under the [MIT License](https://github.com/skcin7/database-manager/blob/master/LICENSE.md).
