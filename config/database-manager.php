<?php

return [

    /*
	|--------------------------------------------------------------------------
	| Providers Settings
	|--------------------------------------------------------------------------
	|
	| This lets you set the credentials for your providers which are used
    | to store database backups. Simply create an app in the official
    | Dropbox developer webpage to create these credentials.
	|
	*/

    'providers' => [

        'local' => [
            'type' => 'Local',
            'root' => storage_path('app'),
        ],
        'dropbox' => [
            'type'   => 'DropboxV2',
            'token'  => env('DROPBOX_TOKEN', ''),
            'key'    => env('DROPBOX_KEY', ''),
            'secret' => env('DROPBOX_SECRET', ''),
            'app'    => env('DROPBOX_APP', ''),
            'root'   => env('DROPBOX_ROOT', ''),

            // Set in `backup-manager/backup-manager/src/FileSystems/DropboxV2Filesystem`
            // using `$config['filesystemConfig']`.
            'filesystem'   => [
                'disable_asserts' => true
            ],
        ]

    ],

    // Unused:
    //    'provider' => 'dropbox',
    //    'compression' => 'gzip',

];
