<?php


namespace Components;


  /**
   * Rest_Test_Unit_Case_Resource
   *
   * @package net.evalcode.components.rest
   * @subpackage test.unit.case
   *
   * @author evalcode.net
   */
  class Rest_Test_Unit_Case_Resource implements Test_Unit_Case
  {
    // TESTS
    /**
     * @test
     * @profile
     */
    public function testDispatch()
    {
      split_time('reset');

      Rest_Test_Unit_Case_Resource_Foo::serve('resource/foo');
      split_time('Invoke Rest_Test_Unit_Case_Resource_Foo::serve(resource/foo)');

      Http_Scriptlet_Context::push(new Http_Scriptlet_Context(Environment::uriComponents()));
      split_time('Initialize Components\\Http_Scriptlet_Context');

      $uri=Uri::valueOf(Environment::uriComponents('rest', 'resource', 'foo', 'poke', '1234.json'));
      split_time("Invoke Uri::valueOf($uri)");

      ob_start();
      split_time('reset');

      Http_Scriptlet_Context::current()->dispatch($uri, Http_Scriptlet_Request::METHOD_GET);

      split_time("Invoke Components\\Http_Scriptlet_Context\$dispatch([$uri], GET)");
      $result=ob_get_clean();

      assertEquals(json_encode(true), $result);
      split_time('reset');

      $uri=Uri::valueOf(Environment::uriComponents('rest', 'resource', 'foo', 'poke', '1234.json'));
      $uri->setQueryParam('log', 'false');
      split_time("Invoke Uri::valueOf($uri)");

      ob_start();
      split_time('reset');

      Http_Scriptlet_Context::current()->dispatch($uri, Http_Scriptlet_Request::METHOD_GET);
      split_time("Invoke Components\\Http_Scriptlet_Context\$dispatch([$uri], GET)");

      $result=ob_get_clean();

      assertEquals(json_encode(false), $result);
    }
    //--------------------------------------------------------------------------
  }


  /**
   * Rest_Test_Unit_Case_Resource_Foo
   *
   * @package net.evalcode.components.rest
   * @subpackage test.unit.case
   *
   * @author evalcode.net
   *
   * @application test
   */
  class Rest_Test_Unit_Case_Resource_Foo extends Rest_Resource
  {
    // ACCESSORS
    /**
     * @GET
     */
    public function poke(Integer /** @pathParam */ $pk_,
      Boolean /** @queryParam name=log, default=true */ $log_=null)
    {
      return $log_;
    }
    //--------------------------------------------------------------------------
  }
?>
