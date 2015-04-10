# ownCloud Activity App For External Database

## Description
The app [activity](https://github.com/owncloud/activity), version 1.1.23, was modified to use its own external database for activity data. This modified app was only tested for ownCloud 7.0.4 and PostgreSQL 9.3.6.

## Instalation:
- Backup your ownCloud and database.
- Remove the activity app folder from the ownCloud.
- Remove all occurrences of the activity app from the `oc_appconfig` table 
<br/> (psql command: `DELETE FROM oc_appconfig WHERE appid='activity';`).
- [Optinal] Remove the activity app tables
<br/> (psql command: `DROP TABLE oc_activity, oc_activity_mq`).
- Create the external database for the new activity app
<br/> (psql command: `CREATE DATABASE owncloud_activity`).
- Include the new activity app folder in the ownCloud
<br/> (i.g.: `../owncloud/apps/`).
- Enable the new activity app as a ownCloud administrator
<br/> (i.e.: `http://localhost/owncloud/index.php/settings/apps?installed`).
- The new activity app automatically creates the `oc_activity_ext_db_conf` table, but need to be filled manually
<br/> (psql command: `INSERT INTO oc_activity_ext_db_conf (dbusername, dbpassword, dbname, dbhost) VALUES ('dbusername', 'dbpassword', 'owncloud_activity', 'ip.from.your.external.host');`).