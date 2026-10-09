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
 * Upgrade script.
 *
 * @package    local_pluginsfetcher
 * @copyright  2026 Melanie Treitinger <melanie.treitinger@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_pluginsfetcher.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_local_pluginsfetcher_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026061701) {
        $defaults = [
            'show_plugin_versions' => 1,
            'show_moodle_version' => 1,
            'show_moodle_release' => 1,
            'show_moodle_branch' => 1,
            'show_php_version' => 1,
            'show_database_system' => 1,
            'show_os_system' => 1,
            'excluded_plugins' => '',
        ];

        foreach ($defaults as $name => $value) {
            if (get_config('local_pluginsfetcher', $name) === false) {
                set_config($name, $value, 'local_pluginsfetcher');
            }
        }

        upgrade_plugin_savepoint(true, 2026061701, 'local', 'pluginsfetcher');
    }

    if ($oldversion < 2026100901) {
        upgrade_plugin_savepoint(true, 2026100901, 'local', 'pluginsfetcher');
    }

    if ($oldversion < 2026100904) {
        // Assign the dedicated capability to the webservice role.
        $cap = 'local/pluginsfetcher:view';
        if ($DB->record_exists('capabilities', ['name' => $cap])) {
            $roles = $DB->get_records_select(
                'role',
                $DB->sql_like('shortname', ':shortname'),
                ['shortname' => '%pluginsfetcher%']
            );
            $systemcontext = context_system::instance();
            foreach ($roles as $role) {
                assign_capability($cap, CAP_ALLOW, $role->id, $systemcontext->id, true);
            }
        }

        upgrade_plugin_savepoint(true, 2026100904, 'local', 'pluginsfetcher');
    }

    return true;
}
