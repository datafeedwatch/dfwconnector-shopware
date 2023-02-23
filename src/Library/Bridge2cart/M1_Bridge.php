<?php declare(strict_types=1);

namespace dfwconnector\Library\Bridge2cart;

use Symfony\Component\HttpFoundation\Request;


/*                       ATTENTION!
+------------------------------------------------------------------------------+
| By our Terms of Use you agreed not to change, modify, add, or remove portions|
| of Bridge Script source code                                                 |
+-----------------------------------------------------------------------------*/

class M1_Bridge
{
  /**
   * @var M1_DatabaseLink|null
   */
  protected $_link  = null; //mysql connection link

  /**
   * @var Request $_request
   */
  public $request;
  public $config    = null; //config adapter

  /**
   * Bridge constructor
   *
   * M1_Bridge constructor.
   * @param $config
   */
  public function __construct(M1_Config_Adapter $config, Request $request)
  {
    $this->config = $config;
    $this->request = $request;

    if ($this->getAction() != "savefile") {
      $this->_link = $this->config->connect();
    }
  }

  /**
   * @return mixed
   */
  public function getTablesPrefix()
  {
    return $this->config->tblPrefix;
  }

  /**
   * @return M1_DatabaseLink|null
   */
  public function getLink()
  {
    return $this->_link;
  }

  /**
   * @return mixed|string
   */
  private function getAction()
  {

    if ($this->request->get('action')) {
      return str_replace('.', '', $this->request->get('action'));
    }

    return '';
  }

  public function run()
  {
    $action = $this->getAction();

    if ($action == "checkbridge") {
      return "BRIDGE_OK";
    }

    if ($this->request->get('token')) {
      return ('ERROR: Field token is not correct');
    }

    if ($this->request->getMethod() == 'POST' && empty($this->request->getContent())) {
      return ('BRIDGE INSTALLED.<br /> Version: ' . M1_BRIDGE_VERSION);
    }

    if (!empty($this->request->get('a2c_sign'))) {
      $sign = $this->request->get('a2c_sign');
    } else {
      return('ERROR: Signature is not correct');
    }

    if ($this->request->headers->get('Content-Type') == 'application/x-www-form-urlencoded') {
      $postDataArray = explode('&', rawurldecode($this->request->getContent()));
      $postData = [];

      foreach ($postDataArray as $items) {
        $item = explode('=', $items, 2);
        if (isset($item[1]) && ($item[1] == strtolower('true') || $item[1] == strtolower('false'))) {
          $postData[$item[0]] = (bool)filter_var($item[1] ?? 0, FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($item[0]) && $item[0] == 'query') {
          $postData[$item[0]] = $this->request->get('query');
        } elseif (isset($item[0]) && $item[0] == 'queries') {
          $postData[$item[0]] = $this->request->get('queries');
        } elseif (isset($item[0]) && $item[0] == 'data') {
          $postData[$item[0]] = $this->request->get('data');
        } else {
          $postData[$item[0] ?? 0] = ctype_digit($item[1] ?? 0) ? (int)$item[1] ?? 0 : (string)$item[1] ?? '';
        }
      }

    } else {
      $postData = \json_decode($this->request->getContent(), true);
    }

    ksort($postData, SORT_STRING);
    unset($postData['a2c_sign']);

    $resSign = hash_hmac('sha256', http_build_query($postData), M1_TOKEN);

    if ($sign !== $resSign) {
      return('ERROR: Signature is not correct');
    }

    $className = "dfwconnector\Library\Bridge2cart\M1_Bridge_Action_" . ucfirst($action);

    if (!class_exists($className)) {
      return 'ACTION_DO_NOT EXIST';
    }

    $actionObj = new $className();
    $actionObj->cartType = $this->config->cartType;
    $actionObj->perform($this);
    $this->_destroy();
  }

  /**
   * @param $dir
   * @return bool
   */
  private function isWritable($dir)
  {
    if (!is_dir($dir)) {
      return false;
    }

    $dh = opendir($dir);

    if ($dh === false) {
      return false;
    }

    while (($entry = readdir($dh)) !== false) {
      if ($entry == "." || $entry == ".." || !is_dir($dir . DIRECTORY_SEPARATOR . $entry)) {
        continue;
      }

      if (!$this->isWritable($dir . DIRECTORY_SEPARATOR . $entry)) {
        return false;
      }
    }

    if (!is_writable($dir)) {
      return false;
    }

    return true;
  }

  private function _destroy()
  {
    $this->_link = null;
  }


  private function _selfTest()
  {
    if (isset($_GET['token'])) {
      if ($_GET['token'] === M1_TOKEN) {
        // good :)
      } else {
        echo('ERROR_INVALID_TOKEN');
        return;
      }
    } else{
      echo('BRIDGE INSTALLED.<br /> Version: ' . M1_BRIDGE_VERSION);
      return;
    }
  }

  /**
   * Remove php comments from string
   * @param string $str
   */
  public static function removeComments($str)
  {
    $result  = '';
    $commentTokens = array(T_COMMENT, T_DOC_COMMENT);
    $tokens = token_get_all($str);

    foreach ($tokens as $token) {
      if (is_array($token)) {
        if (in_array($token[0], $commentTokens))
          continue;
        $token = $token[1];
      }
      $result .= $token;
    }

    return $result;
  }

  /**
   * @param $str
   * @param string $constNames
   * @param bool $onlyString
   * @return array
   */
  public static function parseDefinedConstants($str, $constNames = '\w+', $onlyString = true )
  {
    $res = array();
    $pattern = '/define\s*\(\s*[\'"](' . $constNames . ')[\'"]\s*,\s*'
      . ($onlyString ? '[\'"]' : '') . '(.*?)' . ($onlyString ? '[\'"]' : '') . '\s*\)\s*;/';

    preg_match_all($pattern, $str, $matches);

    if (isset($matches[1]) && isset($matches[2])) {
      foreach ($matches[1] as $key => $constName) {
        $res[$constName] = $matches[2][$key];
      }
    }

    return $res;
  }

}

/**
 * Class miSettings
 */
class miSettings {

