<?php

return [

    /*
	|--------------------------------------------------------------------------
	| Dropbox Settings
	|--------------------------------------------------------------------------
	|
	| This lets you set the credentials for your dropbox app which is used
    | to store database backups. Simply create an app in the official
    | Dropbox developer webpage to create these credentials.
	|
	*/

    'dropbox' => [
        'type'   => 'DropboxV2',
        'token'  => env('DROPBOX_TOKEN', ''),
        'key'    => env('DROPBOX_KEY', ''),
        'secret' => env('DROPBOX_SECRET', ''),
        'app'    => env('DROPBOX_APP', ''),
        'root'   => env('DROPBOX_ROOT', ''),
    ],
];
