<?php declare(strict_types=1);

namespace dfwconnector\Library;

class Store
{
  public $rootPath = '';
  public $bridgePath = '';
  public $configFilePath = '';
  public $bridgeFilePath = '';
  const ERROR_MESSAGE = 'You do not have a permission. Please set an appropriate owner of the file system and the permission for the root directory.';

  public function __construct($rootPath = '')
  {
    $this->rootPath = $rootPath;
    $this->bridgePath = $this->rootPath . '/Bridge2cart/';
    $this->configFilePath = $this->bridgePath . 'config.php';
    $this->bridgeFilePath = $this->bridgePath . 'M1_Bridge.php';
  }

  public function getStoreKey()
  {
    if (file_exists($this->configFilePath)) {
      require_once($this->configFilePath);
      return M1_TOKEN;
    }

    return 'Bridge is not connected yet';
  }

  public function updateToken($token)
  {
    if (!is_writable($this->rootPath)) {
      return ['error' => self::ERROR_MESSAGE];
    }

    if (file_exists($this->configFilePath) && is_writable($this->configFilePath)) {
      $config = fopen($this->configFilePath, 'w');
      $write = fwrite($config, "<?php define('M1_TOKEN', '" . $token . "');");
    } else {
      return ['error' =>
        sprintf('You do not have a permission to %s or this file does not exist!'
          . PHP_EOL . 'Please reinstall plugin!', $this->configFilePath)];
    }

    if (($config === false) || ($write === false) || (fclose($config) === false)) {
      return false;
    }

    return true;
  }

  public function isBridgeExist()
  {
    if (is_dir($this->bridgePath)
      && file_exists($this->bridgeFilePath)
      && file_exists($this->configFilePath)
    ) {
      return true;
    }

    return false;
  }

  public static function generateStoreKey()
  {
    $bytesLength = 256;

    if (function_exists('random_bytes')) { // available in PHP 7
      return md5(random_bytes($bytesLength));
    }

    if (function_exists('mcrypt_create_iv')) {
      $bytes = mcrypt_create_iv($bytesLength, MCRYPT_DEV_URANDOM);
      if ($bytes !== false && strlen($bytes) === $bytesLength) {
        return md5($bytes);
      }
    }

    if (function_exists('openssl_random_pseudo_bytes')) {
      $bytes = openssl_random_pseudo_bytes($bytesLength);
      if ($bytes !== false) {
        return md5($bytes);
      }
    }

    if (file_exists('/dev/urandom') && is_readable('/dev/urandom')) {
      $frandom = fopen('/dev/urandom', 'r');
      if ($frandom !== false) {
        return md5(fread($frandom, $bytesLength));
      }
    }

    $rand = '';
    for ($i = 0; $i < $bytesLength; $i++) {
      $rand .= chr(mt_rand(0, 255));
    }

    return md5($rand);
  }
}
