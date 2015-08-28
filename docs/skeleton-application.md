# Skeleton Application

## Table of contents

- Introduction
- Installation
    * Get it
    * Requirements
    * PHP Dependencies
    * Build assets
- Web server setup
    * PHP built-in Web server
    * Docker

# Introduction

# Installation

## Get it

The Penny skeleton application is [hosted on GitHub](https://github.com/gianarb/penny-classic-app)

The most common ways to get it are: 

### Via git clone

```bash
https://github.com/gianarb/penny-classic-app.git
```

### Downloading the latest master archive

```
wget -nv -O - https://github.com/gianarb/penny-classic-app/archive/master.zip | tar zx
```

## Requirements

- PHP >= 5.4
- Composer (Required to manage PHP dependencies)
- Node and npm  (Required to build frontend assets)

## PHP Dependencies

PHP dependencies and autoloading are managed trough composer. [New to composer?](https://getcomposer.org/doc/00-intro.md)

## Build assets

Javascript frontend dependencies are managed trough [bower](http://bower.io/) and built using [grunt](http://gruntjs.com).
Grunt and other build tools are 

Note: The following commands must be issued in the skeleton application folder:

Resolve node dependencies

```
sudo npm install -G grunt-cli
npm install
```

Resolve frontend dependencies

```
./node_modules/bower/bin/bower install
```

Build assets

```
grunt dev
```

# Web server setup

## PHP built-in Web server

**For testing purposes only** you can use the PHP built-in web server

In the skeleton application folder issue a:

```
php -S 0.0.0.0:80 -t public
```

## Docker

**Attention**: This is configured  as a *development* environment.
If you want to use it in production you have to: disable error reporting, persist logs, disable Z-Ray, raise limits and fine tune your configurations.

The `penny-classic-app` repository contains a `docker-compose.yml.dist` file which currently configures two containers, one
running the NGINX webserver and one running php-fpm.
This file should work as is but  *must be renamed* into `docker-compose.yml`. You can modify if you need something specific for your system like paths, ip addresses, ports, additional services (databases, queues, caching layers) and so on.
Remember that the docker-compose.yml file is in `.gitignore` since this is very specific to the current installation.

### Requirements

- Docker >= 1.6.0
- [docker-compose](https://docs.docker.com/compose/)

### Create your docker-compose.yml

```bash
cp docker-compose.yml.dist docker-compose.yml
# edit it for your specific needs
vi docker-compose.yml ```

### Build
Before starting you have to build penny-classic specific images, to do it issue a:

```bash
docker-compose build
```

### Up and running

```bash
docker-compose up -d
```

### Z-Ray

Z-Ray is included in the Penny Docker development environment.

![Z-Ray](http://i.imgur.com/MfvkfY0.png)

### Endpoints

IP addresses can be configured in `docker-compose.yml`

- Application:  `http://127.0.0.10`
- Z-Ray:  `http://127.0.0.10:10081/ZendServer`
