<?php
namespace dfwconnector\Library\Bridge2cart;

class M1_Config_Adapter implements M1_Platform_Actions
{
  public $host                = 'localhost';
  public $port                = null;//"3306";
  public $sock                = null;
  public $username            = 'root';
  public $password            = '';
  public $dbname              = '';
  public $tblPrefix           = '';
  public $timeZone            = null;

  public $cartType                 = 'Shopware';
  public $cartId                   = '';
  public $imagesDir                = '';
  public $categoriesImagesDir      = '';
  public $productsImagesDir        = '';
  public $manufacturersImagesDir   = '';
  public $categoriesImagesDirs     = '';
  public $productsImagesDirs       = '';
  public $manufacturersImagesDirs  = '';

  public $languages   = array();
  public $cartVars    = array();

  /**
   * @return mixed
   */
  public function create()
  {
    $cartType = $this->cartType;
    $obj = new M1_Config_Adapter_Shopware();
    $obj->cartType = $cartType;

    return $obj;
  }

  /**
   * @param array $data Data
   * @return mixed
   */
  public function productUpdateAction(array $data)
  {
    return array('error' => 'Action is not supported', 'data' => false);
  }

  /**
   * @param array $data Data
   * @return mixed
   */
  public function sendEmailNotifications(array $data)
  {
    return array('error' => 'Action is not supported', 'data' => false);
  }

  /**
   * @param array $data Data
   * @return mixed
   */
  public function triggerEvents(array $data)
  {
    return array('error' => 'Action is not supported', 'data' => false);
  }

  /**
   * @inheritDoc
   */
  public function setMetaData(array $data)
  {
    return array('error' => 'Action is not supported', 'data' => false);
  }

  /**
   *
   * @return mixed
   */
  public function getPaymentMethods(array $data)
  {
    return array('error' => 'Action is not supported', 'data' => false);
  }

  /**
   * Get Card ID string from request parameters
   * @return string
   */
  protected function _getRequestCartId()
  {
    return 'Shopware';
  }

  /**
   * @param $cartType
   * @return string
   */
  public function getAdapterPath($cartType)
  {
    return M1_STORE_BASE_DIR . M1_BRIDGE_DIRECTORY_NAME . DIRECTORY_SEPARATOR
      . "app" . DIRECTORY_SEPARATOR
      . "class" . DIRECTORY_SEPARATOR
      . "config_adapter" . DIRECTORY_SEPARATOR . $cartType . ".php";
  }

  /**
   * @param $source
   */
  public function setHostPort($source)
  {
    $source = trim($source);

    if ($source == '') {
      $this->host = 'localhost';
      return;
    }

    if (strpos($source, '.sock') !== false) {
      $socket = ltrim($source, 'localhost:');
      $socket = ltrim($socket, '127.0.0.1:');

      $this->host = 'localhost';
      $this->sock = $socket;

      return;
    }

    $conf = explode(":", $source);

    if (isset($conf[0]) && isset($conf[1])) {
      $this->host = $conf[0];
      $this->port = $conf[1];
    } elseif ($source[0] == '/') {
      $this->host = 'localhost';
      $this->port = $source;
    } else {
      $this->host = $source;
    }
  }

  /**
   * @return bool|M1_Mysqli|M1_Pdo
   */
  public function connect()
  {
    if (extension_loaded('pdo_mysql')) {
      $link = new M1_Pdo($this);
    } elseif (function_exists('mysqli_connect')) {
      $link = new M1_Mysqli($this);
    } else {
      $link = false;
    }

    return $link;
  }

  /**
   * @param $field
   * @param $tableName
   * @param $where
   * @return string
   */
  public function getCartVersionFromDb($field, $tableName, $where)
  {
    $version = '';

    $link = $this->connect();
    if (!$link) {
      return '[ERROR] MySQL Query Error: Can not connect to DB';
    }

    $result = $link->localQuery("
      SELECT " . $field . " AS version
      FROM " . $this->tblPrefix . $tableName . "
      WHERE " . $where
    );

    if (is_array($result) && isset($result[0]['version'])) {
      $version = $result[0]['version'];
    }

    return $version;
  }
}