  protected $_arr;

  /**
   * @return miSettings|null
   */
  public function singleton()
  {
    static $instance = null;
    if ($instance == null) {
      $instance = new miSettings();
    }
    return $instance;
  }

  /**
   * @param $arr
   */
  public function setArray($arr)
  {
    $this->_arr[] = $arr;
  }

  /**
   * @return mixed
   */
  public function getArray()
  {
    return $this->_arr;
  }

}

/**
 * Class M1_Config_Adapter_Shopware
 */
class M1_Config_Adapter_Shopware extends M1_Config_Adapter
{
  /**
   * M1_Config_Adapter_Shopware constructor.
   */
  public function __construct()
  {
    require_once M1_STORE_BASE_DIR . 'vendor/autoload.php';

    $shopwareVersion = $composerFile = '';

    if (class_exists('PackageVersions\Versions')) {
      preg_match('/(?:v)?\s*((?:[0-9]+\.?)+)/', \PackageVersions\Versions::getVersion('shopware/core'), $matches);
    } elseif ((class_exists('Composer\InstalledVersions'))) {
      preg_match('/(?:v)?\s*((?:[0-9]+\.?)+)/', \Composer\InstalledVersions::getVersion('shopware/core'), $matches);
    }


    if (isset($matches[1])) {
      $shopwareVersion = $matches[1];
    } elseif (file_exists(M1_STORE_BASE_DIR . 'composer.json')) {
      $composerFile = file_get_contents(M1_STORE_BASE_DIR . 'composer.json');
    } elseif (file_exists(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . 'composer.json')) {
      $composerFile = file_get_contents(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . 'composer.json');
    }

    if ($composerFile) {
      $content = json_decode($composerFile, true);
      $shopwareVersion = str_replace(['~', '^', 'v'], '', isset($content['require']['shopware/core']) ? $content['require']['shopware/core'] : '');
    }

    if (empty($shopwareVersion)) {
      throw new \Exception('ERROR_DETECTING_PLATFORM_VERSION');
    }

    $this->cartVars['dbVersion'] = $shopwareVersion;
    $this->timeZone = 'UTC';

    $envLoader = new \Symfony\Component\Dotenv\Dotenv();
    $config = $envLoader->parse(file_get_contents(M1_STORE_BASE_DIR . '.env'));

    $params = [];
    foreach (parse_url($config['DATABASE_URL']) as $param => $value) {////from Doctrine\DBAL\DriverManager parseDatabaseUrl function
      if (is_string($value)) {
        $params[$param] = rawurldecode($value);
      } else {
        $params[$param] = $value;
      }
    }

    $this->cartVars['sdn_strategy'] = isset($config['SHOPWARE_CDN_STRATEGY_DEFAULT']) ? $config['SHOPWARE_CDN_STRATEGY_DEFAULT'] : 'id';

    $port = isset($params['port']) ? ':' . $params['port'] : '';
    $this->setHostPort($params['host'] . $port);

    $this->dbname = ltrim($params['path'], '/');
    $this->username = isset($params['user']) ? $params['user'] : '';
    $this->password = isset($params['pass']) ? $params['pass'] : '';
  }

  /**
   * @param array $data Contain request params and payload
   *
   * @return mixed
   * @throws Exception
   */
  public function productAddAction(array $data)
  {
    return $this->_importEntity($data);
  }

  /**
   * @param array $data Contain request params and payload
   *
   * @return mixed
   * @throws Exception
   */
  public function productUpdateAction(array $data)
  {
    return $this->_importEntity($data);
  }

  /**
   * @param array $a2cData Contain request params and payload
   *
   * @return mixed
   * @throws Exception
   */
  public function apiSend(array $a2cData)
  {
    return $this->_importEntity($a2cData);
  }

  /**
   * @param array $data Data to import
   *
   * @return array
   */
  private function _importEntity($data)
  {
    $response = array('error' => null, 'data' => false);
    try {
      require_once M1_STORE_BASE_DIR . 'vendor/autoload.php';

      if (file_exists(M1_STORE_BASE_DIR . '.env')) {
        (new \Symfony\Component\Dotenv\Dotenv(true))->load(M1_STORE_BASE_DIR . '.env');
      } else {
        throw new \Exception('File \'.env\' not found');
      }

      if (function_exists('curl_version')) {
        $response['data'] = $this->_sendRequestWithCurl($data);
      } elseif (class_exists('\GuzzleHttp\Client')) {
        $response['data'] = $this->_sendRequestWithGuzzle($data);
      } else {
        throw new \Exception('Http client not found');
      }

    } catch (\Exception $e) {
      $response['error']['message'] = $e->getMessage();
      $response['error']['code'] = $e->getCode();
    }

    return $response;
  }

