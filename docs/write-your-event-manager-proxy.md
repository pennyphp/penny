# Write Your Event Manager Proxy

You can write our event manager proxy with the following signature:

```php
namespace App\EventManager\Event;

use Your\Awesome\EventManager;

class YourAwesomeEventManagerProxy implements PennyEvmInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * Proxy EventManager
     */
    public function __construct()
    {
        $this->eventManager = new EventManager();
    }

    /**
     * {@inheritDoc}
     */
    public function trigger(PennyEventInterface $event)
    {
        $this->eventManager->trigger($event);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function attach($eventName, callable $listener, $priority = 0)
    {
        $this->eventManager->attach($eventName, $listener, $priority);
        return $this;
    }
}
```

After that, you can register it as service named 'event_manager' in your favourite container.

```php
use App\EventManager\Event\YourAwesomeEventManagerProxy;
use DI;

$builder = new DI\ContainerBuilder();
$builder->useAnnotations(true);
$builder->addDefinitions(
    [
        'event_manager' =>  DI\object(YourAwesomeEventManagerProxy::class),
        // other services definition here
        // see https://github.com/pennyphp/penny/blob/master/src/Container/PHPDiFactory.php
    ]
);
$builder->addDefinitions($config);
$container = $builder->build();
$container->set('di', $container);

$app = new App($container);
```
