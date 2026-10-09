<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CLI script to set up the pluginsfetcher web service, user, role and token.
 *
 * @package   local_pluginsfetcher
 * @copyright 2026 Melanie Treitinger <melanie.treitinger@ruhr-uni-bochum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', 1);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir  . '/clilib.php');
require_once($CFG->dirroot . '/lib/externallib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

// Set the variables for the new webservice.
$wsname = 'pluginsfetcher';
$additionalcapabilities = [
    'moodle/site:config',
];

// Set system context.
$systemcontext = context_system::instance();

// Set admin user.
$USER = get_admin();

// Enable webservices and REST protocol.
set_config('enablewebservices', true);
$enabledprotocols = get_config('core', 'webserviceprotocols');
if (stripos($enabledprotocols, 'rest') === false) {
    set_config('webserviceprotocols', $enabledprotocols . ',rest');
}

// Enable webservice authentication.
$authsplugins = get_enabled_auth_plugins();
if (!in_array('webservice', $authsplugins)) {
    $authsplugins[] = 'webservice';
    set_config('auth', implode(',', $authsplugins));
    cli_writeln('Webservice authentication plugin enabled.');
} else {
    cli_writeln('Webservice authentication plugin is already enabled, skipping.');
}

// Create a webservice user.
$wsusername = 'ws-' . $wsname . '-user';
$existinguser = $DB->get_record('user', ['username' => $wsusername, 'deleted' => 0]);
if ($existinguser) {
    $webserviceuserid = $existinguser->id;
    cli_writeln('Webservice user "' . $wsusername . '" already exists (id=' . $webserviceuserid . '), skipping creation.');
} else {
    $webserviceuserid = user_create_user([
        'username' => 'ws-' . $wsname . '-user',
        'firstname' => 'Webservice',
        'lastname' => 'User (' . $wsname . ')',
        'email' => 'ws-' . $wsname . '-user@' . parse_url($CFG->wwwroot, PHP_URL_HOST),
        'auth' => 'webservice',
        'confirmed' => 1,
        'mnethostid' => $CFG->mnet_localhost_id,
    ]);
    cli_writeln('Webservice user "' . $wsusername . '" created (id=' . $webserviceuserid . ').');
}

// Create a webservice role.
$wsroleshortname = 'ws-' . $wsname . '-role';
$existingrole = $DB->get_record('role', ['shortname' => $wsroleshortname]);
if ($existingrole) {
    $wsroleid = $existingrole->id;
    cli_writeln('Webservice role "' . $wsroleshortname . '" already exists (id=' . $wsroleid . '), skipping creation.');
} else {
    $wsroleid = create_role('WS Role for ' . $wsname, 'ws-' . $wsname . '-role', '');
    cli_writeln('Webservice role "' . $wsroleshortname . '" created (id=' . $wsroleid . ').');
}
set_role_contextlevels($wsroleid, [CONTEXT_SYSTEM]);
assign_capability('webservice/rest:use', CAP_ALLOW, $wsroleid, $systemcontext->id, true);

foreach ($additionalcapabilities as $cap) {
    assign_capability($cap, CAP_ALLOW, $wsroleid, $systemcontext->id, true);
}

// Assign the webservice user to the webservice role in system context.
role_assign($wsroleid, $webserviceuserid, $systemcontext->id);

$webservicemanager = new webservice();
$service = $webservicemanager->get_external_service_by_shortname($wsname);

// Authorize the user to use the service.
$authorisedusers = $webservicemanager->get_ws_authorised_users($service->id);
$alreadyauthorised = false;
foreach ($authorisedusers as $authoriseduser) {
    if ($authoriseduser->id == $webserviceuserid) {
        $alreadyauthorised = true;
        break;
    }
}
if ($alreadyauthorised) {
    cli_writeln('User ' . $webserviceuserid . ' is already authorised for ' . $wsname . ', skipping authorisation.');
} else {
    $webservicemanager->add_ws_authorised_user(
        (object) ['externalserviceid' => $service->id, 'userid' => $webserviceuserid]
    );
    cli_writeln('User ' . $webserviceuserid . ' authorised for ' . $wsname . '.');
}

// Create a token for the user and service.
$existingtoken = $DB->get_record('external_tokens', [
    'userid' => $webserviceuserid,
    'externalserviceid' => $service->id,
    'tokentype' => EXTERNAL_TOKEN_PERMANENT,
]);
if ($existingtoken) {
    cli_writeln('Token for ' . $wsname . ' already exists, skipping creation.');
} else {
    $token = external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id, $webserviceuserid, $systemcontext);
    cli_writeln('Token for ' . $wsname . ' created: ' . $token .
        ' - MAKE SURE TO COPY THE TOKEN BECAUSE IT WILL NEVER BE SHOWN AGAIN!' . "\n");
}

$service = $webservicemanager->get_external_service_by_id($service->id);
$webservicemanager->update_external_service($service);