  /**
   * @param array $data Contain request params and payload
   *
   * @return bool
   * @throws Exception
   */
  private function _sendRequestWithCurl($data)
  {
    $headers = array(
      'Content-Type: application/json',
      'Authorization: ' . $this->_getToken($data['meta']['user_id'])
    );
    $ch = curl_init();

    try {
      $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
    } catch (\Exception $e) {
      $scriptName = 'index.php';
    }

    $uri = $_SERVER['SERVER_NAME'] . str_replace('/' . $scriptName, '', $_SERVER['PHP_SELF']);
    $uri = str_replace('/bridge2cart', '', $uri);

    if ($data['method'] === 'POST') {
      curl_setopt($ch, CURLOPT_URL, $uri . '/api/' . $data['entity']);
    } elseif ($data['method'] === 'DELETE') {
      curl_setopt($ch, CURLOPT_URL, $uri . '/api/' . $data['entity']);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    } else {
      $url = $uri . '/api/' . $data['entity'] . '/' . $data['meta']['entity_id'];
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $data['payload']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);

    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = json_decode(substr($res, curl_getinfo($ch, CURLINFO_HEADER_SIZE)));
    curl_close($ch);

    if ($httpCode == "204") {
      return "204";
    } else {
      $message = '';
      if (isset($body->errors[0]->detail)) {
        $message = 'Error message: ' . $body->errors[0]->detail;
      }

      throw new \Exception('Bridge curl failed. Not expected http code. ' . $message, $httpCode);
    }
  }

  /**
   * @param array $data Contain request params and payload
   *
   * @return bool
   * @throws Exception
   */
  private function _sendRequestWithGuzzle($data)
  {
    $headers = array(
      'Content-Type' => 'application/json',
      'Accept' => 'application/json',
      'Authorization' => $this->_getToken($data['meta']['user_id'])
    );

    $client = new \GuzzleHttp\Client();
    $message = '';

    try {
      $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
    } catch (\Exception $e) {
      $scriptName = 'index.php';
    }

    $uri = $_SERVER['SERVER_NAME'] . str_replace('/' . $scriptName, '', $_SERVER['PHP_SELF']);
    $uri = str_replace('/bridge2cart', '', $uri);

    try {
      $options = ['body' => $data['payload'], 'headers' => $headers];

      if ($data['method'] === 'POST') {
        $response = $client->post(
          $uri . '/api/' . $data['entity'],
          $options
        );
      } elseif ($data['method'] === 'DELETE') {
        $response = $client->delete(
          $uri . '/api/' . $data['entity']
        );
      } else {
        $response = $client->put(
          $uri . '/api/' . $data['entity'] . '/' . $data['meta']['entity_id'],
          $options
        );
      }
    } catch (\GuzzleHttp\Exception\RequestException $e) {
      if ($e->hasResponse()) {
        $response = $e->getResponse();
        $body = \json_decode(((string)$response->getBody()), true);

        if (isset($body['errors'][0]['detail'])) {
          $message = 'Error message: ' . $body['errors'][0]['detail'];
        }
      } else {
        throw new \Exception('Guzzle failed');
      }
    }

    if ($response->getStatusCode() === 204) {
      return true;
    } else {
      throw new \Exception('Not expected http code from shopware. ' . $message, $response->getStatusCode());
    }
  }

  /**
   * @param string $userId Admin user id
   *
   * @return string
   * @throws Exception
   */
  private function _getToken($userId)
  {
    $connection = \Shopware\Core\Kernel::getConnection();
    $client = new \Shopware\Core\Framework\Api\OAuth\Client\ApiClient('administration', true);

    $scopeRepo = new \Shopware\Core\Framework\Api\OAuth\ScopeRepository(array(), $connection);
    $finalizedScopes = $scopeRepo->finalizeScopes(array(), 'password', $client, $userId);

    $tokenRepo = new \Shopware\Core\Framework\Api\OAuth\AccessTokenRepository();
    $accessToken = $tokenRepo->getNewToken($client, $finalizedScopes, $userId);

    $writeScope = new \Shopware\Core\Framework\Api\OAuth\Scope\WriteScope();
    $accessToken->setClient($client);
    $accessToken->setUserIdentifier($userId);
    $accessToken->addScope($writeScope);
    $accessToken->setExpiryDateTime((new \DateTimeImmutable())->add(new \DateInterval('PT1H')));

    if (file_exists(M1_STORE_BASE_DIR . 'config/jwt/private.pem')) {
      $storeRoot = M1_STORE_BASE_DIR;
    } elseif (file_exists(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . 'config/jwt/private.pem')) {
      $storeRoot = M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR;
    } else {
      throw new Exception('File \'private.pem\' not found');
    }

    $key = new \League\OAuth2\Server\CryptKey($storeRoot . 'config/jwt/private.pem', 'shopware', false);
    $accessToken->setPrivateKey($key);

    return 'Bearer ' . (string)$accessToken->__toString();
  }
}

/**
 * Class M1_Bridge_Action_Update
 */
class M1_Bridge_Action_Update
{
  private $_pathToTmpDir;

  /**
   * M1_Bridge_Action_Update constructor.
   */
  public function __construct()
  {
    $this->_pathToTmpDir = __DIR__ . DIRECTORY_SEPARATOR . "temp_a2c";
  }

  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    $response = array('message' => null, 'data' => false, 'code' => 1);

    $response['message'] = 'Action is not supported';

    echo(json_encode($response));
    return;
  }

}

/**
 * Class M1_Bridge_Action_SetProductStores
 */
class M1_Bridge_Action_SetProductStores
{
  public function perform(M1_Bridge $bridge)
  {
    $response = array('error' => null, 'data' => false);
    $response['error']['message'] = 'Action is not supported';

    echo json_encode($response);
  }
}

/**
 * Class M1_Bridge_Action_Send_Notification
 */
