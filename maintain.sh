#!/bin/bash

#start up the controller if it has died
ps -e | grep php
if [ $? == "1" ]
	then
	exec /usr/bin/php -f /home/gibil/Gibil/simulator.php &
fi

#delete logs older than 4 months
find /home/gibil/Gibil/logs/*.log -type -f -mtime +120 -delete
