<?php
$ACTIVITY_CONFIG = array (
		
  //Log configuration
  'logType' => 'both', // Type 'syslog' (default LOG_INFO), 'database' or 'both'
		
  //Database configuration
  'dbtype' => 'pgsql',
  'dbhost' => 'localhost',
  'dbuser' => 'username',
  'dbpassword' => 'password',
  'dbname' => 'owncloud823external',
		
  //Personal oobactivity development debug: 'syslog'(default syslog info ubuntu: /var/log/syslog), 'owncloud' (for owncloud.log) or '' (for none).
  'oobadebug' => '',
);
