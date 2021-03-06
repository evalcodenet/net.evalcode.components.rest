<?php


namespace Components;


  /**
   * Annotation_Param_Query
   *
   * @api
   * @package net.evalcode.components.rest
   * @subpackage annotation.param
   *
   * @author evalcode.net
   *
   * @property string name
   * @property string type
   * @property string default
   */
  final class Annotation_Param_Query extends Annotation
  {
    // PREDEFINED PROPERTIES
    /**
     * queryParam
     *
     * @var string
     */
    const NAME='queryParam';
    /**
     * Annotation_Param_Query
     *
     * @var string
     */
    const TYPE=__CLASS__;
    //--------------------------------------------------------------------------
  }
?>
