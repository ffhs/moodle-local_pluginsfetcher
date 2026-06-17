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
 * Web service function declarations for the local_pluginsfetcher plugin.
 *
 * @package   local_pluginsfetcher
 * @copyright 2019 Adrian Perez <p.adrian@gmx.ch> {@link https://adrianperez.me}
 * @copyright 2025 Niels Gandraß <niels@gandrass.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// @codingStandardsIgnoreLine
defined('MOODLE_INTERNAL') || die(); // @codeCoverageIgnore

// We defined the web service functions to install.
$functions = [
    'local_pluginsfetcher_get_info' => [
        'classname' => 'local_pluginsfetcher\external\get_info',
        'description' => 'Retrieves information about installed plugins and used software versions.',
        'type' => 'read',
        'ajax' => true,
        'services' => [],
        'capabilities' => 'moodle/site:config',
    ],

    'local_pluginsfetcher_get_information' => [
        'classname' => 'local_pluginsfetcher\external\get_information',
        'description' => 'Legacy API to retrieve information about installed plugins.',
        'type' => 'read',
        'ajax' => true,
        'services' => [],
        'capabilities' => 'moodle/site:config',
    ],
];

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = [
    'Plugins fetcher' => [
        'shortname' => 'pluginsfetcher',
        'functions' => [
            'local_pluginsfetcher_get_info',
        ],
        'restrictedusers' => 1,
        'enabled' => 1,
    ],
    'Plugins fetcher (legacy)' => [
        'shortname' => 'pluginsfetcher_legacy',
        'functions' => [
            'local_pluginsfetcher_get_information',
        ],
        'restrictedusers' => 1,
        'enabled' => 0,
    ],
];
