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


    // ACCESSORS
    public function dispatch(array $parameters_)
    {
      $this->initialize();

      $params=Http_Scriptlet_Request::getUri()->getPathParams();
      $context=self::$m_applications;
      while($chunk=array_shift($params))
      {
        if(is_array($context) && isset($context[$chunk]))
          $context=$context[$chunk];
      }
      var_dump($context, $chunk, $params);
      print_r(Http_Scriptlet_Request::getUri());

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
    private static $m_applications;
    private static $m_resources=array();
    //-----


    private function initialize()
    {
      if(false===(self::$m_applications=Cache::get('components/rest/applications')))
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

          $application=&self::$m_applications;
          if(($applicationAnnotation=$annotations->getTypeAnnotation(Annotation_Application::NAME))
            && ($applicationName=$applicationAnnotation->value))
            $application=&self::$m_applications[$applicationName];

          if(!$resourceAnnotation=$annotations->getTypeAnnotation(Annotation_Resource::NAME))
            continue;

          $resource=$resourceAnnotation->value;
          foreach($annotations->getMethodAnnotations() as $methodName=>$methodAnnotations)
          {
            foreach($methodAnnotations as $methodAnnotation)
            {
              if($methodAnnotation instanceof Annotation_Method)
                $application[$resource][$methodName]=$methodAnnotation->value;
            }
          }
        }

        Cache::set('components/rest/applications', self::$m_applications);
      }
    }
    //--------------------------------------------------------------------------
  }
?>
