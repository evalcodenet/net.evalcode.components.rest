<?php


namespace Components;


  /**
   * Rest_Resource
   *
   * @package net.evalcode.components
   * @subpackage rest
   *
   * @author evalcode.net
   */
  class Rest_Resource extends Http_Scriptlet
  {
    // STATIC ACCESSORS
    public static function register($resource_=null)
    {
      if(null===$resource_)
        $resource_=get_called_class();

      self::$m_resources[$resource_]=$resource_;
    }
    //--------------------------------------------------------------------------


    // STATIC ACCESSORS
    public static function dispatch(Http_Scriptlet_Context $context_, Uri $uri_)
    {
      var_dump((string)$uri_);
      var_dump((string)$context_->getContextRoot());
      var_dump((string)$context_->getResponse()->getMimeType());
      var_dump((string)$context_->getContextUri());
      die();
      if(null===self::$m_routes)
        $this->initialize();

      $params=array();
      $segments=Http_Scriptlet_Request::getUri()->getPathParams();

      $pattern=null;
      $resource=null;

      while(count($segments))
      {
        $params[]=array_pop($segments);
        $path=implode('/', $segments);

        if(isset(self::$m_routes[$path]))
        {
          $resource=self::$m_routes[$path][0];
          $pattern=self::$m_routes[$path][1];

          break;
        }
      }

      if(null===$resource)
        throw new Http_Exception('components/rest/resource', Http_Exception::NOT_FOUND);
    }
    //--------------------------------------------------------------------------


    // OVERRIDES
    /**
     * (non-PHPdoc)
     * @see Components.Object::equals()
     */
    public function equals($object_)
    {
      if($object_ instanceof self)
        return $this->hashCode()===$object_->hashCode();

      return false;
    }

    /**
     * (non-PHPdoc)
     * @see Components.Object::hashCode()
     */
    public function hashCode()
    {
      return object_hash($this);
    }

    /**
     * (non-PHPdoc)
     * @see Components.Object::__toString()
     */
    public function __toString()
    {
      return sprintf('%s@%s{}', __CLASS__, $this->hashCode());
    }
    //--------------------------------------------------------------------------


    // IMPLEMENTATION
    private static $m_routes;
    private static $m_resources=array();
    //-----


    private function initialize()
    {
      if(false===(self::$m_routes=Cache::get('components/rest/routes')))
      {
        Annotations::registerAnnotations(array(
          Annotation_Application::NAME=>Annotation_Application::TYPE,
          Annotation_Resource::NAME=>Annotation_Resource::TYPE,
          Annotation_Method_Delete::NAME=>Annotation_Method_Delete::TYPE,
          Annotation_Method_Get::NAME=>Annotation_Method_Get::TYPE,
          Annotation_Method_Options::NAME=>Annotation_Method_Options::TYPE,
          Annotation_Method_Post::NAME=>Annotation_Method_Post::TYPE,
          Annotation_Method_Put::NAME=>Annotation_Method_Put::TYPE
        ));

        foreach(self::$m_resources as $type)
        {
          $annotations=Annotations::get($type);

          $applicationName=null;
          if(($applicationAnnotation=$annotations->getTypeAnnotation(Annotation_Application::NAME))
            && $applicationAnnotation->value)
            $applicationName=$applicationAnnotation->value;

          if(!$resourceAnnotation=$annotations->getTypeAnnotation(Annotation_Resource::NAME))
            continue;

          $resource=$resourceAnnotation->value;
          foreach($annotations->getMethodAnnotations() as $methodName=>$methodAnnotations)
          {
            foreach($methodAnnotations as $methodAnnotation)
            {
              if($methodAnnotation instanceof Annotation_Method)
              {
                if(null===$applicationName)
                  self::$m_routes["$resource/$methodName"]=array($type, $methodAnnotation->value);
                else
                  self::$m_routes["$applicationName/$resource/$methodName"]=array($type, $methodAnnotation->value);
              }
            }
          }
        }

        Cache::set('components/rest/routes', self::$m_routes);
      }
    }
    //--------------------------------------------------------------------------
  }
?>
