<?php

// Cron job for sending emails and pruning the ooba list
\OC::$server->getJobList()->add('OCA\Ooba\BackgroundJob\EmailNotification');
\OC::$server->getJobList()->add('OCA\Ooba\BackgroundJob\ExpireOobas');
