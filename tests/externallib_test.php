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
 * External functions unit tests
 *
 * @package   local_pluginsfetcher
 * @copyright 2019 Adrian Perez <p.adrian@gmx.ch> {@link https://adrianperez.me}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/webservice/tests/helpers.php');
require_once($CFG->dirroot.'/local/pluginsfetcher/externallib.php');

/**
 * Class local_pluginsfetcher_external_testcase.
 */
class local_pluginsfetcher_external_testcase extends externallib_advanced_testcase
{

    /**
     * Test get all plugins information.
     */
    public function test_get_information_all() {
        $this->resetAfterTest(true);

        // Set the required capabilities by the external function.
        $contextid = context_system::instance()->id;
        $roleid = $this->assignUserCapability('moodle/site:config', $contextid);

        $params = array('type' => '', 'contribonly' => '0');

        $returnvalue = local_pluginsfetcher_external::get_information($params['type'], $params['contribonly']);

        // We need to execute the return values cleaning process to simulate the web service server.
        $returnvalue = external_api::clean_returnvalue(local_pluginsfetcher_external::get_information_returns(),
            $returnvalue);

        $this->assertEquals('mod_assign', $returnvalue[0]['type'].'_'.$returnvalue[0]['name']);

        // Call without required capability.
        $this->unassignUserCapability('moodle/site:config', $contextid, $roleid);
        $this->expectException(required_capability_exception::class);
        $returnvalue = local_pluginsfetcher_external::get_information($params['type'], $params['contribonly']);
    }

    /**
     * Test get plugins information by type.
     */
    public function test_get_information_by_type() {
        $this->resetAfterTest(true);

        $contextid = context_system::instance()->id;
        $this->assignUserCapability('moodle/site:config', $contextid);

        $params = array('type' => 'report', 'contribonly' => '0');

        $returnvalue = local_pluginsfetcher_external::get_information($params['type'], $params['contribonly']);

        $returnvalue = external_api::clean_returnvalue(local_pluginsfetcher_external::get_information_returns(),
            $returnvalue);

        $this->assertEquals('report_backups', $returnvalue[0]['type'].'_'.$returnvalue[0]['name']);

        $params['type'] = 'block';

        $returnvalue = local_pluginsfetcher_external::get_information($params['type'], $params['contribonly']);

        $returnvalue = external_api::clean_returnvalue(local_pluginsfetcher_external::get_information_returns(),
            $returnvalue);

        $this->assertEquals('block_activity_modules', $returnvalue[0]['type'].'_'.$returnvalue[0]['name']);
    }

    /**
     * Test get plugins information by 3rd party plugins.
     */
    public function test_get_information_by_contribonly() {
        $this->resetAfterTest(true);

        $contextid = context_system::instance()->id;
        $this->assignUserCapability('moodle/site:config', $contextid);

        $params = array('type' => '', 'contribonly' => '1');

        $returnvalue = local_pluginsfetcher_external::get_information($params['type'], $params['contribonly']);

        $returnvalue = external_api::clean_returnvalue(local_pluginsfetcher_external::get_information_returns(),
            $returnvalue);

        $this->assertCount(1, $returnvalue);
        $this->assertEquals('local_pluginsfetcher', $returnvalue[0]['type'].'_'.$returnvalue[0]['name']);
    }
}
