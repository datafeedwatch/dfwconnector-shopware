<?php

namespace dfwconnector\Library\Bridge2cart;

abstract class M1_DatabaseLink
{
  protected static $_maxRetriesToConnect = 5;
  protected static $_sleepBetweenAttempts = 2;

  protected $_config = null;
  private $_databaseHandle = null;

  protected $_insertedId = 0;
  protected $_affectedRows = 0;

  /**
   * @param M1_Config_Adapter $config Config adapter
   * @return M1_DatabaseLink
   */
  public function __construct($config)
  {
    $this->_config = $config;
  }

  /**
   * @return void
   */
  public function __destruct()
  {
    $this->_releaseHandle();
  }

  /**
   * @return \stdClass|bool
   */
  private function _tryToConnect()
  {
    $triesCount = self::$_maxRetriesToConnect;

    $link = null;

    while (!$link) {
      if (!$triesCount--) {
        break;
      }
      $link = $this->_connect();
      if (!$link) {
        sleep(self::$_sleepBetweenAttempts);
      }
    }

    if ($link) {
      $this->_afterConnect($link);
      return $link;
    } else {
      return false;
    }
  }

  /**
   * Database handle getter
   * @return \stdClass
   */
  protected final function _getDatabaseHandle()
  {
    if ($this->_databaseHandle) {
      return $this->_databaseHandle;
    }
    if ($this->_databaseHandle = $this->_tryToConnect()) {
      return $this->_databaseHandle;
    } else {
      return($this->_errorMsg('Can not connect to DB'));
    }
  }

  /**
   * Close DB handle and set it to null; used in reconnect attempts
   * @return void
   */
  protected final function _releaseHandle()
  {
    if ($this->_databaseHandle) {
      $this->_closeHandle($this->_databaseHandle);
    }
    $this->_databaseHandle = null;
  }

  /**
   * Format error message
   * @param string $error Raw error message
   * @return string
   */
  protected final function _errorMsg($error)
  {
    $className = get_class($this);
    return "[$className] MySQL Query Error: $error";
  }

  /**
   * @param string $sql       SQL query
   * @param int    $fetchType Fetch type
   * @param array  $extParams Extended params
   * @return array
   */
  public final function query($sql, $fetchType, $extParams)
  {
    if ($extParams['set_names']) {
      $this->_dbSetNames($extParams['set_names']);
    }
    if ($extParams['disable_checks']) {
      $this->_dbDisableChecks();
    }
    $res = $this->_query($sql, $fetchType, $extParams['fetch_fields']);

    if ($extParams['disable_checks']) {
      $this->_dbEnableChecks();
    }
    return $res;
  }

  /**
   * Disable checks
   * @return void
   */
  private function _dbDisableChecks()
  {
    $this->localQuery('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0');
    $this->localQuery("SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
  }

  /**
   * Restore old mode before disable checks
   * @return void
   */
  private function _dbEnableChecks()
  {
    $this->localQuery("SET SQL_MODE=IFNULL(@OLD_SQL_MODE,'')");
    $this->localQuery("SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS,0)");
  }

  /**
   * @return bool|null|resource
   */
  protected abstract function _connect();

  /**
   * Additional database handle manipulations - e.g. select DB
   * @param  \stdClass $handle DB Handle
   * @return void
   */
  protected abstract function _afterConnect($handle);

  /**
   * Close DB handle
   * @param  \stdClass $handle DB Handle
   * @return void
   */
  protected abstract function _closeHandle($handle);

  /**
   * @param string $sql sql query
   * @return array
   */
  public abstract function localQuery($sql);

  /**
   * @param string $sql         Sql query
   * @param int    $fetchType   Fetch Type
   * @param bool   $fetchFields Fetch fields metadata
   * @return array
   */
  protected abstract function _query($sql, $fetchType, $fetchFields = false);

  /**
   * @return string|int
   */
  public function getLastInsertId()
  {
    return $this->_insertedId;
  }

  /**
   * @return int
   */
  public function getAffectedRows()
  {
    return $this->_affectedRows;
  }

  /**
   * @param  string $charset Charset
   * @return void
   */
  protected abstract function _dbSetNames($charset);

}
