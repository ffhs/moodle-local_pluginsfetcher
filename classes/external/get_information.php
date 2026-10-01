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
 * This file defines the get_info webservice function
 *
 * @package   local_pluginsfetcher
 * @copyright 2019 Adrian Perez <me@adrianperez.me> {@link https://adrianperez.me}
 * @copyright 2025 Niels Gandraß <niels@gandrass.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pluginsfetcher\external;

defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore


// TODO (MDL-0): Remove after deprecation of Moodle 4.1 (LTS) on 08-12-2025.
require_once($CFG->dirroot . '/local/pluginsfetcher/patch_401_class_renames.php'); // @codeCoverageIgnore

use core_external\external_api;
use core_external\external_description;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Legacy API endpoint to get information about installed plugins.
 */
class get_information extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'type' => new external_value(
                PARAM_TEXT,
                'Filter returned plugins by type (e.g., mod, local, block, etc.)',
                VALUE_DEFAULT,
                null
            ),
            'contribonly' => new external_value(
                PARAM_BOOL,
                'If true, only return installed 3rd-party plugins (exclude standard Moodle plugins)',
                VALUE_DEFAULT,
                false
            ),
        ]);
    }

    /**
     * Returns description of return parameters
     *
     * @return external_description
     */
    public static function execute_returns(): external_description {
        return new external_multiple_structure(
            new external_single_structure([
                'type' => new external_value(PARAM_TEXT, 'The type'),
                'name' => new external_value(PARAM_TEXT, 'The name'),
                'versiondb' => new external_value(PARAM_TEXT, 'The installed version', VALUE_OPTIONAL, null, NULL_ALLOWED),
                'release' => new external_value(PARAM_TEXT, 'The installed release', VALUE_OPTIONAL, null, NULL_ALLOWED),
            ]),
            'plugins'
        );
    }

    /**
     * Execute the webservice function
     *
     * @param string|null $typeraw Filter plugins by type (e.g., 'mod', 'block',etc.). If null, all plugins are returned.
     * @param bool $contribonlyraw If true, only return contributed plugins (exclude standard Moodle plugins).
     *
     * @return array Webservice response containing plugin and software stats
     * @throws \dml_exception On database errors
     * @throws \required_capability_exception If the user is not allowed to view the Moodle site config
     * @throws \invalid_parameter_exception If the request parameters are invalid
     */
    public static function execute(?string $typeraw, bool $contribonlyraw): array {
        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'type' => $typeraw,
            'contribonly' => $contribonlyraw,
        ]);

        // Check for capabilities.
        $context = \context_system::instance();
        require_capability('moodle/site:config', $context);

        // Build response.
        $pluginstats = \local_pluginsfetcher\collector::get_plugin_stats($params['type'], $params['contribonly']);

        return array_map(
            fn($plugin) => [
                'type' => $plugin['type'],
                'name' => $plugin['name'],
                'versiondb' => $plugin['version'] ?? null,
                'release' => $plugin['release'] ?? null,
            ],
            $pluginstats['plugins']
        );
    }
}
