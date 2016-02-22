# Out-Of-Band ownCloud Activity

## Description
The oob_activity app modifies the original app [activity](https://github.com/owncloud/activity) version 1.1.23,  provided for ownCloud. 
oob_activity allow to register user activity in external storage database or syslog.
The oob_activity app was only tested in Ubuntu 14.04, ownCloud 7.0.4 and PostgreSQL 9.3.6.

## Releases

v1.2.1 [download](https://github.com/Malinoski/oob_activity/releases/tag/v1.2.1)
- The external storage was configured to use database or syslog (default). The configuration can be done in [owncloud home]/apps/activity/config/config.xml.

v1.2 [download](https://github.com/Malinoski/oob_activity/releases/tag/v1.2)
- Enhanced the feature for user activity in syslog. This feature can be enable or disable in configuration file ([owncloud home]/apps/activity/config/config.xml).

v1.1 [download](https://github.com/Malinoski/oob_activity/releases/tag/v1.1)
- Added the configuration file [owncloud home]/apps/activity/config/config.xml.
- Added the feature to register user activity in log files optionally through configuration file.

v1.0 [download](https://github.com/Malinoski/oob_activity/releases/tag/v1.0)
- First version.

## Instalation
1. Backup your ownCloud and database servers.

2. Uninstall the default activity app:
  * Delete the activity app folder from the ownCloud server (`/[path_to_owncloud]/apps/activity/`).
  * Delete all occurrences of the activity app from the `oc_appconfig` table. 
<br/> (ex.: `sudo -u postgres -H -- psql -d owncloud -c "delete from oc_appconfig where appid='activity'"`).
  * Delete the activity app tables.
<br/> (ex.: `sudo -u postgres -H -- psql -d owncloud -c "DROP TABLE oc_activity, oc_activity_mq"`).

3. Create the external database for the oob_activity app.
<br/> (ex.: `sudo -u postgres -H -- psql -c "CREATE DATABASE owncloud_activity_db"`).

4. oob_activity app configuration:
  * Download and extract the last oob_activity reseale.
  * Rename the extracted folder to "activity".
  * Move "activity" folder to the ownCloud's app folder (`/[path_to_owncloud]/apps/`).
  * Grant privilegies to the oob_activity "activity" folder.
<br/> (ex.: `chown -R www-data:www-data /[path_to_owncloud]/apps/activity`).
  * Edit oob_activity parameters as desired in `/[path_to_owncloud]/apps/activity/config/config.php`.

5. ownCloud server configuration
  * Login as administrator in your ownCloud server (ex.: http://[localhost]/[owncloud])
  * Activate the oob_activity app. (ex.: `http://[localhost]/[owncloud]/index.php/settings/apps?installed`).

## Acknowledgements
This development has been funded by [FINEP](http://www.finep.gov.br), the Brazilian Innovation Agency.