class M1_Bridge_Action_Send_Notification
{

  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    $response = array(
      'error' => false,
      'code' => null,
      'message' => null,
    );

    echo json_encode($response);
  }
}


/**
 * Class M1_Bridge_Action_Savefile
 */
class M1_Bridge_Action_Savefile
{
  protected $_imageType = null;
  protected $_mageLoaded = false; protected $_cartType = 'Shopware';

  /**
   * @param $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    $source      = $bridge->request->get('src');
    $destination = $bridge->request->get('dst');
    $width       = (int)$bridge->request->get('width');
    $height      = (int)$bridge->request->get('height');

    echo $this->_saveFile($source, $destination, $width, $height);
  }

  /**
   * @param $source
   * @param $destination
   * @param $width
   * @param $height
   * @param string $local
   * @return string
   */
  public function _saveFile($source, $destination, $width, $height)
  {
    if (preg_match('/(.png)|(.jpe?g)|(.gif)$/', $destination) != 1) {
      return('ERROR_INVALID_FILE_EXTENSION');
    }

    if (!preg_match('/^https?:\/\//i', $source)) {
      $result = $this->_createFile($source, $destination);
    } else {
      $result = $this->_saveFileCurl($source, $destination);
    }

    if ($result != "OK") {
      return $result;
    }

    $destination = M1_STORE_BASE_DIR . $destination;

    if ($width != 0 && $height != 0) {
      $this->_scaled2( $destination, $width, $height );
    }

    if ($this->_cartType == "Prestashop11") {
      // convert destination.gif(png) to destination.jpg
      $imageGd = $this->_loadImage($destination);

      if ($imageGd === false) {
        return $result;
      }

      if (!$this->_convert($imageGd, $destination, IMAGETYPE_JPEG, 'jpg')) {
        return "CONVERT FAILED";
      }
    }

    return $result;
  }

  /**
   * @param $filename
   * @param bool $skipJpg
   * @return bool|resource
   */
  private function _loadImage($filename, $skipJpg = true)
  {
    $imageInfo = getimagesize($filename);
    if ($imageInfo === false) {
      return false;
    }

    $this->_imageType = $imageInfo[2];

    switch ($this->_imageType) {
      case IMAGETYPE_JPEG:
        $image = imagecreatefromjpeg($filename);
        break;
      case IMAGETYPE_GIF:
        $image = imagecreatefromgif($filename);
        break;
      case IMAGETYPE_PNG:
        $image = imagecreatefrompng($filename);
        break;
      default:
        return false;
    }

    if ($skipJpg && ($this->_imageType == IMAGETYPE_JPEG)) {
      return false;
    }

    return $image;
  }

  /**
   * @param $image
   * @param $filename
   * @param int $imageType
   * @param int $compression
   * @return bool
   */
  private function _saveImage($image, $filename, $imageType = IMAGETYPE_JPEG, $compression = 85)
  {
    $result = true;
    if ($imageType == IMAGETYPE_JPEG) {
      $result = imagejpeg($image, $filename, $compression);
    } elseif ($imageType == IMAGETYPE_GIF) {
      $result = imagegif($image, $filename);
    } elseif ($imageType == IMAGETYPE_PNG) {
      $result = imagepng($image, $filename);
    }

    imagedestroy($image);

    return $result;
  }

  /**
   * @param $source
   * @param $destination
   * @return string
   */
  private function _createFile($source, $destination)
  {
    if ($this->_createDir(dirname($destination)) !== false) {
      $destination = M1_STORE_BASE_DIR . $destination;
      $body = base64_decode($source);
      if ($body === false || file_put_contents($destination, $body) === false) {
        return '[BRIDGE ERROR] File save failed!';
      }

      return 'OK';
    }

    return '[BRIDGE ERROR] Directory creation failed!';
  }

  /**
   * @param $source
   * @param $destination
   * @return string
   */
  private function _saveFileCurl($source, $destination)
  {
    $source = $this->_escapeSource($source);
    if ($this->_createDir(dirname($destination)) !== false) {
      $destination = M1_STORE_BASE_DIR . $destination;

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $source);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_TIMEOUT, 60);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_exec($ch);
      $httpResponseCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

      if ($httpResponseCode != 200) {
         curl_close($ch);
        return "[BRIDGE ERROR] Bad response received from source, HTTP code $httpResponseCode!";
      }

      $dst = fopen($destination, "wb");
      if ($dst === false) {
        return "[BRIDGE ERROR] Can't create  $destination!";
      }
      curl_setopt($ch, CURLOPT_NOBODY, false);
      curl_setopt($ch, CURLOPT_FILE, $dst);
      curl_setopt($ch, CURLOPT_HTTPGET, true);
      curl_exec($ch);
      if (($error_no = curl_errno($ch)) != CURLE_OK) {
        return "[BRIDGE ERROR] $error_no: " . curl_error($ch);
      }

