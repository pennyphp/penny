# Getting Started

Penny is a framework that helps you to build YOUR own application.
In this tutorial we will try to build our first skeleton application.

This simple application needs some third-party php components:

* [theleague/plates](https://github.com/theleague/plates) the template system used to render your page.
* [doctrine/doctrine](https://github/doctrine/doctrine2) the ORM, used to persist and load your data from/to the MySQL database.
* [zendframework/zend-form](https://github/zendframework/zend-form) to create forms used manipulate your data.

In this tutorial I also used [bower](https://bower.io) and [grunt](http://gruntjs.com/) to manage fronted assets.

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

- `app` contains application files. 
- `config` is the folder from wich penny loads dependency injection configurations by default.

Every application has an entrypoint, `public/index.php` is our.

```php
// /public/index.php

<?php
chdir(dirname(__DIR__));
require "vendor/autoload.php";

$app = new \GianArb\Penny\App();
$emitter = new \Zend\Diactoros\Response\SapiEmitter();
$emitter->emit($app->run());
```

Create these directories or clone [penny-foldering](https://github.com/gianarb/penny-foldering).

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
    listen 80;
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

# Dependency Injection and routing configuration
At the moment the default DiC library is PHP-DI and in this tutorial I use it.

The default path where penny look for configuration files is the `config` directory.
Files whose name match the `*.php`  pattern are loaded first and then it loads files whose name match the `*.local.php` pattern.
This strategy is  useful to do configuration overriding for things like database credentials or exteranl services api keys.

The first step is to define a routing strategy, at the moment I'm using [nikic/FastRoute](https://github.com/nikic/FastRoute) and, as the name state, it is very fast and surprisingly easy to use.

At this point we can use the DI to load the router decoupling the routing library by the framework.

Create `/config/config.app.php`

```php
<?php
return [
    "router" => function () {
        return \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['PennyApp\Controller\IndexController', 'index']);
            $r->addRoute('POST', '/', ['PennyApp\Controller\IndexController', 'index']);
        });
    },
];
```

In this way GET / resolves to the `PennyApp\Controller\IndexController` controller and then it calls the `index` action.

This is our first route :tada:, now we need the corresponding controller, let's see how to create one.

## Autoloading

To manage autoloading we use [composer](https://getcomposer.org).

You can add this configuration in your composer.json.
This configuration tells your scripts that the `PennyApp` namespace resides under the `app` directory, that's where we are placing our controllers.

```json
{
    "autoload": {
    	"psr-4": {
            "PennyApp\\": "./app"
        }
    }
}
```

see the [penny-foldering composer.json](https://github.com/gianarb/penny-foldering/blob/master/composer.json) for reference

Now we are ready to write the controller that resolve our route.

```php
// /app/Controller/IndexController.php

<?php
namespace PennyApp\Controller;

class IndexController
{
    public function index($request, $response)
    {
        return $response;
    }
}
```

Pretty easy right? This is your controller and your action waiting $request and $response,

the above implementation uses [Zend\Diactoros](https://github.com/zendframework/zend-diactoros) and it is PSR-7 compatible.

## Templating with Plates

[Plates](https://github.com/thephpleague/plates) is a native PHP template system that’s fast, easy to use and easy to extend.

Here's how to add it to our application:

```php
// /config/config.app.php
<?php
return [
    "router" => function () {
        return \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['PennyApp\Controller\IndexController', 'index']);
            $r->addRoute('POST', '/', ['PennyApp\Controller\IndexController', 'index']);
        });
    },
    "template" => \DI\object(\League\Plates\Engine::class)
        ->constructor("./app/view/"), // ./app/view is the path of your templates
];
```

Now you can use it in your controller, update it and create your first template!

```php
// /app/Controller/IndexController.php

<?php
namespace PennyApp\Controller;

class IndexController
{
    /**
     * @Inject("template")
     */
    private $template;

    public function index($request, $response)
    {
        $response->getBody()->write($this->template->render("index", [
            "name" => "developer"
        ]));
        return $response;
    }
}
```

```html
<!-- /app/view/index.php -->
<html>
    <head>
        <title>Penny Application</title>
    </head>
    <body>
        <h1>Hi! I'm a <?php echo $name; ?></h1>
    </body>
</html>
```

## Database integration with Doctrine 2

We are using [Doctrine](https://github.com/dotrine/doctrine2) which is a popular Object Relational Mapper (ORM) library and the following  is it's basic configuration copied directly by
 official site.

```php
<?php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);
$conn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
);
$entityManager = EntityManager::create($conn, $config);
```

Let's see how to integrate it in Penny using the Dependency injection container.

```php
// /config/app.config.php
<?php
return [
    "parameters" => [
        "doctrine" => [
            "orm" => [
                "devMode" => true,
                "entityPaths" => [__DIR__."/../app/Entity"],
                "proxiyDir" => __DIR__."/cache",
                "cacheDir" => __DIR__."/cache",
            ],
            "conn" => [
                "driver" => "pdo_mysql",
                'dbname' => 'translate',
                'user' => 'root',
                'password' => 'root',
                'host' => '127.0.0.1',
            ]
        ]
    ],
    "router" => function () {
        return \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/', ['PennyApp\Controller\IndexController', 'index']);
            $r->addRoute('POST', '/', ['PennyApp\Controller\IndexController', 'index']);
        });
    },
    "template" => \DI\object(\League\Plates\Engine::class)
        ->constructor("./app/view/"), // ./app/view is the path of your templates

    "doctrine.dbal" => \DI\factory(function (\DI\Container $c) {
        return Doctrine\DBAL\DriverManager::getConnection($c->get("parameters")["doctrine"]["conn"]);
    }),
    "doctrine.em" => \DI\factory(function (\DI\Container $c) {
        $config = Setup::createAnnotationMetadataConfiguration(
            $c->get("parameters")['doctrine']['orm']['entityPaths'],
            $c->get("parameters")["doctrine"]["orm"]["devMode"],
            null,
            null,
            false
        );
        $dbal = $c->get("doctrine.dbal");
        return EntityManager::create($dbal, $config);
    }),
];
```

Adding sensitive parameters (like database credentials) into the VCS is a very dangerous and bad practice, the `conn` configuration into the `parameters` array is the default,  VCS committed. not working one.

You can ovveride it in the local environment, adding a `/config/*.local.php` file where keys override the default ones provided in the default configuration.

```
// /config/local.php
return [
    "parameters" => [
        "doctrine" => [
            "conn" => [
                'user' => 'applicationdatabase',
                'password' => '35hw4gesgve4b',
            ]
        ]
    ],
]
```

*Add it into the .gitignore*

Doctrine has an awesome console that helps you to manage database, schema, cache an a lot of other stuff.
**TODO**: we should give an overview on how to do in Penny things contained in  [this chapter](http://doctrine-orm.readthedocs.org/en/latest/tutorials/getting-started.html).

```php
<?php
// cli-config.php
require "vendor/autoload.php";

$app = new \GianArb\Penny\App();
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($app->getContainer()->get("doctrine.em");
```

Now we are ready to use it in your app writing our first entity.

```php
// /app/Entity/Car.php
<?php
namespace PennyApp\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="beers")
 * @ORM\Entity()
 */
class Beer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $nation;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setNation($nation)
    {
        $this->nation = $nation;
        return $this;
    }

    public function getNation()
    {
        return $this->nation;
    }
}
```

## Form and Validation

Before persisting new records into the database validate and filter them is necessary. `Zend\Validator` and `Zend\Form`
are good components that helps to do that.

Update composer.json configuration adding them as dependencies.

```json
{
    "require": {
        "doctrine/orm": "2.5.*",
        "gianarb/penny": "~0.1.0",
        "zendframework/zend-form": "2.5.0",
        "zendframework/zend-view": "2.5.0",
        "zendframework/zend-i18n": "2.5.0",
        "zendframework/zend-escaper": "2.5.0",
        "zendframework/zend-servicemanager": "2.5.0",
        "symfony/console": "2.7.*",
        "league/plates": "3.1.*"
    },
    "require-dev": {
        "phpunit/phpunit": "~4.0"
    },
    "autoload": {
        "psr-4": {
          "PennyApp\\": "./app"
        }
    }
}
```

Write the first form:

```php
// /app/Form/BeerForm.php

<?php
namespace PennyApp\Form;

use Zend\Form\Form;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Text;
use Zend\Form\Element\Submit;

class BeerForm extends Form
{
    public function __construct()
    {
        parent::__construct();

        $hidden = new Hidden('id');
        $this->add($hidden);

        $code = new Text('name');
        $code->setLabel('Name');
        $this->add($code);

        $name = new Text('nation');
        $name->setLabel('Nation');
        $this->add($name);

        $save = new Submit('save');
        $save->setValue('Save');
        $this->add($save);
    }
}
```

Plates is very extensible and now we have a problem, BeerForm require a render! Let's see how to create it:

```php
// /config/app.config.php

return [
    "template" => \DI\object(\League\Plates\Engine::class)
        ->constructor("./app/view/")
        ->method("registerFunction", "form", function () {
            $zfView = new \Zend\View\Renderer\PhpRenderer();
            $plugins = $zfView->getHelperPluginManager();
            $config  = new \Zend\Form\View\HelperConfig;
            $config->configureServiceManager($plugins);
            return $zfView;
    }),
];
```

```html
<!-- /app/view/index.php -->
<html>
    <head>
        <title>Penny Application</title>
    </head>
    <body>
        <h1>Hi! I'm a <?php echo $name; ?></h1>

        <p></p>

        <form method="POST" role="form" action="/">
            <div class="form-group">
                <?= $this->form()->formHidden($form->get('id')) ?>
                <?= $this->form()->formRow($form->get('name')) ?>
                <?= $this->form()->formRow($form->get('nation')) ?>
                <?= $this->form()->formRow($form->get('save')) ?>
            </div>
        </form>

        <p></p>

        <ul>
            <?php foreach ($beers as $beer) { ?>
            <li><?php echo $beer->getName() ?></li>
            <?php } ?>
        </ul>
    </body>
</html>
```

As we did previously with other things we now inject the form into our controller.

```php
// /app/Controller/IndexController.php

<?php
namespace PennyApp\Controller;

use PennyApp\Entity\Beer;

class IndexController
{
    /**
     * @Inject("template")
     */
    private $template;

    /**
     * @Inject("doctrine.em")
     */
    private $entityManager;

    /**
     * @Inject("PennyApp\Form\BeerForm")
     */
    private $beerForm;

    public function index($request, $response)
    {
        $lang = new Beer();
        $beers = $this->entityManager->getRepository("PennyApp\Entity\Beer")->findAll();

        $form = $this->beerForm->bind($lang);

        if ($request->getMethod() == "POST") {
            $form->setData($this->decodeQueryParams($request->getBody()->__toString()));
            if ($form->isValid()) {
                $obj = $form->getObject();
                $this->entityManager->persist($obj);
                $this->entityManager->flush();

                return new RedirectResponse('/', 301);
            }
        }
        $response->getBody()->write($this->template->render("index", [
            "name" => "developer",
            "form" => $beerForm,
            "beers" => $beers,
        ]));
        return $response;
    }

    private function decodeQueryParams($string)
    {
        $params = [];
        foreach (explode('&', $string) as $chunk) {
            $param = explode("=", $chunk);
            $params[urldecode($param[0])] = urldecode($param[1]);
        }
        return $params;
    }
}
```

That's all for now, we really need your feedback to improve Penny.

**SOCIAL-ALERT:** Feedback is important to us. If you want to share your feedback about this document or about Penny, please do it opening an issue or discussing with us on Twitter using the [#pennyphp hashtag](https://twitter.com/hashtag/pennyphp?src=hash) 
