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
 * Definition of the prometheus exporter class.
 *
 * @package    local_pluginsfetcher
 * @copyright  2026 Melanie Treitinger <melanie.treitinger@ruhr-uni-bochum.de>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pluginsfetcher;

/**
 * Exports plugin and software statistics in Prometheus format.
 *
 * @link https://prometheus.io/docs/instrumenting/exposition_formats Prometheus format documentation
 *
 * @package    local_pluginsfetcher
 * @copyright  2026 Melanie Treitinger <melanie.treitinger@ruhr-uni-bochum.de>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exporter {
    /**
     * Exports the webservice result in Prometheus format.
     * @param array $pluginstats
     * @param array $softwarestats
     * @return string
     */
    public static function export(array $pluginstats, array $softwarestats): string {
        $lines = [
            '# HELP local_pluginsfetcher_plugins_total Number of installed Moodle plugins.',
            '# TYPE local_pluginsfetcher_plugins_total gauge',
        ];
        foreach ($pluginstats['stats'] as $category => $value) {
            $lines[] = self::format_value('local_pluginsfetcher_plugins_total', $value, ['category' => $category]);
        }

        $lines[] = '# HELP local_pluginsfetcher_plugin_info Installed Moodle plugin information.';
        $lines[] = '# TYPE local_pluginsfetcher_plugin_info gauge';
        foreach ($pluginstats['plugins'] as $component => $plugin) {
            $labels = [
                'component' => $component,
                'type' => $plugin['type'],
                'name' => $plugin['name'],
                'displayname' => $plugin['displayname'],
                'isstandard' => $plugin['isstandard'] ? 'true' : 'false',
            ];
            if (array_key_exists('version', $plugin)) {
                $labels += [
                    'version' => $plugin['version'],
                    'release' => $plugin['release'],
                    'requires' => $plugin['requires'],
                    'supported' => implode(',', $plugin['supported']),
                    'status' => $plugin['status'],
                ];
            }
            $lines[] = self::format_value('local_pluginsfetcher_plugin_info', 1, $labels);
        }

        if (isset($softwarestats['moodle'])) {
            $lines[] = '# HELP local_pluginsfetcher_moodle_info Moodle installation information.';
            $lines[] = '# TYPE local_pluginsfetcher_moodle_info gauge';
            $lines[] = self::format_value('local_pluginsfetcher_moodle_info', 1, $softwarestats['moodle']);
        }

        if (isset($softwarestats['php'])) {
            $lines[] = '# HELP local_pluginsfetcher_php_info PHP runtime information.';
            $lines[] = '# TYPE local_pluginsfetcher_php_info gauge';
            $lines[] = self::format_value('local_pluginsfetcher_php_info', 1, $softwarestats['php']);
        }

        if (isset($softwarestats['db'])) {
            $lines[] = '# HELP local_pluginsfetcher_database_info Database information.';
            $lines[] = '# TYPE local_pluginsfetcher_database_info gauge';
            $lines[] = self::format_value('local_pluginsfetcher_database_info', 1, $softwarestats['db']);
        }

        if (isset($softwarestats['os'])) {
            $lines[] = '# HELP local_pluginsfetcher_os_info Operating system information.';
            $lines[] = '# TYPE local_pluginsfetcher_os_info gauge';
            $lines[] = self::format_value('local_pluginsfetcher_os_info', 1, $softwarestats['os']);
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Formats the values.
     * @param string $name
     * @param int $value
     * @param array $labels
     * @return string
     */
    private static function format_value(string $name, int $value, array $labels = []): string {
        $labeltext = '';
        if ($labels) {
            $pairs = [];
            foreach ($labels as $labelname => $labelvalue) {
                $pairs[] = $labelname . '="' . self::escape_label_value((string) $labelvalue) . '"';
            }
            $labeltext = '{' . implode(',', $pairs) . '}';
        }
        return "{$name}{$labeltext} {$value}";
    }

    /**
     * Escapes a string for use as a Prometheus label value.
     *
     * @param string $value Raw label value.
     * @return string Escaped label value.
     */
    private static function escape_label_value(string $value): string {
        return strtr($value, ['\\' => '\\\\', '"' => '\\"', "\n" => '\\n']);
    }
}