      curl_close($ch);
      return "OK";

    } else {
      return "[BRIDGE ERROR] Directory creation failed!";
    }
  }

  /**
   * @param $source
   * @return mixed
   */
  private function _escapeSource($source)
  {
    return str_replace(" ", "%20", $source);
  }

  /**
   * @param $dir
   * @return bool
   */
  private function _createDir($dir)
  {
    $dirParts = explode("/", $dir);
    $path = M1_STORE_BASE_DIR;
    foreach ($dirParts as $item) {
      if ($item == '') {
        continue;
      }
      $path .= $item . DIRECTORY_SEPARATOR;
      if (!is_dir($path)) {
        $res = mkdir($path, 0755);
        if (!$res) {
          return false;
        }
      }
    }
    return true;
  }

  /**
   * @param resource $image     - GD image object
   * @param string   $filename  - store sorce pathfile ex. M1_STORE_BASE_DIR . '/img/c/2.gif';
   * @param int      $type      - IMAGETYPE_JPEG, IMAGETYPE_GIF or IMAGETYPE_PNG
   * @param string   $extension - file extension, this use for jpg or jpeg extension in prestashop
   *
   * @return true if success or false if no
   */
  private function _convert($image, $filename, $type = IMAGETYPE_JPEG, $extension = '')
  {
    $end = pathinfo($filename, PATHINFO_EXTENSION);

    if ($extension == '') {
      $extension = image_type_to_extension($type, false);
    }

    if ($end == $extension) {
      return true;
    }

    $width  = imagesx($image);
    $height = imagesy($image);

    $newImage = imagecreatetruecolor($width, $height);

    /* Allow to keep nice look even if resized */
    $white = imagecolorallocate($newImage, 255, 255, 255);
    imagefill($newImage, 0, 0, $white);
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $width, $height );
    imagecolortransparent($newImage, $white);

    $pathSave = rtrim($filename, $end);

    $pathSave .= $extension;

    return $this->_saveImage($newImage, $pathSave, $type);
  }

  /**
   * scaled2 method optimizet for prestashop
   *
   * @param $destination
   * @param $destWidth
   * @param $destHeight
   * @return string
   */
  private function _scaled2($destination, $destWidth, $destHeight)
  {
    $method = 0;

    $sourceImage = $this->_loadImage($destination, false);

    if ($sourceImage === false) {
      return "IMAGE NOT SUPPORTED";
    }

    $sourceWidth  = imagesx($sourceImage);
    $sourceHeight = imagesy($sourceImage);

    $widthDiff = $destWidth / $sourceWidth;
    $heightDiff = $destHeight / $sourceHeight;

    if ($widthDiff > 1 && $heightDiff > 1) {
      $nextWidth = $sourceWidth;
      $nextHeight = $sourceHeight;
    } else {
      if (intval($method) == 2 || (intval($method) == 0 AND $widthDiff > $heightDiff)) {
        $nextHeight = $destHeight;
        $nextWidth = intval(($sourceWidth * $nextHeight) / $sourceHeight);
        $destWidth = ((intval($method) == 0 ) ? $destWidth : $nextWidth);
      } else {
        $nextWidth = $destWidth;
        $nextHeight = intval($sourceHeight * $destWidth / $sourceWidth);
        $destHeight = (intval($method) == 0 ? $destHeight : $nextHeight);
      }
    }

    $borderWidth = intval(($destWidth - $nextWidth) / 2);
    $borderHeight = intval(($destHeight - $nextHeight) / 2);

    $destImage = imagecreatetruecolor($destWidth, $destHeight);

    $white = imagecolorallocate($destImage, 255, 255, 255);
    imagefill($destImage, 0, 0, $white);

    imagecopyresampled($destImage, $sourceImage, $borderWidth, $borderHeight, 0, 0, $nextWidth, $nextHeight, $sourceWidth, $sourceHeight);
    imagecolortransparent($destImage, $white);

    return $this->_saveImage($destImage, $destination, $this->_imageType, 100) ? "OK" : "CAN'T SCALE IMAGE";
  }
}

/**
 * Class M1_Bridge_Action_ReindexProduct
 */
class M1_Bridge_Action_ReindexProduct
{

  public function perform(M1_Bridge $bridge)
  {
    $response = array(
      'error' => null,
      'data' => false
    );
    $response['error']['message'] = 'Action is not supported';

    echo json_encode($response);
  }

}



/**
 * Class M1_Bridge_Action_Query
 */
class M1_Bridge_Action_Query
{

  /**
   * Extract extended query params from post and request
   */
  public static function requestToExtParams($bridge)
  {
    return array(
      'fetch_fields' => ($bridge->request->get('fetchFields') && ($bridge->request->get('fetchFields') == 1)),
      'set_names' => !empty($bridge->request->get('set_names')) ? $bridge->request->get('set_names') : false,
    );
  }

  /**
   * @param M1_Bridge $bridge Bridge Instance
   *
   * @return bool
   */
  public static function setSqlMode(M1_Bridge $bridge)
  {
    if (!empty($bridge->request->get('sql_settings'))) {
      $sqlSettings = $bridge->request->get('sql_settings');

      try {
        if (isset($sqlSettings['sql_modes'])) {
          $query = "SET SESSION SQL_MODE=" . base64_decode(swapLetters($sqlSettings['sql_modes']));
          $bridge->getLink()->localQuery($query);
        }

        if (isset($sqlSettings['sql_variables'])) {
          $query = base64_decode(swapLetters($sqlSettings['sql_variables']));
          $bridge->getLink()->localQuery($query);
        }
      } catch (Throwable $exception) {
        echo base64_encode(
          serialize(
            [
              'error'         => $exception->getMessage(),
              'query'         => $query,
              'failedQueryId' => 0,
            ]
          )
        );

        return false;
      }
    }

    return true;
  }

