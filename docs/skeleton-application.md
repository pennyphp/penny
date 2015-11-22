# Penny Skeleton Application

## Table of contents

- [Introduction](#introduction)
- [Installation](#installation)
    * [Get it](#get-it)
    * [Requirements](#requirements)
    * [PHP Dependencies](#php-dependencies)
    * [Build assets](#build-assets)
- [Web server setup](#web-server-setup)
    * [PHP built-in Web server](#php-built-in-web-server)
    * [Docker](#docker)
- [Next Steps](#next-steps)

# Introduction

The Penny Skeleton Application aims to be the starting point to bootstrap a typical web application made of controllers and views.

## Requirements

- PHP >= 5.4 .
- [Composer](https://getcomposer.org/)  (Required to manage PHP dependencies).
- Node and npm  (Required to build frontend assets).

## PHP Dependencies

PHP dependencies and autoloading are managed trough composer. [New to composer?](https://getcomposer.org/doc/00-intro.md).

# Installation

## Get it

The Penny skeleton application is [hosted on GitHub](https://github.com/pennyphp/penny-skeleton-app)

The most common ways to get it are:

### Via Composer

```
$ composer create-project penny/classic-app -s dev
```

### Via git clone

```bash
$ git clone https://github.com/pennyphp/penny-skeleton-app.git
$ cd penny-skeleton-app && composer install
```

### Downloading the latest master archive

```bash
$ wget -nv -O - https://github.com/pennyphp/penny-skeleton-app/archive/master.zip | tar zx
$ cd penny-skeleton-app-master && composer install
```

## Build assets

Javascript front end dependencies are managed trough [bower](http://bower.io/) and built using [grunt](http://gruntjs.com).
Grunt and other build tools are

**Note:** *The following commands must be issued in the skeleton application folder*,

*Resolve node dependencies*
**Note:**  
bower and grunt require node.js this is only an example of method to manage static asset,
if we don't have familiarity with this tools, no problem, we can use [assetic](https://github.com/kriswallsmith/assetic),
download all static dependencies into the public dir or other solutions.

```bash
$ sudo npm install -G grunt-cli
$ npm install
```

*Resolve frontend dependencies*

```bash
$ ./node_modules/bower/bin/bower install
```

*Build assets*

```bash
$ grunt dev
```

# Web server setup

## PHP built-in Web server

**For testing purposes only** we can use the PHP built-in web server

In the skeleton application folder issue a:

```bash
$ php -S 0.0.0.0:80 -t public
```

## Docker

**Attention**: This is configured  as a *development* environment.
If we want to use it in production, we have to: disable error reporting, persist logs, disable Z-Ray, raise limits and fine tune our configurations.

The `penny-skeleton-app` repository contains a `docker-compose.yml.dist` file which currently configures two containers, one
running the NGINX web server and one running php-fpm.
This file should work as is but  *must be renamed* into `docker-compose.yml`. We can modify if we need something specific for our system like paths, ip addresses, ports, additional services (databases, queues, caching layers) and so on.
Remember that the docker-compose.yml file is in `.gitignore` since this is very specific to the current installation.

### Requirements

- Docker >= 1.6.0
- [docker-compose](https://docs.docker.com/compose/)

### Create our docker-compose.yml

```bash
$ cp docker-compose.yml.dist docker-compose.yml
# edit it for our specific needs
$ vi docker-compose.yml ```

### Build
Before starting, we have to build penny-classic specific images, to do it issue a:

```bash
$ docker-compose build
```

### Up and running

```bash
$ docker-compose up -d
```

### Z-Ray

Z-Ray is included in the Penny Docker development environment.

![Z-Ray](http://i.imgur.com/MfvkfY0.png)

### Endpoints

IP addresses can be configured in `docker-compose.yml`

- Application:  `http://127.0.0.10`
- Z-Ray:  `http://127.0.0.10:10081/ZendServer`


# Next Steps

Add links about Doctrine integration, writing templates with Plates, creating forms, validating forms, etc.
