# Out-Of-Band ownCloud Activity

## Description
The oob_activity app modifies the original app [activity](https://github.com/owncloud/activity),  provided for ownCloud. 
oob_activity allow to register user activity in syslog.
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

v1.3.1 [download](https://github.com/Malinoski/oob_activity/releases/tag/oob_activity-v1.3.1)
- owncloud version: 8.2.3
- Requirements: PostgreSQL
- Notes:
	- External database register feature was added again.
	- File configuration, which allow activity registry for one or both register types (syslog or/and external database). Se in [owncloud home]/apps/activity/config/config.xml.
	- Some debug mode, enabled in [owncloud home]/apps/activity/config/config.xml.
	- Is no more required uninstall the default activity app, both can be used.

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

Modify the brackets `[ ]` in the examples below.

1. Backup your ownCloud and database servers.

2. Get and configure:
  * Download and extract the last release.
  * Rename the extracted folder to "ooba".
  * Move above folder to the ownCloud's app (`/[path_to_owncloud]/apps/`).
  * Grant privilegies to "ooba" folder.
<br/> (ex.: `chown -R www-data:www-data /[path_to_owncloud]/apps/ooba`).
  * Allow access to syslog files in owncloud server. 
<br/> (ex.: `sudo chmod +r /var/log/syslog*`).

3. Configure your ownCloud server:
  * Login as administrator in your ownCloud server (ex.: http://[localhost]/[owncloud])
  * Activate the oob_activity app. (ex.: `http://[localhost]/[owncloud]/index.php/settings/apps?installed`).

## Uninstall

Modify the brackets `[ ]` in the examples below.

1. Delete oob_activity folder.
<br/> (ex.: `rm -rf /var/www/html/[owncloud]/apps/ooba`).

2. Delete all oob_activity database configurations: 'ooba' and 'oobactivity' ids rows from oc_appconfig table).
<br/> (ex.: `sudo -u postgres -H -- psql -d [owncloud database name] -c "delete from oc_appconfig where appid='ooba'"`).
<br/> (ex.: `sudo -u postgres -H -- psql -d [owncloud database name] -c "delete from oc_appconfig where appid='oobactivity'"`).

3. Delete the oob_activity external database.
<br/> (ex.: `sudo -u postgres -H -- psql -c "drop database [external database name]"`).

## Acknowledgements
This development has been funded by [FINEP](http://www.finep.gov.br), the Brazilian Innovation Agency.
