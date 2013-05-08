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
    public static function serve($pattern_, $resource_=null)
    {
      if(false===self::$m_initialized)
        self::initialize();

      if(null===$resource_)
        $resource_=get_called_class();

      parent::serve($pattern_, $resource_);

      self::$m_resources[$resource_]=$resource_;
    }
    //--------------------------------------------------------------------------


    // STATIC ACCESSORS
    public static function dispatch(Http_Scriptlet_Context $context_, Uri $uri_)
    {
      if(null===self::$m_methods)
        self::initializeMethods();

      $resource=get_called_class();
      if(!($method=$uri_->shiftPathParam()) || false===isset(self::$m_methods[$resource][$method]))
        throw new Http_Exception('components/rest/resource', Http_Exception::NOT_FOUND);

      $method=self::$m_methods[$resource][$method];
      if(false===isset($method['methods'][$context_->getRequest()->getMethod()]))
        throw new Http_Exception('components/rest/resource', Http_Exception::NOT_FOUND);

      if(isset($method['path']) && count($uri_->getPathParams())<count($method['path']))
        throw new Http_Exception('components/rest/resource', Http_Exception::NOT_FOUND);

      /* @var $resource \Components\Rest_Resource */
      $resource=new $resource();
      $resource->request=$context_->getRequest();
      $resource->response=$context_->getResponse();

      $params=array();
      if(isset($method['path']) || isset($method['query']))
      {
        $marshaller=Object_Marshaller::forMimeType($resource->response->getMimeType());

        foreach($method['path'] as $name=>$type)
          $params[$name]=$marshaller->unmarshal($uri_->shiftPathParam(), $type);

        if(isset($method['query']))
        {
          foreach($method['query'] as $name=>$type)
          {
            $queryParamName=String::underscoreToCamelCase($name);

            if($uri_->hasQueryParam($queryParamName))
              $params[$name]=$marshaller->unmarshal($uri_->getQueryParam($queryParamName), $type);
            else
              $params[$name]=null;
          }
        }
      }

      if($result=call_user_func_array(array($resource, $method['name']), $params))
        echo $marshaller->marshal($result);
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
    private static $m_initialized=false;
    private static $m_resources=array();
    private static $m_methods;
    //-----


    private static function initializeMethods()
    {
      if(false===(self::$m_methods=Cache::get('components/rest/methods')))
      {
        self::$m_methods=array();

        foreach(self::$m_resources as $resource)
        {
          $annotations=Annotations::get($resource);

          foreach($annotations->getMethodAnnotations() as $methodName=>$methodAnnotations)
          {
            $httpMethods=array();
            foreach($methodAnnotations as $methodAnnotationName=>$methodAnnotation)
            {
              if($methodAnnotation instanceof Annotation_Method)
                $httpMethods[$methodAnnotationName]=$methodAnnotationName;
            }

            if(count($httpMethods))
            {
              $type=new \ReflectionClass($resource);
              $method=$type->getMethod($methodName);

              $parameters=$method->getParameters();

              $path=array();
              $query=array();
              foreach($parameters as $parameter)
              {
                if($parameter->isOptional())
                  $query[$parameter->name]=$parameter->getClass()->name;
                else
                  $path[$parameter->name]=$parameter->getClass()->name;
              }

              $matches=array();
              preg_match('/\@return\s+([\\a-z]+)\n/i', $method->getDocComment(), $matches);

              $return=null;
              if(isset($matches[1]))
                $return=$matches[1];

              self::$m_methods[$resource][$methodName]=array(
                'name'=>$methodName,
                'methods'=>$httpMethods,
                'path'=>$path,
                'query'=>$query,
                'return'=>$return
              );
            }
          }
        }

        Cache::set('components/rest/methods', self::$m_methods);
      }
    }

    private static function initialize()
    {
      Annotations::registerAnnotations(array(
        Annotation_Application::NAME=>Annotation_Application::TYPE,
        Annotation_Method_Delete::NAME=>Annotation_Method_Delete::TYPE,
        Annotation_Method_Get::NAME=>Annotation_Method_Get::TYPE,
        Annotation_Method_Options::NAME=>Annotation_Method_Options::TYPE,
        Annotation_Method_Post::NAME=>Annotation_Method_Post::TYPE,
        Annotation_Method_Put::NAME=>Annotation_Method_Put::TYPE
      ));

      self::$m_initialized=true;
    }
    //--------------------------------------------------------------------------
  }
?>
