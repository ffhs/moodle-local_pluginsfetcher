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

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/webservice/tests/helpers.php');
require_once($CFG->dirroot.'/local/pluginsfetcher/externallib.php');

/**
 * Class local_pluginsfetcher_external_testcase.
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
     */
    public function test_get_information_all() {
        $this->params = ['type' => '', 'contribonly' => '0'];

        $returnvalue = $this->init_test_and_capabilities_and_get_information();

        $this->assertEquals('mod_assign', $returnvalue[0]['type'].'_'.$returnvalue[0]['name']);

        $returnvalue = $this->remove_capabilities_and_get_information();
    }

    /**
     * Test get plugins information by type.
     */
    public function test_get_information_by_type() {
        $this->params = ['type' => 'report', 'contribonly' => '0'];

        $returnvalue = $this->init_test_and_capabilities_and_get_information();

        $this->assertEquals('report_backups', $returnvalue[0]['type'].'_'.$returnvalue[0]['name']);

        $this->params['type'] = 'block';
        $returnvalue = $this->get_cleaned_information();

        $this->assertEquals('block_accessreview', $returnvalue[0]['type'].'_'.$returnvalue[0]['name']);

        $returnvalue = $this->remove_capabilities_and_get_information();
    }

    /**
     * Test get plugins information by contribonly.
     */
    public function test_get_information_by_contribonly() {
        $this->params = ['type' => '', 'contribonly' => '1'];

        $returnvalue = $this->init_test_and_capabilities_and_get_information();

        $this->assertCount(1, $returnvalue);
        $this->assertEquals('local_pluginsfetcher', $returnvalue[0]['type'].'_'.$returnvalue[0]['name']);

        $returnvalue = $this->remove_capabilities_and_get_information();
    }

    /**
     * Test get plugins information by type and contribonly.
     */
    public function test_get_information_by_type_and_contribonly() {
        $this->params = ['type' => 'local', 'contribonly' => '1'];

        $returnvalue = $this->init_test_and_capabilities_and_get_information();

        $this->assertCount(1, $returnvalue);
        $this->assertEquals('local_pluginsfetcher', $returnvalue[0]['type'].'_'.$returnvalue[0]['name']);

        $returnvalue = $this->remove_capabilities_and_get_information();
    }

    /**
     * Init test, set capabilities and get information.
     *
     * @return array|bool|mixed
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws invalid_response_exception
     * @throws required_capability_exception
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
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws invalid_response_exception
     * @throws required_capability_exception
     */
    protected function get_cleaned_information() {
        $returnvalue = local_pluginsfetcher_external::get_information($this->params['type'], $this->params['contribonly']);

        // We need to execute the return values cleaning process to simulate the web service server.
        return external_api::clean_returnvalue(local_pluginsfetcher_external::get_information_returns(), $returnvalue);
    }

    /**
     * Remove capabilities and get information.
     *
     * @return array|string
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws required_capability_exception
     */
    protected function remove_capabilities_and_get_information() {
        // Call without required capability.
        $this->unassignUserCapability('moodle/site:config', $this->contextid, $this->roleid);
        $this->expectException(required_capability_exception::class);

        return local_pluginsfetcher_external::get_information($this->params['type'], $this->params['contribonly']);
    }
}