  /**
   * @param M1_Bridge $bridge Bridge instance
   * @return bool
   */
  public function perform(M1_Bridge $bridge)
  {
    if (!empty($bridge->request->get('query')) && !empty($bridge->request->get('fetchMode'))) {
      $query = base64_decode(swapLetters($bridge->request->get('query')));

      $fetchMode = (int)$bridge->request->get('fetchMode');

      if (!self::setSqlMode($bridge)) {
        return false;
      }

      $res = $bridge->getLink()->query($query, $fetchMode, self::requestToExtParams($bridge));

      if (is_array($res['result']) || is_bool($res['result'])) {
        $result = serialize(array(
          'res'           => $res['result'],
          'fetchedFields' => $res['fetchedFields'],
          'insertId'      => $bridge->getLink()->getLastInsertId(),
          'affectedRows'  => $bridge->getLink()->getAffectedRows(),
        ));

        echo base64_encode($result);
      } else {
        echo base64_encode($res['message']);
      }
    } else {
      return false;
    }
  }
}

class M1_Bridge_Action_Platform_Action
{
  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    if (!empty($bridge->request->get('platform_action')) && !empty($bridge->request->get('data'))
      && method_exists($bridge->config, $bridge->request->get('platform_action'))
    ) {
      $response = array('error' => null, 'data' => null);

      try {
        $data = json_decode(base64_decode(swapLetters($bridge->request->get('data'))), true);
        $response['data'] = $bridge->config->{$bridge->request->get('platform_action')}($data);
      } catch (\Exception $e) {
        $response['error']['message'] = $e->getMessage();
        $response['error']['code'] = $e->getCode();
      } catch (\Throwable $e) {
        $response['error']['message'] = $e->getMessage();
        $response['error']['code'] = $e->getCode();
      }

      echo json_encode($response);
    } else {
      return json_encode(array('error' => array('message' => 'Action is not supported'), 'data' => false));
    }
  }
}

/**
 * Class M1_Bridge_Action_Phpinfo
 */
class M1_Bridge_Action_Phpinfo
{

  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    phpinfo();
  }
}


/**
 * Class M1_Bridge_Action_Mysqlver
 */
class M1_Bridge_Action_Mysqlver
{

  /**
   * @param $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    $message = array();
    preg_match('/^(\d+)\.(\d+)\.(\d+)/', \mysql_get_server_info($bridge->getLink()), $message);
    echo sprintf("02d%02d", $message[1], $message[2], $message[3]);
  }
}

class M1_Bridge_Action_Multiquery
{

  protected $_lastInsertIds = array();
  protected $_result        = array();

  /**
   * @param M1_Bridge $bridge Bridge Instance
   * @return bool|null
   */
  public function perform(M1_Bridge $bridge)
  {
    if (!empty($bridge->request->get('queries')) && !empty($bridge->request->get('fetchMode'))) {
      ini_set("memory_limit","512M");

      $queries = json_decode(base64_decode(swapLetters($bridge->request->get('queries'))));
      $count = 0;

      if (!M1_Bridge_Action_Query::setSqlMode($bridge)) {
        return false;
      }

      foreach ($queries as $queryId => $query) {

        if ($count++ > 0) {
          $query = preg_replace_callback('/_A2C_LAST_\{([a-zA-Z0-9_\-]{1,32})\}_INSERT_ID_/', array($this, '_replace'), $query);
          $query = preg_replace_callback('/A2C_USE_FIELD_\{([\w\d\s\-]+)\}_FROM_\{([a-zA-Z0-9_\-]{1,32})\}_QUERY/', array($this, '_replaceWithValues'), $query);
        }

        $res = $bridge->getLink()->query($query, (int)$bridge->request->get('fetchMode'), M1_Bridge_Action_Query::requestToExtParams($bridge));
        if (is_array($res['result']) || is_bool($res['result'])) {

          $queryRes = array(
            'res'           => $res['result'],
            'fetchedFields' => $res['fetchedFields'],
            'insertId'      => $bridge->getLink()->getLastInsertId(),
            'affectedRows'  => $bridge->getLink()->getAffectedRows(),
          );

          $this->_result[$queryId] = $queryRes;
          $this->_lastInsertIds[$queryId] = $queryRes['insertId'];

        } else {
          $data['error'] = $res['message'];
          $data['failedQueryId'] = $queryId;
          $data['query'] = $query;

          echo base64_encode(serialize($data));
          return false;
        }
      }
      echo base64_encode(serialize($this->_result));
    } else {
      return false;
    }
  }

  /**
   * @param array $matches Matches
   *
   * @return int|string
   */
  protected function _replace($matches)
  {
    return $this->_lastInsertIds[$matches[1]];
  }

  /**
   * @param array $matches Matches
   *
   * @return string
   */
  protected function _replaceWithValues($matches)
  {
    $values = array();
    if (isset($this->_result[$matches[2]]['res'])) {
      foreach ($this->_result[$matches[2]]['res'] as $row) {
        $values[] = addslashes($row[$matches[1]]);
      }
    }

    return '"' . implode('","', array_unique($values)) . '"';
  }

}

/**
 * Class M1_Bridge_Action_Getconfig
 */
class M1_Bridge_Action_Getconfig
{

  /**
   * @param $val
   * @return int
   */
  private function parseMemoryLimit($val)
  {
    $valInt = (int)$val;
    $last = strtolower($val[strlen($val)-1]);

    switch($last) {
      case 'g':
        $valInt *= 1024;
      case 'm':
        $valInt *= 1024;
      case 'k':
        $valInt *= 1024;
    }

    return $valInt;
  }

