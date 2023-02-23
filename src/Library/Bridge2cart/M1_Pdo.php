<?php

namespace dfwconnector\Library\Bridge2cart;

class M1_Pdo extends M1_DatabaseLink
{
  public $noResult = array(
    'delete', 'update', 'move', 'truncate', 'insert', 'set', 'create', 'drop', 'replace', 'start transaction', 'commit'
  );

  /**
   * @return bool|\PDO
   */
  protected function _connect()
  {
    try {
      $dsn = 'mysql:dbname=' . $this->_config->dbname . ';host=' . $this->_config->host;
      if ($this->_config->port) {
        $dsn .= ';port='. $this->_config->port;
      }
      if ($this->_config->sock != null) {
        $dsn .= ';unix_socket=' . $this->_config->sock;
      }

      $link = new \PDO($dsn, $this->_config->username, $this->_config->password);
      $link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

      return $link;

    } catch (\PDOException $e) {
      return false;
    }
  }

  /**
   * @param  \PDO $handle DB Handle
   * @return void
   */
  protected function _afterConnect($handle)
  {
  }

  /**
   * @inheritdoc
   */
  public function localQuery($sql)
  {
    $result = array();
    /**
     * @var \PDO $databaseHandle
     */
    $databaseHandle = $this->_getDatabaseHandle();
    $sth = $databaseHandle->query($sql);

    foreach ($this->noResult as $statement) {
      if (!$sth || strpos(strtolower(trim($sql)), $statement) === 0) {
        return true;
      }
    }

    while (($row = $sth->fetch(\PDO::FETCH_ASSOC)) != false) {
      $result[] = $row;
    }

    return $result;
  }

  /**
   * @inheritdoc
   */
  protected function _query($sql, $fetchType, $fetchFields = false)
  {
    $result = array(
      'result'        => null,
      'message'       => '',
      'fetchedFields' => array()
    );

    /**
     * @var \PDO $databaseHandle
     */
    $databaseHandle = $this->_getDatabaseHandle();

    switch ($fetchType) {
      case 3:
        $databaseHandle->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_BOTH);
        break;
      case 2:
        $databaseHandle->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_NUM);
        break;
      case 1:
      default:
        $databaseHandle->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        break;
    }

    try {
      $res = $databaseHandle->query($sql);
      $this->_affectedRows = $res->rowCount();
      $this->_insertedId = $databaseHandle->lastInsertId();
    } catch (\PDOException $e) {
      $result['message'] = $this->_errorMsg($e->getCode() . ', ' . $e->getMessage());
      return $result;
    }

    foreach ($this->noResult as $statement) {
      if (!$res || strpos(strtolower(trim($sql)), $statement) === 0) {
        $result['result'] = true;
        return $result;
      }
    }

    $rows = array();
    while (($row = $res->fetch()) !== false) {
      $rows[] = $row;
    }

    if ($fetchFields) {
      $fetchedFields = array();
      $columnCount = $res->columnCount();
      for ($column = 0; $column < $columnCount; $column++) {
        $fetchedFields[] = $res->getColumnMeta($column);
      }
      $result['fetchedFields'] = $fetchedFields;
    }

    $result['result'] = $rows;

    unset($res);
    return $result;
  }

  /**
   * @param  \PDO $handle DB Handle
   * @return void
   */
  protected function _closeHandle($handle)
  {
  }

  /**
   * @inheritdoc
   */
  protected function _dbSetNames($charset)
  {
    /**
     * @var \PDO $dataBaseHandle
     */
    $dataBaseHandle = $this->_getDatabaseHandle();
    $dataBaseHandle->exec('SET NAMES ' . $dataBaseHandle->quote($charset));
    $dataBaseHandle->exec('SET CHARACTER SET ' . $dataBaseHandle->quote($charset));
    $dataBaseHandle->exec('SET CHARACTER_SET_CONNECTION = ' . $dataBaseHandle->quote($charset));
  }

}
