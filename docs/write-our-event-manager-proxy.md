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

After that, we can register it as service named 'event_manager' in Our favorite container. For example, we use PHP DI that may be facilitated by `Penny\Container\PHPDiFactory` :

```php
use App\EventManager\Event\OurAwesomeEventManagerProxy;
use DI;
use Penny\App;
use Penny\Config\Loader;
use Penny\Container\PHPDiFactory;
use Zend\Stdlib\ArrayUtils;

$config = Loader::load("./config/{{*}}{{,*.local}}.php");
$config = ArrayUtils::merge(
    [
        'event_manager' =>  DI\object(OurAwesomeEventManagerProxy::class),
    ],
    $config
);

$app = new App(PHPDiFactory::buildContainer($config));
```
