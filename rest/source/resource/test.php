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
   * @Application(rest)
   */
  class Rest_Resource_Test extends Rest_Resource
  {
    // ACCESSORS
    /**
     * @GET
     *
     * @param \Components\Integer $pk_
     * @param \Components\Date $date_
     * @param \Components\Boolean $log_
     *
     * @return \Components\Date
     */
    public function poke(\Components\Integer $pk_, \Components\Date $date_=null, \Components\Boolean $log_=null)
    {
      Log::info('components/rest/resource/test', 'Poke %s', $date_);

      return $date_;
    }
    //--------------------------------------------------------------------------
  }
?>
