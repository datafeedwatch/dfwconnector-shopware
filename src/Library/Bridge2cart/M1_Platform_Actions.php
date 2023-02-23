<?php

namespace dfwconnector\Library\Bridge2cart;

interface M1_Platform_Actions
{
  /**
   * @param array $data
   *
   * @return mixed
   */
  public function productUpdateAction(array $data);

  /**
   * @param array $data
   *
   * @return mixed
   */
  public function sendEmailNotifications(array $data);

  /**
   * @param array $data
   *
   * @return mixed
   */
  public function triggerEvents(array $data);

  /**
   * @param array $data Data
   *
   * @return mixed
   */
  public function setMetaData(array $data);

  /**
   * @param array $data Data
   *
   * @return mixed
   */
  public function getPaymentMethods(array $data);

}
