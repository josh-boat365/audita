<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'bpsl' => [
        'auth_url' => env('AUTH_API_URL'),

    ],

    /*
    |--------------------------------------------------------------------------
    | Auditor API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the external Auditor .NET API backend.
    | This API handles all data storage and business logic.
    |
    */
    'auditor_api' => [
        'base_url' => env('AUDITOR_API_URL', 'http://192.168.1.200:5126'),
        'auth_url' => env('AUDITOR_AUTH_URL', 'http://192.168.1.200:5126/Auditor/Login'),
        'verify_ssl' => env('AUDITOR_API_VERIFY_SSL', false),
        'timeout' => env('AUDITOR_API_TIMEOUT', 30),
        'retry_times' => env('AUDITOR_API_RETRY_TIMES', 3),
        'retry_delay' => env('AUDITOR_API_RETRY_DELAY', 100),

        // Endpoint paths
        'endpoints' => [
            // Authentication
            'login' => '/Auditor/Login',

            // Exception Tracker
            'exception_tracker' => '/Auditor/ExceptionTracker',
            'exception_close' => '/Auditor/ExceptionTracker/close-exception',
            'exception_auditee_update' => '/Auditor/ExceptionTracker/auditee-update',
            'exception_batch_request' => '/Auditor/ExceptionTracker/batch-request',
            'pending_batch_exceptions' => '/Auditor/ExceptionTracker/pending-batch-exceptions',
            'batch_exception' => '/Auditor/ExceptionTracker/get-batch-exception',
            'update_batch_status' => '/Auditor/ExceptionTracker/update-batch-exception-status',
            'update_single_exception_status' => '/Auditor/ExceptionTracker/update-single-exception-status',

            // Exception Batch
            'exception_batch' => '/Auditor/ExceptionBatch',

            // Activity Group
            'activity_group' => '/Auditor/ActivityGroup',
            'employee_groups' => '/Auditor/GroupMember/employee',

            // Group Members
            'group_member' => '/Auditor/GroupMember',

            // Auditor Unit
            'auditor_unit' => '/Auditor/AuditorUnit',

            // Process Type
            'process_type' => '/Auditor/ProcessType',
            'sub_process_type' => '/Auditor/SubProcessType',

            // Risk Rate
            'risk_rate' => '/Auditor/RiskRate',

            // Exception Comments
            'exception_comment' => '/Auditor/ExceptionComment',

            // Exception File Upload
            'exception_file_upload' => '/Auditor/ExceptionFileUpload',
        ],
    ],

];
