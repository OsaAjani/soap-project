#!/bin/bash

#on kill les workers
kill $(ps aux | grep '/var/www/soap-project/console.php' | awk '{print $2}')

#on relance les workers
/var/www/soap-project/console.php internalWorkers status > /dev/null 2>&1 &
/var/www/soap-project/console.php internalWorkers status > /dev/null 2>&1 &

/var/www/soap-project/console.php internalWorkers position > /dev/null 2>&1 &
/var/www/soap-project/console.php internalWorkers position > /dev/null 2>&1 &

/var/www/soap-project/console.php internalWorkers checkTruck > /dev/null 2>&1 &
/var/www/soap-project/console.php internalWorkers checkTruck > /dev/null 2>&1 &

/var/www/soap-project/console.php internalWorkers checkIntervention > /dev/null 2>&1 &
/var/www/soap-project/console.php internalWorkers checkIntervention > /dev/null 2>&1 &
