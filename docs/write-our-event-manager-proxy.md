# Write Our Event Manager Proxy

We can write our event manager proxy with the following signature:

```php
namespace App\EventManager\Event;

use Our\Awesome\EventManager;

class OurAwesomeEventManagerProxy implements PennyEvmInterface
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

After that, we can register it as service named 'event_manager' in Our favorite container.

```php
use App\EventManager\Event\OurAwesomeEventManagerProxy;
use DI;

$builder = new DI\ContainerBuilder();
$builder->useAnnotations(true);
$builder->addDefinitions(
    [
        'event_manager' =>  DI\object(OurAwesomeEventManagerProxy::class),
        // other services definition here
        // see https://github.com/pennyphp/penny/blob/master/src/Container/PHPDiFactory.php
    ]
);
$builder->addDefinitions($config);
$container = $builder->build();
$container->set('di', $container);

$app = new App($container);
```
