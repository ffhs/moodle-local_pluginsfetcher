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
 * API endpoint to get plugin and software insights.
 */
class get_info extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters Description of the parameters accepted by the function
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
     * @return external_description Description of the return structure
     */
    public static function execute_returns(): external_description {
        return new external_single_structure([
            'plugins' => new external_multiple_structure(
                new external_single_structure(
                    [
                        'type' => new external_value(
                            PARAM_TEXT,
                            'Type of the plugin (e.g., mod, local, block, ...)',
                            VALUE_REQUIRED
                        ),
                        'name' => new external_value(
                            PARAM_TEXT,
                            'Internal name of the plugin (e.g., quiz)',
                            VALUE_REQUIRED
                        ),
                        'displayname' => new external_value(
                            PARAM_TEXT,
                            'Human-readable name of the plugin (e.g. "Quiz")',
                            VALUE_REQUIRED
                        ),
                        'version' => new external_value(
                            PARAM_TEXT,
                            'Version number of the installed plugin (e.g., 2025010100) from the database',
                            VALUE_OPTIONAL,
                            null,
                            NULL_ALLOWED
                        ),
                        'release' => new external_value(
                            PARAM_TEXT,
                            'Release identifier of the plugin (e.g., 3.11.0)',
                            VALUE_OPTIONAL,
                            null,
                            NULL_ALLOWED
                        ),
                        'requires' => new external_value(
                            PARAM_INT,
                            'Moodle version required by the plugin (e.g., 2022112800)',
                            VALUE_OPTIONAL,
                            null,
                            NULL_ALLOWED
                        ),
                        'supported' => new external_multiple_structure(
                            new external_value(
                                PARAM_INT,
                                'Supported Moodle versions (e.g., 401, 500)',
                                VALUE_REQUIRED
                            ),
                            '',
                            VALUE_OPTIONAL,
                        ),
                        'isstandard' => new external_value(
                            PARAM_BOOL,
                            'Whether the plugin is a standard Moodle plugin (true) or a contributed plugin (false)',
                            VALUE_REQUIRED
                        ),
                        'status' => new external_value(
                            PARAM_TEXT,
                            'Status of the plugin. One of core_plugin_manager::PLUGIN_STATUS_*',
                            VALUE_OPTIONAL,
                            null,
                            NULL_ALLOWED
                        ),
                    ]
                ),
                'Info about installed plugins',
                VALUE_REQUIRED
            ),
            'pluginstats' => new external_single_structure(
                [
                    'total' => new external_value(
                        PARAM_INT,
                        'Total number of installed plugins',
                        VALUE_REQUIRED
                    ),
                    'standard' => new external_value(
                        PARAM_INT,
                        'Number of standard Moodle plugins installed',
                        VALUE_REQUIRED
                    ),
                    'contrib' => new external_value(
                        PARAM_INT,
                        'Number of contributed plugins installed',
                        VALUE_REQUIRED
                    ),
                ],
            ),
            'software' => new external_single_structure(
                [
                    'moodle' => new external_single_structure(
                        [
                            'version' => new external_value(
                                PARAM_TEXT,
                                'Moodle version number (e.g., 2022112800)',
                                VALUE_OPTIONAL
                            ),
                            'release' => new external_value(
                                PARAM_TEXT,
                                'Moodle release identifier (e.g., 4.1)',
                                VALUE_OPTIONAL
                            ),
                            'branch' => new external_value(
                                PARAM_INT,
                                'Moodle branch number (e.g., 401 for 4.1)',
                                VALUE_OPTIONAL
                            ),
                        ],
                        '',
                        VALUE_OPTIONAL
                    ),
                    'php' => new external_single_structure(
                        [
                            'version' => new external_value(
                                PARAM_TEXT,
                                'PHP version in human-readable format (e.g., 8.1.0)',
                                VALUE_REQUIRED
                            ),
                            'versionid' => new external_value(
                                PARAM_TEXT,
                                'PHP release identifier (e.g., 80100)',
                                VALUE_REQUIRED
                            ),
                        ],
                        '',
                        VALUE_OPTIONAL
                    ),
                    'db' => new external_single_structure(
                        [
                            'type' => new external_value(
                                PARAM_TEXT,
                                'Database type used by Moodle (e.g., mysql, pgsql)',
                                VALUE_OPTIONAL
                            ),
                        ],
                        '',
                        VALUE_OPTIONAL
                    ),
                    'os' => new external_single_structure(
                        [
                            'name' => new external_value(
                                PARAM_TEXT,
                                'Operating system name',
                                VALUE_OPTIONAL
                            ),
                            'family' => new external_value(
                                PARAM_TEXT,
                                'Operating system family',
                                VALUE_OPTIONAL
                            ),
                        ],
                        '',
                        VALUE_OPTIONAL
                    ),
                ],
                'Info about installed software',
                VALUE_OPTIONAL
            ),
        ]);
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
        $res = [
            'plugins' => $pluginstats['plugins'],
            'pluginstats' => $pluginstats['stats'],
        ];

        $softwarestats = \local_pluginsfetcher\collector::get_software_stats();
        if ($softwarestats !== []) {
            $res['software'] = $softwarestats;
        }

        return $res;
    }
}
