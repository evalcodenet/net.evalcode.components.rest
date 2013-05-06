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
   * @Application(foo)
   * @Resource(test)
   */
  class Rest_Resource_Test extends Rest_Resource
  {
    // PROPERTIES
    /**
     * @Inject(Http_Scriptlet_Request)
     *
     * @var Http_Scriptlet_Request
     */
    public $request;
    //--------------------------------------------------------------------------


    // ACCESSORS
    /**
     * @GET(pk/?date&log:false)
     *
     * @param \Components\Integer $pk_
     * @param \Components\Date $date_
     * @param \Components\Boolean $log_
     *
     * @return \Components\Date
     */
    public function bar(Integer $pk_, Date $date_=null, Boolean $log_=null)
    {
      if(null===$date_)
        return Date::now();

      return $date_;
    }
    //--------------------------------------------------------------------------
  }
?>
