# Database Manager Package for Your Laravel Projects

Laravel package to manage your databases (including backups) easily in a Laravel project.

> Note: This package makes use of the framework-agnostic [backup-manager/backup-manager](https://github.com/backup-manager/backup-manager) package which this one is a dependency of. For more information (including Symfony driver) see that package.

> Credit: This package is based on [backup-manager/laravel](https://github.com/backup-manager/laravel) by Shawn McCool and Mitchell van Wijngaarden so credit to them for their great work.

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

I'm actively using this package in my own Laravel projects for managing backups. I would appreciate all feedback/suggestions you may have by [opening a GitHub issue here](https://github.com/skcin7/database-manager).

### Requirements

- PHP 5.5
- Laravel
- MySQL support requires `mysqldump` and `mysql` command-line binaries
- PostgreSQL support requires `pg_dump` and `psql` command-line binaries
- Gzip support requires `gzip` and `gunzip` command-line binaries

### Installation

**Composer**

It's super easy.

1. Run the following command to include this package via Composer: `composer require skcin7/database-manager`.

2. Publish the configuration file.

```php
php artisan vendor:publish --provider="skcin7\DatabaseManager\DatabaseManagerServiceProvider"
```

After publishing, edit this configuration file (which will be located in `config/database-manager.php`) to have your specific configuration needs.

This package makes use of your database configurations in `config/database.php`. The package manages your database connections listed in that file.

**Artisan Commands**

After installation, there will be 3 new Artisan commands available in your project which are: `database-manager:create-backup`, `database-manager:list-backups`, and `database-manager:restore-backup`.

### Scheduling Your Backups

Now you can schedule your Laravel database backups (such as a daily backup) using Laravel Task Scheduling.

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

This package is licensed under the [MIT license](https://github.com/skcin7/database-manager/blob/master/LICENSE).
