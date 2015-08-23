# Getting Started
Penny is a framework that help you to build YOUR application. In this tutorial we can try to build our first skeleton application.
A simple HTML application that require:

* [theleague/plates](https://github.com/theleague/plates) to render your page.
* [doctrine/doctrine](https://github/doctrine/doctrine2) to persist your data into the mysql database.
* [zendframework/zend-form](https://github/zendframework/zend-form) to create/update your data.

In this tutorial I sue [bower](https://bower.io) and [grunt](http://gruntjs.com/) to manage fronted assets

## Install
```bash
composer require gianarb/penny:dev-master
```

## Foldering
Penny is only a framework to build an application is necessary a good foldering. This is the proposal for this tutorial.  
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

`app` contains application files. `config` is the default value that penny use to load dependency injection configurations.  
Every application has an entrypoint `public/index.php` is our.

Create this directories or clone [penny-foldering](https://github.com/gianarb/penny-foldering).
```bash
git clone git@github.com:gianarb/penny-foldering ./penny-app
cd penny-app
composer install
```

# WebServer
You can use your favourite webserver.

## PHP
In develop I use the PHP Internal Server. You go in the root of project and run it.

```bash
php -S 127.0.0.0:8085 -t public
```

## Nginx configuration
```
upstream fpm {
    server unix:/var/run/fpm-api.sock;
}

server {
    listen   8080;
    server_name _;
    proxy_pass_header                   Server;
    root /opt/currency-fair/backend/public;
    index index.php;

    location / {
	try_files                       $uri $uri/ /index.php$is_args$args;
    }

    location ~* .php$ {
        fastcgi_pass                    fpm;
	fastcgi_param                   SCRIPT_FILENAME /opt/currency-fair/backend/public/index.php;
	include fastcgi_params
    }
}
```

# Dependency Injection and configuration
