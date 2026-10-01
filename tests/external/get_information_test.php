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
 * Tests for the get_information external service
 *
 * @package   local_pluginsfetcher
 * @copyright 2025 Niels Gandraß <niels@gandrass.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_pluginsfetcher\external;


/**
 * Tests for the get_information external service
 */
final class get_information_test extends \advanced_testcase {
    /**
     * Test that users without the required capabilities are rejected
     *
     * @covers \local_pluginsfetcher\external\get_information::execute
     *
     * @return void
     * @throws \dml_exception
     * @throws \required_capability_exception
     * @throws \invalid_parameter_exception
     */
    public function test_capability_requirement(): void {
        // Create job.
        $this->resetAfterTest();

        // Check that a user without the required capability is rejected.
        $this->expectException(\required_capability_exception::class);
        get_information::execute(null, false);
    }

    /**
     * Tests that the parameter spec is specified correctly and produces no exception.
     *
     * @covers \local_pluginsfetcher\external\get_information::execute_parameters
     *
     * @return void
     */
    public function test_assure_execute_parameter_spec(): void {
        $this->resetAfterTest();
        $this->assertInstanceOf(
            \core_external\external_function_parameters::class,
            get_information::execute_parameters(),
            'The execute_parameters() method should return an external_function_parameters.'
        );
    }

    /**
     * Tests that the return parameters are specified correctly and produce no exception.
     *
     * @covers \local_pluginsfetcher\external\get_information::execute_returns
     *
     * @return void
     */
    public function test_assure_return_parameter_spec(): void {
        $this->assertInstanceOf(
            \core_external\external_description::class,
            get_information::execute_returns(),
            'The execute_returns() method should return an external_description.'
        );
    }

    /**
     * Tests the execution of the webservice function.
     *
     * @covers \local_pluginsfetcher\external\get_information::execute
     *
     * @return void
     * @throws \dml_exception
     * @throws \required_capability_exception
     * @throws \invalid_parameter_exception
     */
    public function test_execution(): void {
        // Gain webservice permission.
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('show_plugin_versions', 1, 'local_pluginsfetcher');
        set_config('excluded_plugins', '', 'local_pluginsfetcher');

        // Call the webservice function.
        $result = get_information::execute(null, false);

        // Check that the result is an array.
        $this->assertIsArray($result, 'The result should be an array.');

        // Check that each record matches the expected structure.
        foreach ($result as $record) {
            $this->assertArrayHasKey('type', $record, 'Each record should have a "type" key.');
            $this->assertArrayHasKey('name', $record, 'Each record should have a "name" key.');
            $this->assertArrayHasKey('versiondb', $record, 'Each record should have a "versiondb" key.');
            $this->assertArrayHasKey('release', $record, 'Each record should have a "release" key.');
        }
    }
}
