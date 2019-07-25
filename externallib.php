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
 * External webservice template.
 *
 * @package   local_pluginsfetcher
 * @copyright 2019 Adrian Perez <p.adrian@gmx.ch> {@link https://adrianperez.me}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

class local_pluginsfetcher_external extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function get_information_parameters() {
        return new external_function_parameters(
            array(
                'type' => new external_value( PARAM_TEXT, 'The type of plugins to retrieve (optional).', false, null),
                'contribonly' => new external_value(PARAM_INT, 'Get only additional installed (optional)..', false, null)
            )
        );
    }

    /**
     * Returns plugins information.
     *
     * @param string $type
     * @param int|null $contribonly
     * @return string
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    public static function get_information($type, $contribonly) {

        $params = self::validate_parameters(self::get_information_parameters(),
            array(
                'type' => $type,
                'contribonly' => $contribonly
            )
        );

        $pluginman = core_plugin_manager::instance();

        if (!empty($params['type'])) {
            // Get all plugins by type.
            $plugininfo = $pluginman->get_plugins_of_type($type);

            if (!empty($params['contribonly'])) {
                $plugininfo = $pluginman->get_plugins();

                // Get additional plugins by type.
                $contribs = array();
                foreach ($plugininfo as $plugintype => $pluginnames) {
                    foreach ($pluginnames as $pluginname => $pluginfo) {
                        if ($pluginfo->type == $type && !$pluginfo->is_standard()) {
                            $contribs[$pluginname] = $pluginfo;
                        }
                    }
                }
                $plugininfo = $contribs;
            }
        } else {
            $plugininfo = $pluginman->get_plugins();

            if (!empty($params['contribonly'])) {
                // Get all additional plugins.
                $contribs = array();
                foreach ($plugininfo as $plugintype => $pluginnames) {
                    foreach ($pluginnames as $pluginname => $pluginfo) {
                        if (!$pluginfo->is_standard()) {
                            $contribs[$pluginname] = $pluginfo;
                        }
                    }
                }
                $plugininfo = $contribs;
            } else {
                // Get all plugins.
                $all = array();
                foreach ($plugininfo as $plugintype => $pluginnames) {
                    foreach ($pluginnames as $pluginname => $pluginfo) {
                        $all[$pluginname] = $pluginfo;
                    }
                }
                $plugininfo = $all;
            }
        }

        if (empty($plugininfo)) {
            return array();
        }

        $syscontext = context_system::instance();
        require_capability('moodle/site:config', $syscontext);

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
}
