# node-gearman-jobserver
A NodeJS worker/client experiment using the Gearman Job Server application

## Gearman Installation on the Server
### Install additional packages for compiling
```shell
apt-get -y install wget unzip re2c libgearman-dev php7.1-dev gearman-job-server
 ```
 
### Install from source
```shell
mkdir -p /tmp/install
cd /tmp/install
wget https://github.com/wcgallego/pecl-gearman/archive/master.zip
unzip master.zip
cd pecl-gearman-master
phpize
./configure
make install
echo "extension=gearman.so" > /etc/php/7.1/mods-available/gearman.ini
phpenmod -v ALL -s ALL gearman
rm -rf /tmp/install/pecl-gearman-master
rm /tmp/install/master.zip

service php7.1-fpm restart
service apache2 restart
```
 
### Verify if module is really installed
```shell
php -m | grep gearman
```

### Config
Update or add some custom configuration to the service file.

```shell
nano /lib/systemd/system/gearman-job-server.service
```

Restart the services to use the new configuration
```shell
sudo systemctl daemon-reload
sudo service gearman-job-server restart
```

## Get Started
### Client
Send job requests to the server and handle the responses.

### Server
Register a new worker for given function.

## Info
You can check the status of the Gearman server via Telnet

```shell
telnet 10.32.91.72 4730
```

### Get the general status
Shows a nice overview of all the running / Queued jobs
```shell
status
```
```shell
status
reverse	1	1	1
.
```

### Get the some info about the workers
Show all workers and there running status
```shell
workers
```
```shell
workers
34 10.32.91.26 - : reverse
35 127.0.0.1 - :
```
