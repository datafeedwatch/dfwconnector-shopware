<?php

namespace dfwconnector\Controller\Api;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Routing\Annotation\Route;
use dfwconnector\Library\Store;

/**
 * @RouteScope(scopes={"administration"})
 */
class ApiBridgeTestController extends AbstractController
{
  /**
   * @var Client
   */
  protected $_client;

  const CART_ID = 'Shopware';
  const BRIDGE_ACTION = 'checkbridge';
  const BRIDGE_FOLDER = 'bridge2cart';

  /**
   * ApiBridgeTestController constructor.
   */
  public function __construct()
  {
    $this->_client = new Client([
      'allow_redirects' => true,
    ]);
  }

  /**
   * @Route(path="/api/v{version}/_action/api-bridge-test/verify")
   */
  public function check(RequestDataBag $dataBag, Context $context): JsonResponse
  {
    $params = [
      'store_root' => $dataBag->get('dfwconnector.config.StoreRoot') ?? ''
    ];
    $data = $this->_prepareUseHash($dataBag->get('dfwconnector.config.StoreKey') ?? '', $params);
    $query = http_build_query($data['get']);
    $headers = [
      'Accept-Language' => '*',
      'User-Agent' => $this->randomUserAgent()
    ];

    $request = $this->_exec(
      'POST',
      [
        'query' => $query,
        'form_params' => $data['post'],
        'headers' => $headers
      ],
      $context
    );

    if (!$request instanceof Response) {
      return new JsonResponse(['error' => $request]);
    } else {
      $content = $request->getBody()->getContents();

      if ($content == 'BRIDGE_OK') {
        return new JsonResponse(['success' => $content]);
      } else {
        return new JsonResponse(['error' => $content]);
      }

    }

  }

  /**
   * @param string  $method
   * @param array   $params
   * @param Context $context
   * @return \Psr\Http\Message\ResponseInterface|string
   */
  private function _exec($method = 'GET', $params = [], $context)
  {
    $sleep = 500000;
    $request = 'Bad Request';

    /**
     * @var RequestStack $requestStack
     */
    $requestStack = $this->container->get('request_stack');
    $sheme = $requestStack->getCurrentRequest()->getScheme();
    $host = $requestStack->getCurrentRequest()->getHost();

    /**
     * @var EntityRepositoryInterface|null $salesChannelRepo
     */
    $salesChannelRepo = $this->container->get('sales_channel.repository');
    $criteria = new Criteria();
    $criteria->addFilter(
      new EqualsFilter('active', '1')
    );

    /**
     * @var SalesChannelCollection $salesChannelCollection
     */
    $salesChannelCollection = $salesChannelRepo->search($criteria, $context);

    /**
     * @var EntityRepositoryInterface|null $salesChannelDomainRepo
     */
    $salesChannelDomainRepo = $this->container->get('sales_channel_domain.repository');
    $access_key = '';

    foreach ($salesChannelCollection->getElements() as $salesChannel) {
      $criteria = new Criteria();
      $criteria->addFilter(new EqualsFilter('salesChannelId', $salesChannel->getId()));

      /** @var SalesChannelDomainCollection|null $salesChannelDomainCollection */
      $salesChannelDomainCollection = $salesChannelDomainRepo->search($criteria, $context);
      $access_key = $salesChannel->getAccessKey();
      $params['headers'] = ['sw-access-key' => $access_key];

      foreach ($salesChannelDomainCollection->getElements() as $salesChannelDomain) {
        $uri = $salesChannelDomain->getUrl() . DIRECTORY_SEPARATOR . 'store-api/v3/bridge2cart/bridge-action';
        $request = $this->_request($method, $uri, $params);

        if ($request instanceof Response) {
          break 2;
        }

        usleep($sleep);
      }

    }

    //try to connect to default port
    if (!$request instanceof Response) {
      $params['headers'] = ['sw-access-key' => $access_key];
      $uri = $sheme . '://' . $host . DIRECTORY_SEPARATOR . 'store-api/v3/bridge2cart/bridge-action';
      $request = $this->_request($method, $uri, $params);
    }

    return $request;
  }

