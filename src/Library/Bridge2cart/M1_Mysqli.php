<?php

namespace dfwconnector\Library\Bridge2cart;

class M1_Mysqli extends M1_DatabaseLink
{
  protected function _connect()
  {
    return mysqli_connect(
      $this->_config->host,
      $this->_config->username,
      $this->_config->password,
      $this->_config->dbname,
      $this->_config->port ? $this->_config->port : null,
      $this->_config->sock
    );
  }

  /**
   * @return void
   */
  protected function _afterConnect($handle)
  {
    mysqli_select_db($handle, $this->_config->dbname);
  }

  /**
   * @inheritdoc
   */
  public function localQuery($sql)
  {
    $result = array();
    /**
     * @var mysqli $databaseHandle
     */
    $databaseHandle = $this->_getDatabaseHandle();
    $sth = mysqli_query($databaseHandle, $sql);
    if (is_bool($sth)) {
      return $sth;
    }
    while (($row = mysqli_fetch_assoc($sth))) {
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
      'fetchedFields' => ''
    );

    $fetchMode = MYSQLI_ASSOC;
    switch ($fetchType) {
      case 3:
        $fetchMode = MYSQLI_BOTH;
        break;
      case 2:
        $fetchMode = MYSQLI_NUM;
        break;
      case 1:
        $fetchMode = MYSQLI_ASSOC;
        break;
      default:
        break;
    }

    /**
     * @var mysqli $databaseHandle
     */
    $databaseHandle = $this->_getDatabaseHandle();

    $res = mysqli_query($databaseHandle, $sql);

    $triesCount = 10;
    while (mysqli_errno($databaseHandle) == 2013) {
      if (!$triesCount--) {
        break;
      }
      // reconnect
      $this->_releaseHandle();
      if (isset($_REQUEST['set_names'])) {
        mysqli_set_charset($databaseHandle, $_REQUEST['set_names']);
      }

      // execute query once again
      $res = mysqli_query($databaseHandle, $sql);
    }

    if (($errno = mysqli_errno($databaseHandle)) != 0) {
      $result['message'] = $this->_errorMsg($errno . ', ' . mysqli_error($databaseHandle));
      return $result;
    }

    $this->_affectedRows = mysqli_affected_rows($databaseHandle);
    $this->_insertedId = mysqli_insert_id($databaseHandle);

    if (is_bool($res)) {
      $result['result'] = $res;
      return $result;
    }

    if ($fetchFields) {
      $result['fetchedFields'] = mysqli_fetch_fields($res);
    }


    $rows = array();
    while ($row = mysqli_fetch_array($res, $fetchMode)) {
      $rows[] = $row;
    }

    $result['result'] = $rows;

    mysqli_free_result($res);

    return $result;
  }

  /**
   * @inheritdoc
   */
  protected function _dbSetNames($charset)
  {
    /**
     * @var mysqli $databaseHandle
     */
    $databaseHandle = $this->_getDatabaseHandle();
    mysqli_set_charset($databaseHandle, $charset);
  }

  /**
   * @return void
   */
  protected function _closeHandle($handle)
  {
    mysqli_close($handle);
  }

}
