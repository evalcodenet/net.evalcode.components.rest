<?php


namespace Components;


  /**
   * Rest_Test_Unit_Suite
   *
   * @package net.evalcode.components.rest
   * @subpackage test.unit
   *
   * @author evalcode.net
   */
  class Rest_Test_Unit_Suite implements Test_Unit_Suite
  {
    // OVERRIDES
    public function name()
    {
      return 'rest/test/unit/suite';
    }

    public function cases()
    {
      return array(
        'Components\\Rest_Test_Unit_Case_Resource'
      );
    }
    //--------------------------------------------------------------------------
  }
?>
