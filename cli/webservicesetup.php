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
 * This file defines the collector class.
 *
 * @package   local_pluginsfetcher
 * @copyright 2026 Melanie Treitinger ≤melanie.treitinger@ruhr-uni-bochum.de≥
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', 1);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir  . '/clilib.php');
require_once($CFG->dirroot . '/lib/externallib.php');
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

// Create a webservice user.
$webserviceuserid = user_create_user([
    'username' => 'ws-' . $wsname . '-user',
    'firstname' => 'Webservice',
    'lastname' => 'User (' . $wsname . ')',
    'email' => 'ws-' . $wsname . '-user@' . parse_url($CFG->wwwroot, PHP_URL_HOST),
    'auth' => 'webservice',
    'confirmed' => 1,
    'mnethostid' => $CFG->mnet_localhost_id,
]);

// Create a webservice role.
$wsroleid = create_role('WS Role for ' . $wsname, 'ws-' . $wsname . '-role', '');
set_role_contextlevels($wsroleid, [CONTEXT_SYSTEM]);
assign_capability('webservice/rest:use', CAP_ALLOW, $wsroleid, $systemcontext->id, true);

foreach ($additionalcapabilities as $cap) {
    assign_capability($cap, CAP_ALLOW, $wsroleid, $systemcontext->id, true);
}

// Assign the webservice user to the webservice role in system context.
role_assign($wsroleid, $webserviceuserid, $systemcontext->id);

$webservicemanager = new webservice();
$service = $webservicemanager->get_external_service_by_shortname($wsname);

// Authorise the user to use the service.
$webservicemanager->add_ws_authorised_user((object) ['externalserviceid' => $service->id, 'userid' => $webserviceuserid]);

// Create a token for the user.
$token = external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id, $webserviceuserid, $systemcontext);
cli_writeln("Token for $wsname created: $token - MAKE SURE TO COPY THE TOKEN BECAUSE IT WILL NEVER BE SHOWN AGAIN!\n");

$service = $webservicemanager->get_external_service_by_id($service->id);
$webservicemanager->update_external_service($service);
