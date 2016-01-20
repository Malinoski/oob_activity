# ownCloud Activity App For External ("Out-Of-Band") Database

## Description
The oob_activity app modifies the original app [activity](https://github.com/owncloud/activity) version 1.1.23,  provided for ownCloud, to use its own external ("out-of-band") database for activity data. The oob_activity app was only tested for ownCloud 7.0.4 and PostgreSQL 9.3.6.

## Instalation 
- Backup your ownCloud and database.

Uninstall the activity app:
- Delete the activity app folder from the ownCloud server (`/[path_to_owncloud]/apps/activity/`).
- Delete all occurrences of the activity app from the `oc_appconfig` table. 
<br/> (ex.: `sudo -u postgres -H -- psql -d owncloud -c "delete from oc_appconfig where appid='activity'"`).
- Delete the activity app tables.
<br/> (ex.: `sudo -u postgres -H -- psql -d owncloud -c "DROP TABLE oc_activity, oc_activity_mq"`).

Install the oob_activity app:
- Create the external database for the oob_activity app.
<br/> (ex.: `sudo -u postgres -H -- psql -c "CREATE DATABASE owncloud_logactivity_db"`).
- Download and extract the last oob_activity reseale.
- Rename the extracted folder to "activity".
- Move the oob_activity "activity" folder to the ownCloud's app folder (`/[path_to_owncloud]/apps/`).
- Grant privilegies to the oob_activity "activity" folder.
<br/> (ex.: `chown -R www-data:www-data /[path_to_owncloud]/apps/activity`).
- Configure the oob_activity parameters as desired (`/[path_to_owncloud]/apps/activity/config/config.php`).

Activate the oob_acivity in your ownCloud server 
- Login as administrator and activate the oob_activity app.
<br/> (ex.: `http://[localhost]/[owncloud]/index.php/settings/apps?installed`).

## Releases

[v1.1](https://github.com/Malinoski/oob_activity/releases/tag/v1.0)
- Added the configuration file [owncloud home]/apps/activity/config/config.xml.
- Added the feature to register user activity in log files optionally through configuration file.

[v1.0](https://github.com/Malinoski/oob_activity/releases/tag/v1.1)


## Acknowledgements
This development has been funded by [FINEP](http://www.finep.gov.br), the Brazilian Innovation Agency.
