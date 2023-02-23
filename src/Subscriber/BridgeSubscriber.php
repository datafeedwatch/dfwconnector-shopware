<?php declare(strict_types=1);

namespace dfwconnector\Subscriber;

use dfwconnector\dfwconnector;
use dfwconnector\{Library\Store};
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\Plugin\PluginEvents;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BridgeSubscriber implements EventSubscriberInterface
{
  /**
   * @var SystemConfigService
   */
  private $systemConfigService;

  /**
   * @var $store Store
   */
  private $store;

  /**
   * @var $container ContainerInterface
   */
  private $container;

  /**
   * BridgeSubscriber constructor.
   * @param SystemConfigService $systemConfigService
   * @param ContainerInterface $container
   */
  public function __construct(SystemConfigService $systemConfigService, ContainerInterface $container)
  {
    $this->systemConfigService = $systemConfigService;
    $pluginPath = realpath(dirname(__FILE__, 2)) . DIRECTORY_SEPARATOR . 'Library' . DIRECTORY_SEPARATOR;
    $this->store = new Store($pluginPath);
    $this->container = $container;
  }

  public static function getSubscribedEvents(): array
  {
    return [
      PluginEvents::PLUGIN_LOADED_EVENT => 'onPluginLoaded'
    ];
  }

  public function onPluginLoaded(EntityLoadedEvent $event): void
  {
    if ($this->container->get(dfwconnector::class)->isActive()) {
      if (!$this->store->isBridgeExist()) {
        $this->systemConfigService->set('dfwconnector.config.StoreKey', 'Bridge not exist! Please reinstall this Plugin.');
      } else {
        $this->systemConfigService->set('dfwconnector.config.StoreKey', $this->store->getStoreKey());
      }
    }
  }
}