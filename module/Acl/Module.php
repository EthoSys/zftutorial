<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Acl;

use Zend\ModuleManager\ModuleManager; // add for module specific layout
// add For ACL
use Zend\Mvc\MvcEvent,
    Zend\ModuleManager\Feature\AutoloaderProviderInterface,
    Zend\ModuleManager\Feature\ConfigProviderInterface;


class Module
{
     public function getConfig()
     {
         return include __DIR__ . '/config/module.config.php';
     }
     
     public function onBootstrap(MvcEvent $e){         
         $eventManager = $e->getApplication()->getEventManager();
         $eventManager->attach('route',array($this, 'localConfiguration'), 2);
         //
     }
     
     public  function localConfiguration(MvcEvent $e){         
         
        $application   =   $e->getApplication();
        $sm            =   $application->getServiceManager();
        $sharedManager =   $application->getEventManager()->getSharedManager();
        
        
        $router  = $sm->get('router');
        $request = $sm->get('request');
       
        $matchedRoute = $router->match($request);
       
        if(null != $matchedRoute){           
            $sharedManager->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch' , function($e) use ($sm){                
                $sm->get('ControllerPluginManager')->get('AclPlugin')->doAuthorization($e);
            },2);
        }    
     }
     
     public function init(ModuleManager $moduleManager){
         $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
         $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e){
              $controller = $e->getTraget();
         },100);
     }
     
     public function getAutoloaderConfig(){
         return array(
           'Zend\Loader\ClassMapAutoloader' => array(
              __DIR__.'/autoload_classmap.php',  
           ),
           'Zend\Loader\StandardAutoloader' => array(
               'namespace' => array(
               __NAMESPACE__=>__DIR__.'/src/'.__NAMESPACE__,
               ),     
           ),             
         );
     }
             
}