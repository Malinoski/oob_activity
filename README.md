# ownCloud Activity App For External ("Out-Of-Band") Database

## Description
The oob_activity app modifies the original app [activity](https://github.com/owncloud/activity) version 1.1.23, provided for ownCloud, to use its own external ("out-of-band") database for activity data. The oob_activity app was only tested for ownCloud 7.0.4 and PostgreSQL 9.3.6.

## Instalation 
- Backup your ownCloud and database.

Uninstall the activity app (modify the brackets `[ ]` in the examples below)
- Remove the activity app folder from the ownCloud (`/[path_to_owncloud]/apps/activity/`).
- Remove all occurrences of the activity app from the `oc_appconfig` table 
<br/> (ex.: `sudo -u postgres -H -- psql -d [owncloud704test] -c "delete from oc_appconfig where appid='activity'"`).
- [Optional] Remove the activity app tables
<br/> (ex.: `sudo -u postgres -H -- psql -d [owncloud704test] -c "DROP TABLE oc_activity, oc_activity_mq"`).

Install the new activity app (modify the brackets `[ ]` in the examples below)
- Create the external database for the new activity app (choose a desired name)
<br/> (ex.: `sudo -u postgres -H -- psql -c "CREATE DATABASE [owncloud_activity]"`).
- Include the new activity app folder in the ownCloud (copy `/[..]/oob_activity/activity` and paste to `/[path_to_owncloud]/apps/`).
- Grant privilegies to the new activity folder.
<br/> (ex.: `chown -R www-data:www-data /[path_to_owncloud]/apps/activity`) 
- Enable the new activity app as ownCloud administrator
<br/> (ex.: `http://[localhost]/[owncloud]/index.php/settings/apps?installed`).
- The new activity app automatically creates the `oc_activity_ext_db_conf` table, but needs to be filled manually
<br/> (ex.: `sudo -u postgres -H -- psql -d owncloud704test -c "UPDATE oc_activity_ext_db_conf SET dbusername='[username]', dbpassword='[password]', dbname='[owncloud_activity]', dbhost='[ip.of.the.external.db]' where activity_conf_id=1;"`.

## Acknowledgements
This development has been funded by [FINEP](http://www.finep.gov.br), the Brazilian Innovation Agency.
