<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/provafacil_sync:sync' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
