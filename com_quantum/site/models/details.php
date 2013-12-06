<?php

// No direct access to this file

defined('_JEXEC') or die('Restricted access');

 

// import Joomla modelitem library

jimport('joomla.application.component.modelitem');

 

/**

 * HelloWorld Model

 */

class QuantumFpModelDetails extends JModelItem

{

        /**

         * @var string msg

         */

        protected $users;

 

        /**

         * Get the message

         * @return string The message to be displayed to the user

         */

        private function combineMyArrays($arr1, $arr2) 

        {

		            $comb = array();

                    $comb = array_merge($arr1, $arr2);

					$resul = array();

				    foreach($comb as $val) {

					      foreach($val as $key2 => $val2) {

                              $resul[0][$key2] = $val2;

					      }

					}



                return $resul;

        }

		

        public function getUsers() 

        {

                if (!isset($this->users)) 

                {

				        

                       $user = JFactory::getUser();

                       $id = $user->id;

                       $db =& JFactory::getDBO();

                       $query = $query = "SELECT id, name, username, email FROM #__users WHERE jos_users.id=$id";

					   $query2 = "SELECT title, cell, tel, fax, subscribe  FROM #__quantum_users WHERE user_id = $id";

                       $db->setQuery($query);

				       $users1 = $db->loadAssocList();

					   $db->setQuery($query2);

					   $users2 = $db->loadAssocList();

					   $this->users = $this->combineMyArrays($users1, $users2);

                }

                return $this->users;

        }



}

