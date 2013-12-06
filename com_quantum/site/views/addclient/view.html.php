<?php
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
jimport( 'joomla.environment.request' );
jimport('joomla.user.helper');
jimport('joomla.filesystem.file');

function isAllowed($chech_perms, $user_perms) {
    $is_allowed = false;
	
    if (is_int($chech_perms)) {
        foreach($user_perms as $v) {
	        if($v == $chech_perms) $is_allowed = true;
        }	
	}
	return $is_allowed;
}

function checkEmail($str) {
	return preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $str);
}

function send_mail($from,$to,$subject,$body) 
{
	$headers = '';
	$headers .= "From: $from\n";
	$headers .= "Reply-to: $from\n";
	$headers .= "Return-Path: $from\n";
	$headers .= "Message-ID: <" . md5(uniqid(time())) . "@" . $_SERVER['SERVER_NAME'] . ">\n";
	$headers .= "MIME-Version: 1.0\n";
  $headers .= "X-Priority: 1(Highest)\r\n";
  $headers .= "X-MSMail-Priority: High\r\n";
  $headers .= "Importance: High\r\n";
	$headers .= "Date: " . date('r', time()) . "\n";

	mail($to,$subject,$body,$headers);
}


class quantumUser {

    private $myarr, $id, $output = '';

    private function processForm() {
       $arr = array();
	   $arr['title'] = JRequest::getWord('title', '', 'POST');
	   $arr['fullname'] = JRequest::getString('fullname', '', 'POST');
	   $arr['email'] = JRequest::getString('email', '', 'POST');
	   $arr['username'] = JRequest::getWord('username', '', 'POST'); 
	   $arr['cell'] = JRequest::getInt('cell', '', 'POST');
	   $arr['tel'] = JRequest::getInt('tel', '', 'POST');
	   $arr['fax'] = JRequest::getInt('fax', '', 'POST' ); 
	   $arr['password'] = JRequest::getString('password', '', 'POST');
	   $arr['subscribe'] = JRequest::getString('subscribe', '', 'POST');
     
       foreach($arr as $k=>$v) {
	       if (!$v && !($k == 'subscribe' || $k == 'tel' || $k == 'fax' || $k == 'cell')) {
               echo "<script language=\"JavaScript\">
			            alert (\"Please fill in the $k feild\");
					    history.go(-1);
				     </script>";
            exit();		   
		   }
	   }   
	   
	   if(!checkEmail($arr['email'])) {
           echo '<script language="JavaScript">
			        alert ("Invalid Email");
					history.go(-1);
				 </script>';
           return;	   
	   }
	   
       $this->myarr = $arr;	 
       return true;	   
	}
	
    public function create() {
	    $output='';
	    $mailtext='';
		$home = "index.php?option=com_quantumfp&view=addclient";
	    $this->processForm();
		
	    $user['fullname'] = $this->myarr['fullname'];
		$user['email'] = $this->myarr['email'];
		$user['username'] = $this->myarr['username'];
		$password = $this->myarr['password'];
		
		$salt = JUserHelper::genRandomPassword(32);
        $crypt = JUserHelper::getCryptedPassword($password, $salt);
        $password = $crypt.':'.$salt;
		
		$instance = JUser::getInstance();		
		$config = JComponentHelper::getParams('com_users');
		$defaultUserGroup = $config->get('new_usertype', 2);
		$acl = JFactory::getACL();
		
		$instance->set('id', 0);
		$instance->set('name', $user['fullname']);
		$instance->set('username', $user['username']);
		$instance->set('password', $password);
		$instance->set('email', $user['email']);
		$instance->set('usertype', 'deprecated');
		$instance->set('groups', array($defaultUserGroup));
		

        if ($instance->save()) {      
	        $newUser =& JFactory::getUser($this->myarr['username']);
			$this->id = $newUser->get('id');
			$this->addInfo();
			$this->createFolder();
			
			$output .= "<h2>New User Created</h2>";
			$mailtext .= "QuantumFP Client Account";
			$output .= "-----------------------------------------";	
      $mailtext .= "\n---------------------------------------------------";		
			$output .= "<p><strong>Name:</strong> \t ".$user['fullname']."</p>";
			$mailtext .= "\n Name: \t ".$user['fullname'];
			$mailtext .= "\n Username: \t ".$user['username'];
			$output .= "<p><strong>Username:</strong> \t ".$user['username']."</p>";
			$mailtext .= "\n Password: \t ".$this->myarr['password'];
			$output .= "<p><strong>Password:</strong> \t ".$this->myarr['password']."</p>";
			$output .= "<br /> -----------------------------------------";
			$mailtext .= "\n---------------------------------------------------";
			$mailtext .= "\n\nPlease login to www.quantumfp.co.za/home/ and edit your details!";
			
			send_mail('no-reply@quantumfp.co.za', $user['email'], 'NEW QuantumFP Client Account', $mailtext);
			$output .= "<p><a href=\"$home\">Go Back</a></p>";
			
			
			
			return $output;
		}
        else {   
	        return JError::raiseWarning('SOME_ERROR_CODE', $instance->getError());	
        }			
    }
	
    private function addInfo() {
	  if (isset($this->id)) {
	    $title = $this->myarr['title'];
		$cell = $this->myarr['cell'];
		$tel = $this->myarr['tel'];
		$fax = $this->myarr['fax'];
		$fax = $this->myarr['fax'];
        $user_id = $this->id;
		if($this->myarr['subscribe'] == 'yes'){
		   $subscribe = 1;
		}
		else {
       $subscribe = 0;
    }
        $db =& JFactory::getDBO();
        $query = "INSERT INTO jos_quantum_users(user_id, title, cell, tel, fax, subscribe) 
		          VALUES('".$user_id."', '".$title."', '".$cell."', '".$tel."', '".$fax."', '".$subscribe."')";
        $db->setQuery($query);
		
		return $db->query();
      }		
	}
	
    private function createFolder() {
	  if (isset($this->id)) {
        $path = JPATH_SITE . DS . 'media' . DS . 'com_quantumfp' . DS . 'client_folders' . DS . 'user_' . $this->id;
 
		if (!JFolder::exists($path)) { 
		    if(!JFolder::create($path, 0777)) {
              echo '<script language="JavaScript">
			       alert ("Folder Already Exists, please delete old folders.");
			     </script>';
              return;
		       }
            else return true;	
        }
        else if(!JFolder::create($path . '_', 0777)){
           echo '<script language="JavaScript">
			       alert ("Folder Already Exists, please delete old folders.");
			     </script>';
           return;
        }
      }		
    }
}
/**
 * HTML View class for the HelloWorld Component
 */
class QuantumFpViewAddClient extends JView
{
        // Overwriting JView display method
        function display($tpl = null) 
        { 
		        $currentUser =& JFactory::getUser();
				$this->allowed = isAllowed(3, $currentUser->authorisedLevels());
				
	            if(!$this->allowed) {
	                header("Location: http://www.quantumfp.co.za/home");
					return;
	            }

                if(isset($_POST['import'])) {
				    $myClas = new quantumUser();
                    $a = $myClas->create();
					
                    echo $a;
					return true;
				}
				
                // Check for errors.
                if (count($errors = $this->get('Errors'))) 
                {
                        JError::raiseError(500, implode('<br />', $errors));
                        return false;
                }
                // Display the view
                parent::display($tpl);

        }
}