  /**
   * @return mixed
   */
  private function getMemoryLimit()
  {

    $memoryLimit = trim(ini_get('memory_limit'));
    if (strlen($memoryLimit) === 0) {
      $memoryLimit = "0";
    }
    $memoryLimit = $this->parseMemoryLimit($memoryLimit);

    $maxPostSize = trim(ini_get('post_max_size'));
    if (strlen($maxPostSize) === 0) {
      $maxPostSize = "0";
    }
    $maxPostSize = $this->parseMemoryLimit($maxPostSize);
    //return trim(ini_get('post_max_size'));
    $suhosinMaxPostSize = "0";//trim(ini_get('suhosin.post.max_value_length'));

    if (strlen($suhosinMaxPostSize) === 0) {
      $suhosinMaxPostSize = "0";
    }

    $suhosinMaxPostSize = $this->parseMemoryLimit($suhosinMaxPostSize);

    if ($suhosinMaxPostSize == 0) {
      $suhosinMaxPostSize = $maxPostSize;
    }

    if ($maxPostSize == 0) {
      $suhosinMaxPostSize = $maxPostSize = $memoryLimit;
    }

    return min($suhosinMaxPostSize, $maxPostSize, $memoryLimit);
  }

  /**
   * @return bool
   */
  private function isZlibSupported()
  {
    return function_exists('gzdecode');
  }

  /**
   * @param $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    if (!defined("DEFAULT_LANGUAGE_ISO2")) {
      define("DEFAULT_LANGUAGE_ISO2", ""); //variable for Interspire cart
    }

    try {
      $timeZone = date_default_timezone_get();
    } catch (\Exception $e) {
      $timeZone = 'UTC';
    }

    $result = array(
      "images" => array(
        "imagesPath"                => $bridge->config->imagesDir, // path to images folder - relative to store root
        "categoriesImagesPath"      => $bridge->config->categoriesImagesDir,
        "categoriesImagesPaths"     => $bridge->config->categoriesImagesDirs,
        "productsImagesPath"        => $bridge->config->productsImagesDir,
        "productsImagesPaths"       => $bridge->config->productsImagesDirs,
        "manufacturersImagesPath"   => $bridge->config->manufacturersImagesDir,
        "manufacturersImagesPaths"  => $bridge->config->manufacturersImagesDirs,
      ),
      "languages"             => $bridge->config->languages,
      "baseDirFs"             => M1_STORE_BASE_DIR,    // filesystem path to store root
      "bridgeVersion"         => M1_BRIDGE_VERSION,
      "defaultLanguageIso2"   => DEFAULT_LANGUAGE_ISO2,
      "databaseName"          => $bridge->config->dbname,
      "cartDbPrefix"          => $bridge->config->tblPrefix,
      "memoryLimit"           => $this->getMemoryLimit(),
      "zlibSupported"         => $this->isZlibSupported(),
      "cartVars"              => $bridge->config->cartVars,
      "time_zone"             => $bridge->config->timeZone ?: $timeZone
    );

    echo serialize($result);
  }

}

/**
 * Class M1_Bridge_Action_Collect_Totals
 */
class M1_Bridge_Action_Get_Url
{

  /**
   * @param M1_Bridge $bridge bridge
   *
   * @return void
   */
  public function perform(M1_Bridge $bridge)
  {
    $response = array('error' => null, 'data' => null);

    $response['error'] = [
      'message' => 'Action is not supported'
    ];

    echo json_encode($response);
  }

}


/**
 * Class M1_Bridge_Action_GetShipmentProviders
 */
class M1_Bridge_Action_GetShipmentProviders
{

  public function perform(M1_Bridge $bridge)
  {
    $response = array('error' => null, 'data' => null);

    $response['error'] = [
      'message' => 'Action is not supported'
    ];

    echo json_encode($response);
  }

}

/**
 * Class M1_Bridge_Action_GetPaymentModules
 */
class M1_Bridge_Action_GetPaymentModules
{

  public function perform(M1_Bridge $bridge)
  {
    $response = array('error' => null, 'data' => null);

    $response['error'] = [
      'message' => 'Action is not supported'
    ];

    echo json_encode($response);
  }

}

/**
 * Class M1_Bridge_Action_GetCartWeight
 */
class M1_Bridge_Action_GetCartWeight
{
  public function perform(M1_Bridge $bridge)
  {
    $response = array('error' => null, 'data' => null);
    $response['error']['message'] = 'Action is not supported';

    echo json_encode($response);
  }
}

/**
 * Class M1_Bridge_Action_GetAbandonedOrderTotal
 */
class M1_Bridge_Action_GetAbandonedOrderTotal
{

  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {

    $response = array('error' => null, 'data' => null);
    $response['error']['message'] = 'Action is not supported';

    echo json_encode($response);
  }

}

/**
 * Class M1_Bridge_Action_DispatchCartEvents
 */
class M1_Bridge_Action_DispatchCartEvents
{

  public function perform(M1_Bridge $bridge)
  {
    $response = array('error' => null, 'data' => null);
    $response['error']['message'] = 'Action is not supported';
    $response['error']['code']    = 1;

    echo json_encode($response);
  }

}

/**
 * Class M1_Bridge_Action_Deleteimages
 */
class M1_Bridge_Action_Deleteimages
{

  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    $response = array('error' => null, 'data' => null);
    $response['error']['message'] = 'Action is not supported';
    $response['error']['code']    = 1;

    echo json_encode($response);
  }

}

/**
 * Class M1_Bridge_Action_Delete
 */
class M1_Bridge_Action_Delete
{

  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    $response = new \stdClass();

    $response->code    = 1;
    $response->message = 'Action is not supported';

    echo(json_encode($response));
    return;
  }

}

/**
 * Class M1_Bridge_Action_CreateRefund
 */
class M1_Bridge_Action_CreateRefund
{

  /**
   * @param M1_Bridge $bridge
   * @return void
   */
  public function perform(M1_Bridge $bridge)
  {
    $response = array('error' => null, 'data' => null);

    $response['error']['message'] = 'Action is not supported';
    $response['error']['code']    = 1;

    echo json_encode($response);
    return;
  }

}


