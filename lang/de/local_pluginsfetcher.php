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
 * Plugin strings are defined here.
 *
 * @package   local_pluginsfetcher
 * @copyright 2019 Adrian Perez <p.adrian@gmx.ch> {@link https://adrianperez.me}
 * @copyright 2025 Niels Gandraß <niels@gandrass.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// @codingStandardsIgnoreFile

defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore

// General.
$string['pluginname'] = 'Pluginsfetcher';

// Capabilities.
$string['pluginsfetcher:view'] = 'Plugininformationen anzeigen';

// Privacy.
$string['privacy:metadata'] = 'Der Pluginsfetcher speichert keine personenbezogenen Daten. Er stellt lediglich Informationen bereit, die in anderen Teilen des Systems gespeichert sind.';

// Settings.
$string['settings:show_moodle_version'] = 'Moodle-Version ausgeben';
$string['settings:show_moodle_version_desc'] = 'Gibt die installierte Moodle-Version aus. Beispiel: `2024100713.01`.';
$string['settings:show_moodle_release'] = 'Moodle-Release ausgeben';
$string['settings:show_moodle_release_desc'] = 'Gibt das Moodle-Release aus. Beispiel: `4.5.13+ (Build: 20260818)`.';
$string['settings:show_moodle_branch'] = 'Moodle-Branch ausgeben';
$string['settings:show_moodle_branch_desc'] = 'Gibt die Moodle-Branch-Nummer aus. Beispiel: `405`.';
$string['settings:show_php_version'] = 'PHP-Version ausgeben';
$string['settings:show_php_version_desc'] = 'Gibt die genutzte PHP-Version und die PHP-Version-ID aus. Beispiel: `8.1.33 80133`.';
$string['settings:show_database_system'] = 'Datenbanksystem ausgeben';
$string['settings:show_database_system_desc'] = 'Gibt das genutzte Datenbanksystem aus. Beispiel: `mariadb`.';
$string['settings:show_os_system'] = 'Betriebssystem ausgeben';
$string['settings:show_os_system_desc'] = 'Gibt den Betriebssystem-Typ aus. Beispiel: `Linux`.';
$string['settings:show_plugin_versions'] = 'Informationen über Pluginversionen ausgeben';
$string['settings:show_plugin_versions_desc'] = 'Gibt die folgenden Informationen zu einem Plugin aus: `version`, `release`, `requires`, `supported`, `status`.';
$string['settings:excluded_plugins'] = 'Plugins oder Plugin Typen nicht ausgeben';
$string['settings:excluded_plugins_desc'] = 'Liste an Plugins oder Plugin Typen, die nicht ausgebenen werden sollen, z.B.
`local_pluginsfetcher` or `local_*`.<br />Ein Eintrag pro Zeile.';