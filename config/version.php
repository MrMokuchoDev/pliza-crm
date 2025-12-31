<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Current Version
    |--------------------------------------------------------------------------
    |
    | The current version of the application. This is used to compare against
    | the latest version available on GitHub to determine if an update is available.
    |
    */
    'current' => '1.4.1',

    /*
    |--------------------------------------------------------------------------
    | Update Channel
    |--------------------------------------------------------------------------
    |
    | The update channel to use when checking for updates.
    | - 'stable': Only stable releases (no pre-releases)
    | - 'beta': Include pre-release versions
    |
    */
    'channel' => env('UPDATE_CHANNEL', 'stable'),

    /*
    |--------------------------------------------------------------------------
    | Auto Check for Updates
    |--------------------------------------------------------------------------
    |
    | Whether to automatically check for updates when an admin logs in.
    |
    */
    'auto_check' => env('AUTO_UPDATE_CHECK', true),

    /*
    |--------------------------------------------------------------------------
    | Check Interval
    |--------------------------------------------------------------------------
    |
    | How often to check for updates automatically.
    | Supported: 'hourly', 'daily', 'weekly'
    |
    */
    'check_interval' => env('UPDATE_CHECK_INTERVAL', 'daily'),

    /*
    |--------------------------------------------------------------------------
    | GitHub Repository
    |--------------------------------------------------------------------------
    |
    | The GitHub repository to check for updates.
    | Format: 'owner/repo'
    |
    */
    'github_repo' => 'MrMokuchoDev/pliza-crm',

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    |
    | Settings for creating backups before updates.
    |
    */
    'backup' => [
        'enabled' => true,
        'include_database' => true,
        'include_uploads' => true,
        'max_backups' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Preserved Paths
    |--------------------------------------------------------------------------
    |
    | Paths that should not be overwritten during updates.
    |
    */
    'preserved_paths' => [
        '.env',
        'storage/app/public',
        'storage/app/backups',
        'storage/app/updates',
        'storage/logs',
    ],
];
