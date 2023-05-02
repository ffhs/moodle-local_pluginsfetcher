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

namespace local_pluginsfetcher;

use context_system;
use externallib_advanced_testcase;
use local_pluginsfetcher_external;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/webservice/tests/helpers.php');

/**
 * Class local_pluginsfetcher_external_testcase.
 *
 * @runTestsInSeparateProcesses
 * @coversDefaultClass \local_pluginsfetcher_external
 */
class externallib_test extends externallib_advanced_testcase {
    /**
     * @var array
     */
    private $params;
    /**
     * @var int
     */
    private $contextid;
    /**
     * @var int
     */
    private $roleid;

    /**
     * Test get all plugins information.
     *
     * @covers ::get_information
     */
    public function test_get_information_all() {
        $this->params = ['type' => '', 'contribonly' => '0'];

        $plugins = $this->init_test_and_capabilities_and_get_information();

        $this->assertIsArray($plugins);

        $this->assertTrue($this->contains_plugin($plugins, 'mod_assign'));

        $returnvalue = $this->remove_capabilities_and_get_information();
    }

    /**
     * Test get plugins information by type.
     *
     * @covers ::get_information
     */
    public function test_get_information_by_type() {
        $this->params = ['type' => 'report', 'contribonly' => '0'];

        $plugins = $this->init_test_and_capabilities_and_get_information();

        $this->assertIsArray($plugins);
        $this->assertTrue($this->contains_plugin($plugins, 'report_backups'));
        $this->assertFalse($this->contains_plugin($plugins, 'block_accessreview'));

        $this->params['type'] = 'block';
        $plugins = $this->get_cleaned_information();

        $this->assertIsArray($plugins);
        $this->assertFalse($this->contains_plugin($plugins, 'report_backups'));
        $this->assertTrue($this->contains_plugin($plugins, 'block_accessreview'));

        $returnvalue = $this->remove_capabilities_and_get_information();
    }

    /**
     * Test get plugins information by contribonly.
     *
     * @covers ::get_information
     */
    public function test_get_information_by_contribonly() {
        $this->params = ['type' => '', 'contribonly' => '1'];

        $plugins = $this->init_test_and_capabilities_and_get_information();

        $this->assertIsArray($plugins);
        $this->assertTrue($this->contains_plugin($plugins, 'local_pluginsfetcher'));
        $this->assertFalse($this->contains_plugin($plugins, 'report_backups'));

        $returnvalue = $this->remove_capabilities_and_get_information();
    }

    /**
     * Init test, set capabilities and get information.
     *
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     */
    protected function init_test_and_capabilities_and_get_information() {
        $this->resetAfterTest(true);

        // Set the required capabilities by the external function.
        $this->contextid = context_system::instance()->id;
        $this->roleid = $this->assignUserCapability('moodle/site:config', $this->contextid);

        return $this->get_cleaned_information();
    }

    /**
     * Call the webservice and return cleaned values.
     *
     * @return array|bool|mixed
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \invalid_response_exception
     * @throws \required_capability_exception
     */
    protected function get_cleaned_information() {
        global $CFG;

        require_once($CFG->dirroot.'/local/pluginsfetcher/externallib.php');
        $returnvalue = local_pluginsfetcher_external::get_information($this->params['type'], $this->params['contribonly']);

        // We need to execute the return values cleaning process to simulate the web service server.
        return \core_external\external_api::clean_returnvalue(local_pluginsfetcher_external::get_information_returns(),
            $returnvalue);
    }

    /**
     * Remove capabilities and get information.
     *
     * @return array|string
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     */
    protected function remove_capabilities_and_get_information() {
        // Call without required capability.
        $this->unassignUserCapability('moodle/site:config', $this->contextid, $this->roleid);
        $this->expectException(\required_capability_exception::class);

        return local_pluginsfetcher_external::get_information($this->params['type'], $this->params['contribonly']);
    }

    /**
     * Check if a plugin is in a list.
     *
     * @param $list
     * @param $pluginname
     * @return bool
     */
    protected function contains_plugin($list, $pluginname) {
        foreach ($list as $plugin) {
            if ($plugin['type'] . '_' . $plugin['name'] == $pluginname) {
                return true;
            }
        }
        return false;
    }
}
