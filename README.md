# Out-Of-Band ownCloud Activity

## Description
The oob_activity app modifies the original app [activity](https://github.com/owncloud/activity),  provided for ownCloud. 
oob_activity allow to register user activity in external storage database or syslog.
The oob_activity app was only tested in Ubuntu 14.04 and PostgreSQL 9.3.6.

User activity types:
- created self ("You created A.txt").
- created by ("The SomeUser created A.txt").
- created public ("A.txt was created in a public folder").
- changed self ("You changed A.txt").
- changed by ("SomeUser changed A.txt").
- deleted self ("You deleted A.txt").
- deleted_by ("A.txt deleted by SomeUser").
- restored self ("You restored A.txt").
- restored by ("SomeUser restored A.txt").
- shared user self ("You shared A.txt with SomeUser").
- shared group self ("You shared A.txt with SomeGroup").
- shared with by  ("SomeUser shared A.txt with you").
- shared link self ("You shared A.txt via link").

## Download

v1.3 [download](https://github.com/Malinoski/oob_activity/releases/tag/oob_activity-v1.3)
- owncloud version: 8.0.5
- Notes:
	- syslog register enhanced
	- The oob_activity doesn't require the removal of the orginal app Activity. Is possible to use both apps.
	- [TODO] Disabled the external database register.

v1.2.1 [download](https://github.com/Malinoski/oob_activity/releases/tag/v1.2.1)
- owncloud version: 7.0.4
- Notes: 
	- The external storage was configured to use database or syslog (default). The configuration can be done in [owncloud home]/apps/activity/config/config.xml.

v1.2 [download](https://github.com/Malinoski/oob_activity/releases/tag/v1.2)
- owncloud version: 7.0.4
- Notes: 
	- Enhanced the feature for user activity in syslog. This feature can be enable or disable in configuration file ([owncloud home]/apps/activity/config/config.xml).

v1.1 [download](https://github.com/Malinoski/oob_activity/releases/tag/v1.1)
- owncloud version: 7.0.4
- Notes: 
	- Added the configuration file [owncloud home]/apps/activity/config/config.xml.
	- Added the feature to register user activity in log files optionally through configuration file.

v1.0 [download](https://github.com/Malinoski/oob_activity/releases/tag/v1.0)
- owncloud version: 7.0.4
- Notes: 
	- First version.

## Install

This section shows how to install oob_activity v1.3 (for old releases check the respective README.txt).

1. Backup your ownCloud and database servers.

2. Get and configure:
  * Download and extract the last release.
  * Rename the extracted folder to "oobactivity".
  * Move "oobactivity" folder to the ownCloud's app folder (`/[path_to_owncloud]/apps/`).
  * Grant privilegies to "oobactivity" folder.
<br/> (ex.: `chown -R www-data:www-data /[path_to_owncloud]/apps/oobactivity`).

3. Configure your ownCloud server:
  * Login as administrator in your ownCloud server (ex.: http://[localhost]/[owncloud])
  * Activate the oobactivity app. (ex.: `http://[localhost]/[owncloud]/index.php/settings/apps?installed`).

## Acknowledgements
This development has been funded by [FINEP](http://www.finep.gov.br), the Brazilian Innovation Agency.
