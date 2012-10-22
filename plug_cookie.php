<?php

// error_reporting(E_ALL);

// no direct access
defined('_JEXEC') or die('Restricted access');

$mainframe->registerEvent('onAfterRoute', 'plgSystemCookie');

// Import library dependencies
jimport('joomla.plugin.plugin');

class plgSystemCookie extends JPlugin{

    function checkUrls($urls, $current){

        //create JRoute Object for current url
        $current = JFactory::getURI($current);
        $current->delVar('Itemid');

        foreach($urls as $url){

            //create JRoute Object for url set in the backend
            $link = JFactory::getURI($url);
            $link->delVar('Itemid');

            //compare arrays of uri params
            if($link->_vars == $current->_vars){
                return true;
            }
        }
        return false;
    }

	function onAfterRoute(){
		global $mainframe;

        $plugin =& JPluginHelper::getPlugin('system', 'plug_cookie');
        if(is_object($plugin)){
            $pluginParams = new JParameter($plugin->params);
        }else{
            $pluginParams = new JParameter('');
        }

        //checks is plugin is being called from frontend
		if($mainframe->isAdmin()){
			return false;
		}

        // str_replace(JURI::base(),"",JURI::current()) -- current url without domain.
        // explode($this->getParam("urls"),",") -- list of urls defined at the backend
        $urls    = explode(",",$pluginParams->get('urls'));
        $current = str_replace(JURI::base(),"",JURI::getInstance()->toString());


        //builds url for current url
        $router =& JSite::getRouter();
        $current = "index.php?".JURI::buildQuery($router->getVars());

        //sets the cookie if in defined urls
        if($this->checkUrls($urls, $current)){
            switch($pluginParams->get('expire')){
                case "not_set": setcookie("plug_cookie",$current, 0, '/');
                                break;
                case "30"     : setcookie("plug_cookie",$current, mktime() + 60 * 60 * 24 * 30, '/');
                                break;
                case "90"     : setcookie("plug_cookie",$current, mktime() + 60 * 60 * 24 * 90, '/');
                                break;
                case "180"    : setcookie("plug_cookie",$current, mktime() + 60 * 60 * 24 * 180, '/');
                                break;
                case "365"    : setcookie("plug_cookie",$current, mktime() + 60 * 60 * 24 * 365, '/');
                                break;
                default       : setcookie("plug_cookie",$current, 0, '/');
                                break;
            }
        }

        $cookie_redirect = JRequest::getVar('plug_cookie', null ,"COOKIE");

        //checks is this is homepage
        if(JURI::base() == JURI::current()){
            // if(isset($_COOKIE["plug_cookie"])){
            if(isset($cookie_redirect) && ($cookie_redirect != "")){
                if($pluginParams->get('message') == 'on'){
                    $mainframe->redirect($_COOKIE["plug_cookie"], 'Redirected by Cookie Plugin!!!', 'message');
                }else{
                    $mainframe->redirect($_COOKIE["plug_cookie"]);
                }
            }
         }
	}
}