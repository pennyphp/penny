# Penny
[![Build Status](https://travis-ci.org/pennyphp/penny.svg?branch=master)](https://travis-ci.org/pennyphp/penny)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gianarb/penny/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gianarb/penny/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/gianarb/penny/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/gianarb/penny/?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/55dadff98d9c4b0018000466/badge.svg?style=flat)](https://www.versioneye.com/user/projects/55dadff98d9c4b0018000466)

Another PHP Framework made of other components.  
One penny is valueless but a lot of pennies build an empire.  

![Penny PHP logo](https://raw.githubusercontent.com/gianarb/penny/master/docs/assets/img/pennyphp.png)

## What is penny?
Penny is a library that help you to build PHP application. it is focused around the interoperability concept.  
At moment Symfony, Zend Framework, Laravel are very big projects and this complexity often is not required.  
This project help you to create applications with the best standalone components.  

## Getting Started
[classic app](https://github.com/gianarb/penny-classic-app) is a first penny implementation. "Classic" because it integrates league/plates and help you to build an HTML application.

### Installation

$ composer create-project penny/classic-app -s dev

### Built-in webserver
```
$ php -S 127.0.0.1:8080 -t public
```
it's ready! You can visit 127.0.0.1:8080

## Projects
[penny-foldering](https://github.com/gianarb/penny-foldering) represents only a foldering implementation. This is the simplest starting poin

[classic-app](https://github.com/gianarb/penny-classic-app) is a skeleton application to build classic web application with a HTML Render. It implements [thephpleague/plates](https://github.com/thephpleague/plates) how template engine.

[currency-fair](https://github.com/gianarb/currency-fair-codetest) backend directory is an API system implementations of penny. It implements [predis](https://github.com/nrk/predis) and few zf components.
