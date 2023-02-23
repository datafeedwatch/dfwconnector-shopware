<?php

namespace dfwconnector\Controller;

use dfwconnector\Library\Bridge2cart\M1_Config_Adapter as Adapter;
use dfwconnector\Library\Bridge2cart\M1_Bridge as Bridge;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use function Composer\Autoload\includeFile;

/**
 * @RouteScope(scopes={"store-api"})
 */
class BridgeController extends AbstractController
{
  const HTTP_NO_CONTENT = '204';

  /**
   * @Route("/store-api/v{version}/bridge2cart/bridge-action", name="api.action.bridge2cart.bridge-action", methods={"GET", "POST"})
   */
  public function BridgeApi(Request $request, Context $context)
  {

    $rootPath = $this->container->getParameter('shopware.filesystem.public.config.root');

    $error = '';
    define("M1_STORE_BASE_DIR", realpath(dirname($rootPath)) . DIRECTORY_SEPARATOR);

    try {
      $adapter = new Adapter();
      $bridge = new Bridge($adapter->create(), $request);
    } catch (\Exception $exception) {
      return JsonResponse::fromJsonString($exception->getMessage());
    }

    if (!$bridge->getLink()) {
      $error = 'ERROR_BRIDGE_CANT_CONNECT_DB';
    }

    $request = $bridge->run();

    $res = !empty($request) ? $request : '';

    if ($res == self::HTTP_NO_CONTENT) {
      return JsonResponse::HTTP_NO_CONTENT;
    }

    return JsonResponse::fromJsonString($res);
  }

}