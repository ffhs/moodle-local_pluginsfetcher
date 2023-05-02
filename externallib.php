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
 * Plugin fetcher API.
 *
 * @package   local_pluginsfetcher
 * @copyright 2019 Adrian Perez <me@adrianperez.me> {@link https://adrianperez.me}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External webservice functions.
 *
 * @package   local_pluginsfetcher
 * @copyright 2019 Adrian Perez <p.adrian@gmx.ch> {@link https://adrianperez.me}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_pluginsfetcher_external extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_information_parameters() {
        return new external_function_parameters(
            array(
                'type' => new external_value(PARAM_TEXT, 'The type of plugins to retrieve (optional).', VALUE_DEFAULT, null),
                'contribonly' => new external_value(PARAM_INT, 'Get only additional installed (optional)..', VALUE_DEFAULT, null)
            )
        );
    }

    /**
     * Returns plugins information.
     *
     * @param string|null $type The plugin type.
     * @param int|null $contribonly Include contributed plugins.
     * @return array|\core\plugininfo\base[]|string
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public static function get_information(?string $type, ?int $contribonly) {
        $syscontext = context_system::instance();
        require_capability('moodle/site:config', $syscontext);

        $params = self::validate_parameters(self::get_information_parameters(),
            array(
                'type' => $type,
                'contribonly' => $contribonly
            )
        );

        $pluginman = core_plugin_manager::instance();

        if (!empty($params['type'])) {
            if (!empty($params['contribonly'])) {
                // Get additional plugins by type and contrib.
                $plugininfo = self::get_plugins_by_parameters($pluginman, $type);
            } else {
                // Get all plugins by type.
                $plugininfo = $pluginman->get_plugins_of_type($type);
            }
        } else {
            if (!empty($params['contribonly'])) {
                // Get all plugins by contrib.
                $plugininfo = self::get_plugins_by_parameters($pluginman);
            } else {
                // Get all plugins.
                $plugininfo = self::get_plugins_by_parameters($pluginman, null, true);
            }
        }

        return $plugininfo;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_information_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'type' => new external_value(PARAM_TEXT, 'The type'),
                    'name' => new external_value(PARAM_TEXT, 'The name'),
                    'versiondb' => new external_value(PARAM_INT, 'The installed version'),
                    'release' => new external_value(PARAM_TEXT, 'The installed release')
                ), 'plugins'
            )
        );
    }

    /**
     * Retrieves plugin data based on type and contrib.
     *
     * @param object $pluginman The core plugin manager singleton instance.
     * @param string|null $type The plugin type.
     * @param bool $contribonly Include contributed plugins.
     * @return array
     */
    protected static function get_plugins_by_parameters(object $pluginman, $type = null, $contribonly = false): array {
        $plugins = array();
        $plugininfo = $pluginman->get_plugins();

        foreach ($plugininfo as $plugintype => $pluginnames) {
            foreach ($pluginnames as $pluginname => $plugin) {
                if ($contribonly || ($plugin->type == $type && !$plugin->is_standard()) ||
                    (is_null($type) && !$plugin->is_standard())) {
                    $key = $plugintype . '_' . $pluginname;
                    $plugins[$key] = $plugin;
                }
            }
        }

        return $plugins;
    }
}
