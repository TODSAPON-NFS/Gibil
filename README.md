# Gibil
Fire Alarm Display server

This server displays fire alarm events generated at on the UBC vancouver campus

## System Spec

### Operating System
Ubuntu 14.04 Trusty Tahr

### Installation Steps
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


