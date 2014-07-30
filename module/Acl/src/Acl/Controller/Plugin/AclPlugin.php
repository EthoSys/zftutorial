<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Acl\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin,
    Zend\Session\Container as SessionContainer,
    Zend\Permissions\Acl\Acl,
    Zend\Permissions\Acl\Role\GenericRole as Role,
    Zend\Permissions\Acl\Resource\GenericResource as Resource;


class AclPlugin extends AbstractPlugin{
    protected  $sesscontainer;
    
    private function  getSessContainer(){
        if(!$this->sesscontainer){
            $this->sesscontainer = new SessionContainer('zftutorial');
        }
        return $this->sesscontainer;
    }
    
    public function doAuthorization($e){
        $acl = new Acl();
        $acl->deny();
        
        // ROLES
        $acl->addRole(new Role('anonymous'));
        $acl->addRole(new Role('user'), 'anonymous');
        $acl->addRole(new Role('admin'), 'user');
        
        // RESOUREC
        
        $acl->addResource('application');
        $acl->addResource('album');
        
        //Application
        
        $acl->allow('anonymous', 'application', 'index:index');
        $acl->allow('anonymous', 'application', 'profile:index');
        
        //Album
        $acl->allow('anonymous', 'album', 'album:index');
        //$acl->allow('anonymous', 'album', 'album:add');
        $acl->deny('anonymous', 'album', 'album:hello');
        //$acl->allow('anonymous', 'album', 'album:view');
        //$acl->allow('anonymous', 'album', 'album:edit');
        
        
        $controller = $e->getTarget();
        $controllerClass = get_class($controller);
        $moduleName = strtolower(substr($controllerClass,0,  strpos($controllerClass, '\\')));
        $role = (! $this->getSessContainer()->role ) ? 'anonymous' : $this->getSessContainer()->role; 
        $routeMatch = $e->getRouteMatch();
        
        $actionName = strtolower($routeMatch->getParam('action', 'not-found'));
        $controllerName1 =  $routeMatch->getParam('controller', 'not-found');         
        $controllerNameArr = explode('\\', $controllerName1);         
        $controllerName = strtolower(array_pop($controllerNameArr));
        
        
        
        if(!$acl->isAllowed($role, $moduleName, $controllerName.':'.$actionName)){
            $router = $e->getRouter();
            $url = $router->assemble(array(), array('name'=>'application'));
            $response = $e->getResponse();
            $response->setStatusCode(302);
            
            $response->getHeaders()->addHeaderLine('Location', $url);
            $e->stopPropagation();                    
        }
        
        
    }
}