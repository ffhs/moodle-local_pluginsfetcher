# Moodle Plugins Fetcher Plugin ![Moodle Plugin CI](https://github.com/ffhs/moodle-local_pluginsfetcher/workflows/Moodle%20Plugin%20CI/badge.svg) [![Coverage Status](https://coveralls.io/repos/github/ffhs/moodle-local_pluginsfetcher/badge.svg?branch=main)](https://coveralls.io/github/ffhs/moodle-local_pluginsfetcher?branch=main)

This plugin installs a new webservice `local_pluginsfetcher_get_information` which can be used to retrieve information about installed plugins.

## Main features

There are two optional parameters which can be combined to fetch only given data:

| key         | value  | description                             |
|-------------|--------|-----------------------------------------|
| type        | string | Only given plugin types will be fetched |
| contribonly | int    | Only additional plugins will be fetched |

## Installation
1. Copy this plugin to the `local` directory of your Moodle instance: `git clone https://github.com/ffhs/moodle-local_pluginsfetcher.git local/pluginsfetcher`
2. Visit the notifications page to complete the installation process

For more information, visit [MoodleDocs](https://docs.moodle.org/311/en/Installing_plugins#Installing_manually_at_the_server) for installing contributed modules and plugins.

## Configuration
1. Enable the pre-built service under [Site administration / Server / Web services / External services](https://FQDN/admin/settings.php?section=externalservices) with click on `Edit`
2. Add the user to be used under `Autorised users`
3. Create a `token` for this service `Plugins fetcher` and user under [Site administration / Server / Web services / Manage tokens](https://FQDN/admin/webservice/tokens.php?action=create). It's important that the user has the capability `moodle/site:config`.
