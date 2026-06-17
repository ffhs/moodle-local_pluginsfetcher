# Moodle Plugins Fetcher

[![Latest Version](https://img.shields.io/github/v/release/ffhs/moodle-local_pluginsfetcher)](https://github.com/ffhs/moodle-local_pluginsfetcher/releases)
[![PHP Support](https://img.shields.io/badge/PHP-7.4%20--%208.4-blue)](https://github.com/ffhs/moodle-local_pluginsfetcher)
[![Moodle Support](https://img.shields.io/badge/Moodle-4.1%20--%205.0-orange)](https://github.com/ffhs/moodle-local_pluginsfetcher)
[![GitHub Workflow Status: Moodle Plugin CI](https://img.shields.io/github/actions/workflow/status/ffhs/moodle-local_pluginsfetcher/moodle-plugin-ci.yml?label=Moodle%20Plugin%20CI)](https://github.com/ffhs/moodle-local_pluginsfetcher/actions/workflows/moodle-plugin-ci.yml)
[![Code Coverage](https://img.shields.io/coverallsCoverage/github/ffhs/moodle-local_pluginsfetcher)](https://coveralls.io/github/ffhs/moodle-local_pluginsfetcher)
[![GitHub Issues](https://img.shields.io/github/issues/ffhs/moodle-local_pluginsfetcher)](https://github.com/ffhs/moodle-local_pluginsfetcher/issues)
[![GitHub Pull Requests](https://img.shields.io/github/issues-pr/ffhs/moodle-local_pluginsfetcher)](https://github.com/ffhs/moodle-local_pluginsfetcher/pulls)
[![Maintenance Status](https://img.shields.io/maintenance/yes/9999)](https://github.com/ffhs/moodle-local_pluginsfetcher/)
[![License](https://img.shields.io/github/license/ffhs/moodle-local_pluginsfetcher)](https://github.com/ffhs/moodle-local_pluginsfetcher/blob/master/LICENSE)
[![GitHub Stars](https://img.shields.io/github/stars/ffhs/moodle-local_pluginsfetcher?style=social)](https://github.com/ffhs/moodle-local_pluginsfetcher/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/ffhs/moodle-local_pluginsfetcher?style=social)](https://github.com/ffhs/moodle-local_pluginsfetcher/network/members)
[![GitHub Contributors](https://img.shields.io/github/contributors/ffhs/moodle-local_pluginsfetcher?style=social)](https://github.com/ffhs/moodle-local_pluginsfetcher/graphs/contributors)

This plugin allows to share information about installed plugins and software versions via a secure Moodle webservice
endpoint.


## Configuration and Usage

During installation, the plugin will create two new external services:

- `Plugins fetcher`: This is the primary service that allows you to fetch information about installed plugins and
  software versions.
- `Plugins fetcher (legacy)`: This external service provides a simple and backwards-compatible API for fetching basic
  information about installed plugins.
  - This service is disabled by default and must be enabled manually under _Site Administration > Server > External
    services_.

### Initial setup

To start using the plugin, you need to:

1. Add a new user to the list of _Authorised users_ for the `Plugins fetcher` service under _Site Administration >
   Server > External services_. 
2. Create a new web service token for the assigned user under _Site Administration > Plugins > Web services > Manage
   tokens_.

> **Use the cli install script to automatically perform the necessary steps**: 
> - Enable web services and REST protocol.
> - Create a webservice user
> - Create a webservice role with the necessary capabilities.
> - Assign the webservice user to the webservice role in system context.
> - Authorise the user to use the webservice.
> - Create a token for the user - the token is printed out, MAKE SURE TO COPY THE TOKEN BECAUSE IT WILL NEVER BE SHOWN AGAIN!
> ```
> php local/pluginsfetcher/cli/webservicesetup.php
> ```

### Example usage
```
curl "http://moodle.example.com/webservice/rest/server.php?wstoken=XXXXXXXXXXXX&wsfunction=local_pluginsfetcher_get_info&moodlewsrestformat=json"

curl "http://moodle.example.com/webservice/rest/server.php?wstoken=XXXXXXXXXXXX&wsfunction=local_pluginsfetcher_get_info&moodlewsrestformat=json&type=mod"

curl "http://moodle.example.com/webservice/rest/server.php?wstoken=XXXXXXXXXXXX&wsfunction=local_pluginsfetcher_get_info&moodlewsrestformat=json&contribonly=1"
```

### API documentation

You can find a full documentation of the API functions under _Site Administration > Server > Web services > API
Documentation_.


#### Example response

The `local_pluginsfetcher_get_info` web service function returns a JSON object with the following structure:

```json
{
    "plugins": [
        {
            "type": "mod",
            "name": "quiz",
            "displayname": "Quiz",
            "version": 2024100700,
            "release": null,
            "requires": 2024100100,
            "supported": [],
            "isstandard": true,
            "status": "uptodate"
        },
        {
            "type": "auth",
            "name": "email",
            "displayname": "Email-based self-registration",
            "version": 2024100700,
            "release": null,
            "requires": 2024100100,
            "supported": [],
            "isstandard": true,
            "status": "uptodate"
        },
        {
            "type": "local",
            "name": "pluginsfetcher",
            "displayname": "Plugins fetcher",
            "version": 2021052405,
            "release": "v3.11-r2",
            "requires": 2022112800,
            "supported": [
                401,
                500
            ],
            "isstandard": false,
            "status": "uptodate"
        },
        [...]
    ],
    "pluginstats": {
        "total": 461,
        "standard": 448,
        "contrib": 13
    },
    "software": {
        "moodle": {
            "version": 2024100703,
            "release": "4.5.3 (Build: 20250317)",
            "branch": 405
        },
        "php": {
            "version": "8.2.28",
            "versionid": "80228"
        },
        "db": {
            "type": "pgsql"
        },
        "os": {
            "name": "Linux",
            "family": "Linux"
        }
    }
}
```

#### Example response (legacy web service)

The `local_pluginsfetcher_get_information` web service function returns a JSON object with the following structure:

```json
[
    {
        "type": "mod",
        "name": "quiz",
        "version": 2024100700,
        "release": null
    },
    {
        "type": "auth",
        "name": "email",
        "version": 2024100700,
        "release": "null"
    },
    {
        "type": "local",
        "name": "pluginsfetcher",
        "version": 2021052405,
        "release": "v3.11-r2"
    },
    [...]
]
```



## Installation

This plugin can be installed like any other Moodle plugin by placing its source code inside your Moodle installation and
executing the upgrade routine afterward.


### Installing via the site administration (uploaded ZIP file)

1. Download the latest release of this plugin.

2. Log in to your Moodle site as an admin and go to _Site administration > Plugins > Install plugins_.
3. Upload the ZIP file with the plugin code.
4. Check the plugin validation report and finish the installation.


### Installing manually

The plugin can be also installed by putting the contents of this directory into

```
{your/moodle/dirroot}/local/pluginsfetcher
```

Afterwards, log in to your Moodle site as an admin and go to _Site administration > Notifications_ to complete the
installation.

Alternatively, you can run `php admin/cli/upgrade.php` from the command line to complete the installation.


## Reporting a bug or requesting a feature

If you find a bug or have a feature request, please open an issue via the [GitHub issue tracker](https://github.com/ffhs/moodle-local_pluginsfetcher/issues).

Please do not use the comments section within the Moodle plugin directory. Thanks :)


## Testing

You can find testing instructions for developers in the [TESTING.md](TESTING.md) file.

## Acknowledgements
A big thank you to everyone who contributed to this project:

- @lucaboesch and @ngandrass - for the plugin overhaul and API extension

## License

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
