<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
 
/**
 * HelloWorld Model
 */
class QuantumFpModelQuantumFp extends JModelItem
{
        /**
         * @var string msg
        
        protected $msg, $users;
 
        /**
         * Get the message
         * @return string The message to be displayed to the user
         
		 
        public function getTable($type = 'QuantumFp', $prefix = 'QuantumFpTable', $config = array()) 
        {
                return JTable::getInstance($type, $prefix, $config);
        }
		
        public function getMessage() 
        {
                if (!isset($this->msg)) 
                {
                        $id = JRequest::getInt('id');
						
						// Get a TableHelloWorld instance
                       $db =& JFactory::getDBO();
                       $query = "SELECT views FROM #__quantumfp WHERE id=$id ";

						// Assign the message
                        $this->msg = $db->loadResult($query);
                }
                return $this->msg;
        }
		
        public function getUsers() 
        {
                if (!isset($this->users)) 
                {
                       $db =& JFactory::getDBO();
                       $query = "SELECT username, email FROM #__users";
                       $db->setQuery($query);
				       $this->users = $db->loadAssocList();
                }
                return $this->users;
        }*/

}
