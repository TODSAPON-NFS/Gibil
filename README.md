# Fire Alarm Display server

![FAD Diagram](https://s9.postimg.org/jmcpax7rz/archetecture.png) 

This server displays fire alarm events generated at on the UBC Vancouver
campus. This README is an installation guide, for the server, and it's
configuration to recover upon power failures.

## System Specification

The following specification is for a [LAMP (Linux Apache MySQL
PHP)](https://en.wikipedia.org/wiki/LAMP_%28software_bundle%29) web server All
installation instructions are to be performed on a bash command line.


### Operating System
Ubuntu 14.04 Trusty Tahr (Desktop 32bit)

Ubuntu 14.04 is a time tested Linux distribution with stable software packages
(circa 2017). A disk image of the operating system can be found on [Ubuntu's
download
page](http://releases.ubuntu.com/14.04/ubuntu-14.04.5-desktop-i386.iso.torrent?_ga=2.259986057.203424073.1496254464-30841599.1496254464)
which can be installed from a flash drive using [pen drive
linux](https://www.pendrivelinux.com/)


## Installation Steps (LAMP stack)
The following list of commands install the LAMP stack which the Fire Alarm Display server relies on. 

The [sudo](https://en.wikipedia.org/wiki/Sudo) command used throughout grants a user of a Linux computer temporary complete control over the system. Allowing for software to be installed.

[apt-get (Advanced Packaging
Tool)](https://en.wikipedia.org/wiki/Advanced_Packaging_Tool) Is the package
manager (software installer) for the Ubuntu operating system.

The following commands issued in order on a bash command prompt complete the
installation and configuration of the LAMP stack

### install lamp stack

`sudo apt-get update`
`sudo apt-get install lamp-server`

### update PHP to latest

`sudo apt-get install PHP5-json`

### set SQL database root password to (THIS PASSWORD IS MANDATORY!)

`iwicbV15`

## Installation Steps (Fire Alarm Display Server)

### install git

[git](https://git-scm.com/) is a version control tool for managing software.
The Fire Alarm Display server is managed by a git repository. Install git
with the command.
`sudo apt-get install git`

### clone repository to home directory 

The Fire Alarm Display server is
managed as a private git repository on Github.com under the code name
[Gibil](https://github.com/wantonsolutions/Gibil)
([origin](https://en.wikipedia.org/wiki/Gibil)). Issue the following commands
to clone the repository to your home directory.

`cd`
`git clone https://github.com/wantonsolutions/Gibil.git`

If access to the repository is not granted, request a
[tarball](https://en.wikipedia.org/wiki/Tar_%28computing%29A) From either
[Stewart Grant](sgrant09@cs.ubc.ca) or [Howard Davis](davis@dccnet.com). After acquiring the tarball run, place it in your home directory and run.

`cd`
`tar -xvzf name_of_tarball.tar.gz`

### initialize the db

The Fire Alarm Display relies on a MySQL database to store panel status. The
database schema is initialized by running the initdb script stored in the
Gibil directory.

`mysql -u root -p < initdb.sql`

### link Apache to Gibil
The Apache Web server serves content out of the /var/www/html directory by default. The Fire Alarm Web server will not be view able until its directory is linked to Apaches.

`cd /var/www/html`
`ln -s /home/gibil/Gibil/`


## Installation Steps (Serial Interface) The Fire Alarm Web server receives
fire alarm panel updated from the AEU over a serial connection. The follow
commands install drivers and provide users with permissions to read and write
from serial connections.

### Install setserial

`sudo apt-get install setserial`

### add Apache and www-data to the dialout usergroup

`sudo useradd -g apache dialout`
`sudo useradd -g www-data dialout`

### if necessary change the privileges of the serial port in use 

Linux reads and writes to serial connections via the \dev\ttyS\_\#,(where \# is
the number of the serial interface).

`sudo chmod 777 \dev\ttyS\_#`


## Install Background maintenance service 

The Fire alarm web server produces a large amount of logs for debugging
purposes. In addition the process could fail and the web server would not
restart.  [Cron](https://en.wikipedia.org/wiki/Cron) is a Linux job scheduler.
By setting up maintain.sh as a cron job web server logs over the age of 4
months will be recycled, and the web server will be restarted upon failure
within 60s.

To setup gibil as a background task add the following command to /etc/crontab
`* * * * * root exec /home/gibil/gibil/maintain.sh` 


## Install Automatic Email warnings The Fire Alarm Web server is configured to

send emails automatically when failures occur. The functionality is setup
through the PHP mail() function.  A detailed guide to setting up an msmtp
server can be found
[here]:https://www.digitalocean.com/community/tutorials/how-to-use-gmail-or-yahoo-with-php-mail-function

## Automatic Booting

By default PC's do not boot automatically when supplied with AC power. In case of power failures, it is useful boot automatically when power is restored. Doing so is done via BIOS configuration [tutorial](http://www.tomshardware.com/reviews/bios-beginners,1126-8.html).


