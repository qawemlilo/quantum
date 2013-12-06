<?php

// No direct access to this file

defined('_JEXEC') or die('Restricted access');

 

// import Joomla modelitem library

jimport('joomla.application.component.modelitem');

 

/**

 * HelloWorld Model

 */

class QuantumFpModelUploadFile extends JModelItem

{

        /**

         * @var string msg

         */

        protected $users;

 

        /**

         * Get the message

         * @return string The message to be displayed to the user

         */

		

        public function getUsers() 

        {

                if (!isset($this->users)) 

                {

                       $db =& JFactory::getDBO();

                         
                       $query = "SELECT DISTINCT user_id FROM #__quantum_users";

                       $db->setQuery($query); 
                       
                       $id_arr = $db->loadResultArray();
                       
                       $id_arr = implode(",", $id_arr);
                       
                       $query_two = "SELECT id, name FROM #__users WHERE id in ($id_arr) ORDER BY name";
                       
                       $db->setQuery($query_two);

				               $this->users = $db->loadAssocList();

                }

                return $this->users;

        }



}

