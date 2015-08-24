# Getting Started
Penny is a framework that helps you to build YOUR own application.
In this tutorial we will try to build our first skeleton application.

Thissimple HTML application needs also some other components:

* [theleague/plates](https://github.com/theleague/plates) to render your page.
* [doctrine/doctrine](https://github/doctrine/doctrine2) to persist your data into the mysql database.
* [zendframework/zend-form](https://github/zendframework/zend-form) to create/update your data.

In this tutorial I used [bower](https://bower.io) and [grunt](http://gruntjs.com/) to manage fronted assets.

## Install

```bash
composer require gianarb/penny:dev-master
```

## Foldering

Penny is just the base framework, to build an application is also necessary a good folder structure.

This tutorial proposal is:

```
.
├── app
│   ├── Controller
│   ├── Form
│   ├── Entity
│   ├── ...
│   └── view
├── bower.json
├── composer.json
├── config
│   └── di.php
├── Gruntfile.js
├── package.json
├── vendor 
└── public
    └── index.php
```

`app` contains application files. `config` is the value that penny uses by default to load dependency injection configurations.  

Every application has an entrypoint, `public/index.php` is our.

Create this directories or clone [penny-foldering](https://github.com/gianarb/penny-foldering).

```bash
git clone git@github.com:gianarb/penny-foldering ./penny-app
cd penny-app
composer install
```

# WebServer

Of courese you can use your favourite webserver. Here are just a few examples

## PHP
In develop I use the PHP Internal Server. You go in the root of project and run it.

```bash
php -S 127.0.0.0:8085 -t public
```

## NGINX/PHP-FPM configuration

`nginx/server.d/example.conf`

```
upstream fpm {
    server unix:/var/run/fpm-example.sock;
}

server {
    listen 8080;
    server_name example.com;
    proxy_pass_header Server;
    root /var/www/example/public;
    index index.php;

    location / {
	    try_files                       $uri $uri/ /index.php$is_args$args;
    }

    location ~* .php$ {
        fastcgi_pass                    fpm;
	    fastcgi_param                   SCRIPT_FILENAME /opt/example/public/index.php;
	    include fastcgi_params
    }
}
```
`php/etc/pool.d/example.conf`

```
[example]


user = fpm
group = fpm

listen = /var/run/fpm-example.sock
listen.mode = 0666

pm = dynamic
pm.max_children = 20
pm.start_servers = 10
pm.min_spare_servers = 10
pm.max_spare_servers = 10
pm.max_requests = 100

chdir = /var/www/example/

security.limit_extensions = .php .phtml

request_terminate_timeout = 600
```

# Dependency Injection and configuration
