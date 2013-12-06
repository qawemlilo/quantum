<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * Hello World Component Controller
 */
class QuantumFpController extends JController
{
	function display() {
       if(!JRequest::getVar('view')) {
              JRequest::setVar('view', 'addclient');
       }
            parent::display();    
    }
}
