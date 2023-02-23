<?php declare(strict_types=1);

namespace dfwconnector;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\Plugin;

use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use dfwconnector\Library\Store;


class dfwconnector extends Plugin
{
  /**
   * @var Store
   */
  private $store;

  /** initialize Store class
   * @return Store
   */
  private function _initStore()
  {
    $pluginPath = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Library' . DIRECTORY_SEPARATOR;
    $this->store = new Store($pluginPath);
  }

  /** install plugin
   * @param InstallContext $installContext plugin install context
   * @return void
   */
  public function install(InstallContext $installContext): void
  {
    parent::install($installContext);

    $this->_initStore();
    $storeKey = $this->store::generateStoreKey();
    $this->store->updateToken($storeKey);
    $this->container->get(SystemConfigService::class)->set($this->getName() . '.config.StoreKey', $this->store->getStoreKey());
  }

  /** uninstall plugin
   * @param UninstallContext $uninstallContext plugin uninstall context
   * @return void
   */
  public function uninstall(UninstallContext $uninstallContext): void
  {
    parent::uninstall($uninstallContext);

    if ($uninstallContext->keepUserData()) {
      return;
    }

    $this->removeConfiguration($uninstallContext->getContext());
  }

  /** remove configuration
   * @param Context $context plugin context
   * @return void
   */
  private function removeConfiguration(Context $context): void
  {
    /**
     * @var EntityRepositoryInterface $systemConfigRepository
     */
    $systemConfigRepository = $this->container->get('system_config.repository');
    $criteria = (new Criteria())->addFilter(new ContainsFilter('configurationKey', $this->getName() . '.config.'));
    $idSearchResult = $systemConfigRepository->searchIds($criteria, $context);

    $ids = array_map(static function ($id) {
      return ['id' => $id];
    }, $idSearchResult->getIds());

    if ($ids === []) {
      return;
    }

    $systemConfigRepository->delete($ids, $context);
  }

  /**
   * @param ActivateContext $activateContext plugin activate context
   * @return void
   */
  public function activate(ActivateContext $activateContext): void
  {
    parent::activate($activateContext);

    $this->_initStore();
    $this->container->get(SystemConfigService::class)->set($this->getName() . '.config.StoreRoot', realpath($this->container->getParameter('kernel.project_dir')));
    $this->container->get(SystemConfigService::class)->set($this->getName() . '.config.BridgeEndpoint', '/store-api/bridge2cart/bridge-action');
  }
}