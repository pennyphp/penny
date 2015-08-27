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
```php
// /public/index.php

<?php
chdir(dirname(__DIR__));
require "vendor/autoload.php";

$app = new \GianArb\Penny\App();
$emitter = new \Zend\Diactoros\Response\SapiEmitter();
$emitter->emit($app->run());
```

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

# Dependency Injection and routing configuration
In this moment the default DiC library is PHP-DI and in this tutorial I use it.

The default path to load configuration files is `/config` directory. It loads all `*.php` files and after them it loads `*.local.php`. This strategy is soo useful to override configurations of to load paramters how database configurations or api keys.

The first step is to define a routing, in this moment I use [nikic/FastRoute](https://github.com/nikic/FastRoute) it is very fast and easy to use. We can use the DI to load Router because this strategy help us to uncouple the routing library by the framework.

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
In this way GET / resolve `PennyApp\Controller\IndexController` object and it calls `index` function. This is our first route.

## Autoloading
To manage autoload you can use [composer](https://getcomposer.org). You can add this configuration in your composer.json
```json
{
    "autoload": {
    	"psr-4": {
            "PennyApp\\": "./app"
        }
    }
}
```
see [composer.json](https://github.com/gianarb/penny-foldering/blob/master/composer.json)

We are ready to write the controller that resolve our route.
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
Only one word "easy".. This is your controller and your action wait $request and $response,
this implementation use [Zend\Diactoros](https://github.com/zendframework/zend-diactoros) and it is PSR-7 compatible.

## Plates
[Plates](https://github.com/thephpleague/plates) is a native PHP template system that’s fast, easy to use and easy to extend.
Now we add it into the our application from dependence injection.

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

## Database and doctrine2 integration
[Doctrine](https://github.com/dotrine/doctrine2) is a famouse Object Relational Mapper (ORM) library and this is the basicly configuration copied by
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

We implement doctrine without factory or other class, we build integration with only dependence injection container.
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
To add personal parameters (database username, password) into the VCS is a very dangerous and bad practice, the `conn` key into the
`parameters` array is only a boilerplate. You can ovveride it, you can add a `/config/*.local.php` file.
```
<?php
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
];
```
*Add it into the .gitignore*

doctrine has an awesome console that helps you to manage database, schema, cache an a lot of other stuff, we should translate [this chapter](http://doctrine-orm.readthedocs.org/en/latest/tutorials/getting-started.html)
```php
<?php
// cli-config.php
require "vendor/autoload.php";

$app = new \GianArb\Penny\App();
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($app->getContainer()->get("doctrine.em"));
```

Now we are ready to use it in your app. We can write our first entity
```
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
Before persist new record into the database validate and filter them is necessary. `Zend\Validator` and `Zend\Form`
are good components to implement this features.

Update composer.json configuration with all dependencies
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

We can write the form
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

Plates is very extensible and we have a problem, BeerForm require a render!
```php
<?php
// /config/app.config.php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use \Interop\Container\ContainerInterface;

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
    "form.beer" => \DI\object(\PennyApp\Form\BeerForm::class)
        ->method("setHydrator", new \Zend\Stdlib\Hydrator\ClassMethods()),
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

```php
// /app/Controller/IndexController.php

<?php
namespace PennyApp\Controller;

use PennyApp\Entity\Beer;
use Zend\Diactoros\Response\RedirectResponse;

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
