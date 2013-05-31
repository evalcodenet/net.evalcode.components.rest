<?php


namespace Components;


  /**
   * Rest_Resource_Test
   *
   * @package net.evalcode.components
   * @subpackage rest.resource
   *
   * @author evalcode.net
   *
   * @application rest
   */
  class Rest_Resource_Test extends Rest_Resource
  {
    // ACCESSORS
    /**
     * @GET
     */
    public function poke(/** @pathParam type=integer */ $pk_,
      Date /** @queryParam name=date */ $date_=null,
      Boolean /** @queryParam name=log, default=true */ $log_=null)
    {
      Log::info('rest/resource/test', 'Poke %s', $date_);

      return Color::forHexString('cecfd0');
    }
    //--------------------------------------------------------------------------
  }
?>
