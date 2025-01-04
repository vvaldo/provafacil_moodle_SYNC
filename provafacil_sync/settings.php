<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_provafacil_sync', get_string('pluginname', 'local_provafacil_sync'));

    $settings->add(new admin_setting_configtext(
        'local_provafacil_sync/apitoken',
        get_string('apitoken', 'local_provafacil_sync'),
        get_string('apitokendesc', 'local_provafacil_sync'),
        'https://unisced.provafacilnaweb.com.br/unisced/api/'
    ));

    $settings->add(new admin_setting_configtext(
        'local_provafacil_sync/apiurl',
        get_string('apiurl', 'local_provafacil_sync'),
        get_string('apiurldesc', 'local_provafacil_sync'),
        '#apitoken'
    ));

    $ADMIN->add('localplugins', $settings);
}
$ADMIN->add('localplugins', new admin_externalpage(
    'local_provafacil_sync_enroll',
    get_string('enrollstudent', 'local_provafacil_sync'),
    new moodle_url('/local/provafacil_sync/enroll_student.php'),
    'moodle/site:config'
));
