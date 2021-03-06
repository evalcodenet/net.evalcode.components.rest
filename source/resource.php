<?php


namespace Components;


  /**
   * Rest_Resource
   *
   * @api
   * @package net.evalcode.components.rest
   *
   * @author evalcode.net
   */
  // TODO Support array|HashMap parameters.
  class Rest_Resource extends Http_Scriptlet
  {
    // STATIC ACCESSORS
    public static function serve($pattern_=null, $resource_=null)
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
      if(!($method=$uri_->shiftPathParam()))
        throw new Http_Exception('rest/resource', null, Http_Exception::NOT_FOUND);

      $resource=get_called_class();

      if(null===self::$m_methods || false===isset(self::$m_methods[$resource][$method]))
      {
        self::initializeMethods();

        if(false===isset(self::$m_methods[$resource][$method]))
          throw new Http_Exception('rest/resource', null, Http_Exception::NOT_FOUND);
      }

      $method=self::$m_methods[$resource][$method];
      if(false===isset($method['methods'][$context_->getRequest()->getMethod()]))
        throw new Http_Exception('rest/resource', null, Http_Exception::NOT_FOUND);

      if(isset($method['path']) && count($uri_->getPathParams())<count($method['path']))
        throw new Http_Exception('rest/resource', null, Http_Exception::NOT_FOUND);

      /* @var $resource \Components\Rest_Resource */
      $resource=new $resource();
      $resource->request=$context_->getRequest();
      $resource->response=$context_->getResponse();

      $params=[];
      if(isset($method['path']) || isset($method['query']))
      {
        $marshaller=Object_Marshaller::forMimetype($resource->response->getMimetype());

        foreach($method['path'] as $name=>$type)
          $params[$name]=$marshaller->unmarshal($uri_->shiftPathParam(), $type);

        if(isset($method['query']))
        {
          foreach($method['query'] as $name=>$options)
          {
            if($uri_->hasQueryParam($options['name']))
              $params[$name]=$marshaller->unmarshal($uri_->getQueryParam($options['name']), $options['type']);
            else if($options['value'])
              $params[$name]=$marshaller->unmarshal($options['value'], $options['type']);
            else
              $params[$name]=null;
          }
        }
      }

      if($result=call_user_func_array([$resource, $method['name']], $params))
        echo $marshaller->marshal($result);
    }
    //--------------------------------------------------------------------------


    // ACCESSORS
    public function invoke($method_, array $args_)
    {

    }
    //--------------------------------------------------------------------------


    // OVERRIDES
    /**
     * @see \Components\Object::equals() equals
     */
    public function equals($object_)
    {
      if($object_ instanceof self)
        return $this->hashCode()===$object_->hashCode();

      return false;
    }

    /**
     * @see \Components\Object::hashCode() hashCode
     */
    public function hashCode()
    {
      return \math\hasho($this);
    }

    /**
     * @see \Components\Object::__toString() __toString
     */
    public function __toString()
    {
      return sprintf('%s@%s{}', __CLASS__, $this->hashCode());
    }
    //--------------------------------------------------------------------------


    // IMPLEMENTATION
    private static $m_initialized=false;
    private static $m_resources=[];
    private static $m_methods;
    //-----


    private static function initializeMethods()
    {
      if(false===(self::$m_methods=Cache::get('components/rest/methods')))
        self::$m_methods=[];

      foreach(self::$m_resources as $resource)
      {
        if(isset(self::$m_methods[$resource]))
          continue;

        $annotations=Annotations::get($resource);

        foreach($annotations->getMethodAnnotations() as $methodName=>$methodAnnotations)
        {
          $httpMethods=[];
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

            $path=[];
            $query=[];
            foreach($parameters as $parameter)
            {
              $parameterAnnotations=$annotations->getParameterAnnotations($methodName, $parameter->name);

              if(isset($parameterAnnotations[Annotation_Param_Query::NAME]))
              {
                if(isset($parameterAnnotations[Annotation_Param_Query::NAME]->name))
                  $name=$parameterAnnotations[Annotation_Param_Query::NAME]->name;
                else
                  $name=$parameter->name;

                if(isset($parameterAnnotations[Annotation_Param_Query::NAME]->type))
                {
                  $type=$parameterAnnotations[Annotation_Param_Query::NAME]->type;

                  if(Primitive::isNative($type))
                    $type=Primitive::asBoxed($type);
                  else if($lookupType=Runtime_Classloader::lookup($type))
                    $type=$lookupType;
                }
                else
                {
                  if($type=$parameter->getClass())
                    $type=$type->name;
                  else
                    $type=String::TYPE;
                }

                if(isset($parameterAnnotations[Annotation_Param_Query::NAME]->default))
                  $value=$parameterAnnotations[Annotation_Param_Query::NAME]->default;
                else
                  $value=null;

                $query[$parameter->name]=[
                  'name'=>$name,
                  'type'=>$type,
                  'value'=>$value
                ];
              }
              else if($parameter->isOptional())
              {
                if($type=$parameter->getClass())
                  $query[$parameter->name]=$type->name;
                else
                  $query[$parameter->name]=String::TYPE;
              }
              else
              {
                if(isset($parameterAnnotations[Annotation_Param_Path::NAME]->type))
                {
                  $type=$parameterAnnotations[Annotation_Param_Path::NAME]->type;

                  if(Primitive::isNative($type))
                    $type=Primitive::asBoxed($type);
                  else if($lookupType=Runtime_Classloader::lookup($type))
                    $type=$lookupType;

                  $path[$parameter->name]=$type;
                }
                else
                {
                  if($type=$parameter->getClass())
                    $path[$parameter->name]=$type->name;
                  else
                    $path[$parameter->name]=String::TYPE;
                }
              }
            }

            $matches=[];
            preg_match('/\@return\s+([\\a-z]+)\n/i', $method->getDocComment(), $matches);

            $return=null;
            if(isset($matches[1]))
              $return=$matches[1];

            self::$m_methods[$resource][$methodName]=[
              'name'=>$methodName,
              'methods'=>$httpMethods,
              'path'=>$path,
              'query'=>$query,
              'return'=>$return
            ];
          }
        }
      }

      Cache::set('components/rest/methods', self::$m_methods);
    }

    private static function initialize()
    {
      Annotations::registerAnnotations([
        Annotation_Application::NAME=>Annotation_Application::TYPE,
        Annotation_Method_Delete::NAME=>Annotation_Method_Delete::TYPE,
        Annotation_Method_Get::NAME=>Annotation_Method_Get::TYPE,
        Annotation_Method_Options::NAME=>Annotation_Method_Options::TYPE,
        Annotation_Method_Post::NAME=>Annotation_Method_Post::TYPE,
        Annotation_Method_Put::NAME=>Annotation_Method_Put::TYPE,
        Annotation_Param_Path::NAME=>Annotation_Param_Path::TYPE,
        Annotation_Param_Query::NAME=>Annotation_Param_Query::TYPE
      ]);

      self::$m_initialized=true;
    }
    //--------------------------------------------------------------------------
  }
?>
