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


## Installation Steps (Serial Interface)

Install setserial
`sudo apt-get install setserial`

add apache and www-data to the dialout usergroup
`sudo useradd -g apache dialout`
`sudo useradd -g www-data dialout`

if nessisary change the privilages of the serial port in use
`sudo chmod 777 \dev\ttyS_`
