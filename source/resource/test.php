<?php


namespace Components;


  /**
   * Rest_Resource_Test
   *
   * @package net.evalcode.components.rest
   * @subpackage resource
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
      Color /** @queryParam name=color */ $color_=null,
      Boolean /** @queryParam name=log, default=true */ $log_=null)
    {
      if(null===$color_)
        $color_=Color::white();

      Log::info('rest/resource/test', 'Poke %s', $color_);

      return $color_->toRgbString();
    }

    /**
     * @GET
     */
    public function country(I18n_Country /** @pathParam */ $country_)
    {
      return $country_->title();
    }
    //--------------------------------------------------------------------------
  }
?>
