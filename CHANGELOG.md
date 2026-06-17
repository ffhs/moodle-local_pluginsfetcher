# Changelog

## Version v5.2-r1 (2026061700)

### Added
- Add support for Moodle 5.2

### Fixed
- Fix parameter type for weekly Moodle versions

### Changed
- Update README
- Update Moodle Plugin CI for Moodle 5.2

## Version v5.1-r1 (2025103000)

### Added
- Add git ignore file, changelog, license, and PHP coding style
- Provide German translation
- Provide Unit tests
- Add webservice function local_pluginsfetcher_get_info to fetch more information about the plugin
- Added support for Moodle 5.1

### Fixed
- Fix PHP coding style
- Patch Moodle <= 4.1 external lib API changes

### Changed
- Update Moodle Plugin CI GitHub actions workflow
- Refactor webservice code
- Move local_pluginsfetcher_get_information to a legacy webservice
- Update and extend README

### Upgrade notes
The default webservice function local_pluginsfetcher_get_information has changed to local_pluginsfetcher_get_info.
Please update the url to fetch plugin information.
If you want to use the old webservice function, please enable the legacy webservice.
