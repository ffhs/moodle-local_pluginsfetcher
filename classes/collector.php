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
 * @copyright 2025 Niels Gandraß <niels@gandrass.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pluginsfetcher;

// @codingStandardsIgnoreLine
defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore


/**
 * Collects plugin statistics and software version information.
 */
class collector {
    /**
     * Gathers statistics about installed plugins and their versions.
     *
     * @param string|null $type The type of plugins to filter by (e.g., 'mod',
     * 'block', etc.). If null, all types are included.
     * @param bool $contribonly If true, only 3rd-party plugins are included.
     *
     * @return array Plugin details and global plugin statistics.
     */
    public static function get_plugin_stats(?string $type = null, bool $contribonly = false): array {
        // Retrieve all plugin info objects.
        $plugman = \core_plugin_manager::instance();
        $plugins = $plugman->get_plugins();

        // Prepare response.
        $res = [
            'stats' => [
                'total' => 0,
                'standard' => 0,
                'contrib' => 0,
            ],
            'plugins' => [],
        ];

        // Visit all plugin types.
        foreach ($plugins as $curtype => $pluginsoftype) {
            // Filter by type if requested.
            if ($type !== null && $curtype != $type) {
                continue;
            }

            // Retrieve info for each plugin of this type.
            foreach ($pluginsoftype as $curname => $plugin) {
                if (self::is_excluded_plugin($plugin->component)) {
                    continue;
                }

                // Filter out standard plugins if requested.
                if ($contribonly && $plugin->is_standard()) {
                    continue;
                }

                // Add plugin info.
                $plugininfo = [
                    'type' => $plugin->type,
                    'name' => $plugin->name,
                    'displayname' => $plugin->displayname,
                    'isstandard' => $plugin->is_standard(),
                ];
                if (self::is_plugin_version_enabled()) {
                    $plugininfo += [
                        'version' => $plugin->versiondb,
                        'release' => $plugin->release,
                        'requires' => $plugin->versionrequires,
                        'supported' => $plugin->pluginsupported ?? [],
                        'status' => $plugin->get_status(),
                    ];
                }
                $res['plugins'][$plugin->component] = $plugininfo;

                // Update stats.
                $res['stats']['total']++;
                if ($plugin->is_standard()) {
                    $res['stats']['standard']++;
                } else {
                    $res['stats']['contrib']++;
                }
            }
        }

        return $res;
    }

    /**
     * Gathers Moodle, PHP, and other software version information.
     *
     * @return array
     */
    public static function get_software_stats(): array {
        global $CFG;

        $softwarestats = [];
        if (self::is_software_option_enabled('show_moodle_version')) {
            $softwarestats['moodle']['version'] = $CFG->version;
        }
        if (self::is_software_option_enabled('show_moodle_release')) {
            $softwarestats['moodle']['release'] = $CFG->release;
        }
        if (self::is_software_option_enabled('show_moodle_branch')) {
            $softwarestats['moodle']['branch'] = $CFG->branch;
        }
        if (self::is_software_option_enabled('show_php_version')) {
            $softwarestats['php'] = [
                'version' => phpversion(),
                'versionid' => PHP_VERSION_ID,
            ];
        }
        if (self::is_software_option_enabled('show_database_system')) {
            $softwarestats['db']['type'] = $CFG->dbtype;
        }
        if (self::is_software_option_enabled('show_os_system')) {
            $softwarestats['os'] = [
                'name' => PHP_OS,
                'family' => PHP_OS_FAMILY,
            ];
        }

        return $softwarestats;
    }

    /**
     * Checks whether a plugin matches a configured exclusion pattern.
     *
     * @param string $component Plugin component name.
     * @return bool
     */
    private static function is_excluded_plugin(string $component): bool {
        $configured = (string) get_config('local_pluginsfetcher', 'excluded_plugins');
        // Split by newlines and trim whitespace.
        $patterns = array_filter(array_map('trim', preg_split('/\R/', trim($configured), -1, PREG_SPLIT_NO_EMPTY)));
        foreach ($patterns as $pattern) {
            if (fnmatch(strtolower($pattern), strtolower($component))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks whether a software statistic option is enabled.
     *
     * @param string $option Configuration name.
     * @return bool
     */
    private static function is_software_option_enabled(string $option): bool {
        $value = get_config('local_pluginsfetcher', $option);
        return $value === false || (bool) $value;
    }

    /**
     * Checks whether plugin version information should be displayed.
     * @return bool
     */
    private static function is_plugin_version_enabled(): bool {
        $value = get_config('local_pluginsfetcher', 'show_plugin_versions');
        return $value === false || (bool) $value;
    }
}
