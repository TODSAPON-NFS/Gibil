# Gibil
Fire Alarm Display server

This server displays fire alarm events generated at on the UBC vancouver campus

## System Spec

### Operating System
Ubuntu 14.04 Trusty Tahr

## Installation Steps (Web Server)
install lamp stack
`sudo apt-get update`
`sudo apt-get install lamp-server`

update php to latest
`sudo apt-get install php5-json`

set sql database root password to
`iwicbV15`

install git
`sudo apt-get install git`

clone repository to home directory
`cd`
`git clone https://github.com/wantonsolutions/Gibil.git`

initalize the db
`mysql -u root -p < initdb.sql`

link apache to gibil
`cd /var/www/html`
`ln -s /home/gibil/Gibil/`


## Installation Steps (Serial Interface)

Install setserial
`sudo apt-get install setserial`

add apache and www-data to the dialout usergroup
`sudo useradd -g apache dialout`
`sudo useradd -g www-data dialout`

if nessisary change the privilages of the serial port in use
`sudo chmod 777 \dev\ttyS_`


## Install Background maintenence service

To setup gibil as a background task add the folowing command to /etc/crontab
`* * * * * root exec /home/gibil/Gibil/maintain.sh` 

## Install Automatic Email warnings
gibil is configured to send emails automatically when failures 
occur. The functionality is setup through the php mail() function.
 A detailed guide to setting up an msmtp server can be found 
[here]:https://www.digitalocean.com/community/tutorials/how-to-use-gmail-or-yahoo-with-php-mail-function