  /**
   * @param string $method
   * @param string $uri
   * @param array  $params
   * @return \Psr\Http\Message\ResponseInterface|string
   */
  private function _request($method = 'GET', string $uri, $params = [])
  {
    if ($method == 'GET') {
      $response = $this->_get($uri, $params);
      return $response;
    } elseif ($method = 'POST') {
      $response = $this->_post($uri, $params);
      return $response;
    }

  }

  /**
   * @param string $uri
   * @param array  $options
   * @return \Psr\Http\Message\ResponseInterface|string
   */
  private function _get(string $uri, array $options = [])
  {
    try {
      $response = $this->_client->get($uri, $options);
    } catch (RequestException $requestException) {
      return $this->handleRequestException($requestException);
    }

    return $response;
  }

  /**
   * @param string $uri
   * @param array  $options
   * @return \Psr\Http\Message\ResponseInterface|string
   */
  private function _post(string $uri, array $options = [])
  {
    try {
      $response = $this->_client->post($uri, $options);
    } catch (RequestException $requestException) {
      return $this->handleRequestException($requestException);
    }

    return $response;
  }

  /**
   * @param string     $storeKey
   * @param array|null $params
   * @return array
   */
  private function _prepareUseHash(string $storeKey, array $params = null): array
  {
    /** @var Connection $connection */
    $connection = $this->container->get(Connection::class);
    $paramConnections = $connection->getParams();

    $getParams = [
      'unique' => md5(uniqid(mt_rand(), 1)),
      'disable_checks' => 1,
      'cart_id' => $this::CART_ID //for older bridge compatibility
    ];

    if (!is_array($params)) {
      $params = [];
    }

    $params['action'] = $this::BRIDGE_ACTION;
    $params['cart_id'] = $this::CART_ID;

    if (isset($paramConnections['charset'])) {
      $params['set_names'] = $paramConnections['charset'];
    }

    ksort($params, SORT_STRING);
    $params['a2c_sign'] = hash_hmac('sha256', http_build_query($params), $storeKey);

    return ['get' => $getParams, 'post' => $params];
  }

  /**
   * Generate random User-Agent
   * @return string
   */
  private function randomUserAgent()
  {
    $rand = mt_rand(1, 3);
    switch ($rand) {
      case 1:
        return 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:25.0) Gecko/2010' . mt_rand(10, 12) . mt_rand(10, 30) . ' Firefox/' . mt_rand(10, 25) . '.0';

      case 2:
        return 'Mozilla/6.0 (Windows NT 6.2; WOW64; rv:16.0.1) Gecko/2012' . mt_rand(10, 12) . mt_rand(10, 30) . ' Firefox/' . mt_rand(10, 16) . '.0.1';

      case 3:
        return 'Opera/10.' . mt_rand(10, 60) . ' (Windows NT 5.1; U; en) Presto/2.6.30 Version/10.60';

      // don't use "cn" letter combination in user-agent strings; can cause problems fo some stores
    }
  }

  /**
   * @param RequestException $requestException
   * @return string
   */
  private function handleRequestException(RequestException $requestException): string
  {
    $exceptionMessage = $requestException->getMessage();
    $exceptionResponse = $requestException->getResponse();

    if ($exceptionResponse === null) {
      return 'ERROR: ' . $exceptionMessage;
    }

    $error = \json_decode($exceptionResponse->getBody()->getContents(), true);
    if (\is_array($error) && \array_key_exists('error', $error) && \array_key_exists('error_description', $error)) {
      return 'ERROR: ' . $error['error_description'];
    } else {
      return 'ERROR: ' . $exceptionMessage;
    }

  }

  /**
   * @Route(path="/api/v{version}/_action/api-bridge-test/updatekey")
   */
  public function updateStoreKey(RequestDataBag $dataBag, Context $context): JsonResponse
  {
    $pluginPath = realpath(dirname(__FILE__, 3)) . DIRECTORY_SEPARATOR . 'Library' . DIRECTORY_SEPARATOR;
    $store = new Store($pluginPath);
    $storeKey = Store::generateStoreKey();
    $res = $store->updateToken($storeKey);

    if (!isset($res['error'])) {
      $this->container->get(SystemConfigService::class)->set('dfwconnector.config.StoreKey', $storeKey);
      return new JsonResponse([
        'success' => 'Store Key updates successfully. Please reload this page.',
        'storekey' => $storeKey
      ]);
    } else {
      return new JsonResponse(['error' => $res['error']]);
    }
  }

}