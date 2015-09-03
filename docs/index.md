# What is Penny?
Penny is a framework that helps you to build middleware applications, it is based on an event system and allows you to combine the perfect libraries for your application.

# Perfect Libraries
PHP has a very powerful open source ecosystem. There are a lot of developers and a lot of companies that work and put effort  on some of the best libraries with wich we can use to build our applications.
Penny was born to manage integration between your favorite libraries and it does not force you to use nothing you don't want to.

# The Core
Penny is very easy its core is built using [PHP-DI](http://php-di.org) a strong dependency injection library.
In its base implementation it uses [nikic/FastRoute](https://github.com/nikic/FastRoute) a fast regular expression based router.

# The cost of freedom
This freedom requires a strong knowledge of the Dependency Injection pattern and a deep understanding of the open source ecosystem is essential to choose the perfect mix of libraries with which build your application.

Here we put a list of implementation examples to help you with your development process:

* [classic-app](https://github.com/gianarb/penny-classic-app) is a skeleton application to build classic web application with a HTML Render. It implements [thephpleague/plates](https://github.com/thephpleague/plates) how template engine.
