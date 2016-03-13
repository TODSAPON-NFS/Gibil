#!/bin/bash
ps -e | grep php
if [ $? == "1" ]
	then
	/usr/bin/php -f /home/gibil/Gibil/controller.php &
fi
