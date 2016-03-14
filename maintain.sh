#!/bin/bash
echo "maintaining"
echo `date`
ps -e | grep php
if [ $? == "1" ]
	then
	exec /usr/bin/php -f /home/gibil/Gibil/controller.php &
fi