/**
 * Class M1_Bridge_Action_Clearcache
 */
class M1_Bridge_Action_Clearcache
{

  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    {
      $shopwareVersion = $bridge->config->cartVars['dbVersion'];
      $response = array('error' => null, 'data' => false);

      if (version_compare($shopwareVersion, '6.0.0.0', '>=')) {
        try {
          if (file_exists(M1_STORE_BASE_DIR . '.env')) {
            (new \Symfony\Component\Dotenv\Dotenv(true))->load(M1_STORE_BASE_DIR . '.env');
            $storeRoot = M1_STORE_BASE_DIR;
          } elseif(file_exists(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . '.env')) {
            (new \Symfony\Component\Dotenv\Dotenv(true))->load(M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR . '.env');
            $storeRoot = M1_STORE_BASE_DIR . '..' . DIRECTORY_SEPARATOR;
          } else {
            throw new \Exception('File \'.env\' not found');
          }

          $adapter = new M1_Config_Adapter_Shopware();

          require_once $storeRoot . 'vendor/autoload.php';

          if (isset($_POST['cache_type']['user_id'])) {
            $userId = $_POST['cache_type']['user_id'];
          } else {
            $response['error']['message'] = 'UserId not defined';
            $response['error']['code']    = 1;
          }

          $data = ['payload' => [], 'method' => 'DELETE', 'entity' => '_action/cache', 'meta' => ['user_id' => $userId]];
          $response['data'] = $adapter->apiSend($data);
        } catch (\Exception $e) {
          $response['error']['message'] = $e->getMessage();
          $response['error']['code'] = $e->getCode();
        }
      }

      echo json_encode($response);
    }
  }

}

/**
 * Class M1_Bridge_Action_Batchsavefile
 */
class M1_Bridge_Action_Batchsavefile extends M1_Bridge_Action_Savefile
{

  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    $result = array();

    foreach ($bridge->request->files->all() as $fileInfo) {
      $result[$fileInfo['id']] = $this->_saveFile(
        $fileInfo['source'],
        $fileInfo['target'],
        (int)$fileInfo['width'],
        (int)$fileInfo['height']
      );
    }

    echo serialize($result);
    return;
  }

}

/**
 * Class M1_Bridge_Action_Basedirfs
 */
class M1_Bridge_Action_Basedirfs
{

  /**
   * @param M1_Bridge $bridge
   */
  public function perform(M1_Bridge $bridge)
  {
    echo M1_STORE_BASE_DIR;
    return;
  }
}

/**
 * Class M1_Bridge_Action_GetShippingRates
 */
class M1_Bridge_Action_GetShippingRates
{
  public function perform(M1_Bridge $bridge)
  {
    $response = array('error' => null, 'data' => null);
    $response['error']['message'] = 'Action is not supported';

    echo json_encode($response);
    return;
  }
}


define('M1_BRIDGE_VERSION', '134');
define('M1_BRIDGE_DIRECTORY_NAME', basename(getcwd()));

show_error(0);

require_once 'config.php';

if (!defined('M1_TOKEN')) {
  echo('ERROR_TOKEN_NOT_DEFINED');
  return;
}

if (strlen(M1_TOKEN) !== 32) {
  echo('ERROR_TOKEN_LENGTH');
  return;
}

function show_error($status)
{
  if ($status) {
    //@ini_set('display_errors', 1);
    if (substr(phpversion(), 0, 1) >= 5) {
      error_reporting(E_ALL & ~E_STRICT);
    } else {
      error_reporting(E_ALL);
    }
  } else {
    //@ini_set('display_errors', 0);
    error_reporting(0);
  }
}

/**
 * @param $array
 * @return array|string
 */
function stripslashes_array($array)
{
  return is_array($array) ? array_map('dfwconnector\Library\Bridge2cart\stripslashes_array', $array) : stripslashes($array);
}

function exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() === 0) {
    return;
  }

  if (strpos($message, 'Declaration of') === 0) {
    return;
  }

  if (error_reporting() & $severity) {
    throw new \ErrorException($message, 0, $severity, $filename, $lineno);
  }
}

set_error_handler('dfwconnector\Library\Bridge2cart\exceptions_error_handler');

/**
 * @return bool|mixed|string
 */
function getPHPExecutable()
{
  $paths = explode(PATH_SEPARATOR, getenv('PATH'));
  $paths[] = PHP_BINDIR;
  foreach ($paths as $path) {
    // we need this for XAMPP (Windows)
    if (isset($_SERVER["WINDIR"]) && strstr($path, 'php.exe') && file_exists($path) && is_file($path)) {
      return $path;
    } else {
      $phpExecutable = $path . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
      if (file_exists($phpExecutable) && is_file($phpExecutable)) {
        return $phpExecutable;
      }
    }
  }
  return false;
}

function swapLetters($input) {
  $default = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
  $custom  = "ZYXWVUTSRQPONMLKJIHGFEDCBAzyxwvutsrqponmlkjihgfedcba9876543210+/";

  return strtr($input, $default, $custom);
}

if (version_compare(phpversion(), '7.4', '<') && get_magic_quotes_gpc()) {
  $_COOKIE  = stripslashes_array($_COOKIE);
  $_FILES   = stripslashes_array($_FILES);
  $_GET     = stripslashes_array($_GET);
  $_POST    = stripslashes_array($_POST);
  $_REQUEST = stripslashes_array($_REQUEST);
}

?>