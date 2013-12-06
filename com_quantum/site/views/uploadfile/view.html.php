<?php

defined('_JEXEC') or die('Restricted access');

 

// import Joomla view library

jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');
jimport('joomla.user.helper');
jimport( 'joomla.environment.request' );

function isAllowed($chech_perms, $user_perms) {

    $is_allowed = false;

    if (is_int($chech_perms)) {

        foreach($user_perms as $v) {

	        if($v == $chech_perms) $is_allowed = true;

        }	

	}

	return $is_allowed;

}

function sendMail($clientemail, $subject, $doc, $client) {
$to = $clientemail;

$time = date('Y-m-d H:i:s',time());

$message="
<html>
<head>
  <title></title>
</head>
<body>
<h1 style=\"color: #4B3F34\">Quantum Financial Planning</h1>
<p style=\"color: green\">File Uploaded Successfully!</p>
<table width=\"400\">
   <tbody>
     <tr>
	   <td width=\"120\">
	       <strong>Client:</strong>
	   </td>
	   <td>
	      {$client}
	   </td>	   
	 </tr>
     <tr>
	   <td width=\"120\">
	       <strong>Document:</strong>
	   </td>
	   <td>
	       {$doc}
	   </td>
	 </tr>
     <tr>
	   <td width=\"120\">
	       <strong>Time:</strong>
	   </td>
	   <td>
	       {$time}
	   </td>
	 </tr>
  </tbody>
</table> 
<p>Please login to www.quantumfp.co.za/home/ to retrieve.</p>
<p><i>JM Shelf 2 cc T/A Quantum Financial Planning<br />
CK 2000/064134/23<br />
Member N.H CAIRNS, C.IVINS <br />
An Authorised Financial Services Provider<br />
FSP 7596<br />
</i></p>
</body>
</html>
";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= "From: QuantumFP <no-reply@quantumfp.co.za>\r\n";
$headers .= "Bcc: reports@quantumfp.co.za\r\n";
$headers .= "X-Priority: 1(Highest)\r\n";
$headers .= "X-MSMail-Priority: High\r\n";
$headers .= "Importance: High\r\n";

mail($to, $subject, $message, $headers);
}

class quantumFiles {
  private $client, $filename, $filetype;
	
  public function upload($userName, $email) {
    $home = "index.php?option=com_quantumfp&view=uploadfile"; 
    $doctype = array("doc"=>"Document", "tracker"=>"Investment Tracker", "corresp"=>"Correspondence");
    $file = JRequest::getVar('file_upload', null, 'files', 'array');
		$client = JRequest::getInt('client', null, 'POST');
		$filetype = JRequest::getWord('filetype', '', 'POST');
		
		jimport('joomla.filesystem.file');
		
		$user =& JFactory::getUser($client);
		$userN = $user->get('name');
		$userem = $user->get('email');
		
		$filename = JFile::makeSafe($file['name']);
		$src = $file['tmp_name'];
		
		$this->client = $client;
		$this->filename = $filename;
		$this->filetype = $filetype;

    $clientfolder = JPATH_SITE . DS . 'media' . DS . 'com_quantumfp' . DS . 'client_folders' . DS . 'user_' . $client;
    
    if(!JFolder::exists($clientfolder)){
		    if(!JFolder::create($clientfolder, 0777)) {
              echo '<script language="JavaScript">
			       alert ("Client folder does not exist and could not be created, please contact developers.");
			       history.go(-1);
			     </script>';
              return;
		    }
        else return true;    
    }	
    	
		if ( strtolower(JFile::getExt($filename) ) == 'pdf') {
		
		    if($filetype == 'tracker') {
		        $dest = $clientfolder . DS . 'tracker.pdf';
		      if (JFile::exists("media/com_quantumfp/client_folders/user_{$client}/tracker.pdf")) {
              JFile::delete("media/com_quantumfp/client_folders/user_{$client}/tracker.pdf");  
          }
		    }
		    else {
            $dest = $clientfolder . DS . $filename;
        }
        $filetype = $doctype[$filetype];
		    if ( JFile::upload($src, $dest) ) {
			
		      if ($this->databaseRegister()) {
			
			      $output .= "<h2 style=\"color: green\">File Uploaded Successfully!</h2>";
			      $output .= "-----------------------------------------";			
			      $output .= "<p><strong>Client:</strong> \t " . $userN . "</p>";
			      $output .= "<p><strong>File Type</strong> \t " . $filetype . "</p>";
			      $output .= "<br /> -----------------------------------------";
			      
			      sendMail($userem, "QFP CONFIDENTIAL UPLOAD: ". $userN . "'s  " . $filetype, $filetype, $userN);
			      
			      $output .= "<p><a href=\"$home\">Go Back</a></p>";
			   }
			   
		     else {
			
			      $output .= "<h2 style=\"color: red\">File uploaded not registered in database</h2>";
			      $output .= "-----------------------------------------";			
			      $output .= "<p><strong>Client:</strong> \t " . $userName . "</p>";
			      $output .= "<p><strong>File Type:</strong> \t " . $filetype . "</p>";
			      $output .= "<br /> -----------------------------------------";
			
			      $output .= "<p><a href=\"$home\">Go Back</a></p>";
			   }
			
			    return $output;
				
			} else {
			    $output .= "<h2 style=\"color: red\">File Upload Failure!</h2>";
			    $output .= "-----------------------------------------";			
			    $output .= "<p><strong>Client:</strong> \t " . $userName . "</p>";
			    $output .= "<p><strong>File Type:</strong> \t " . $filetype . "</p>";
			    $output .= "<br /> -----------------------------------------";
			
			    $output .= "<p><a href=\"$home\">Go Back</a></p>";         
            }
		}
  }	
	
	private function databaseRegister() {
		$user_id = $this->client;
		$name = $this->filename;
		$file_type = $this->filetype;
		$file_url = "http://www.quantumfp.co.za/home/media/com_quantumfp/client_folders/user_" . $user_id ."/" . $name;
		
    $db =& JFactory::getDBO();
    if ($file_type == 'tracker') {
        return true;
    }
    else {
        $query = "INSERT INTO #__quantum_files(`user_id`, `name`, `file_url`, `file_type`) VALUES('".$user_id."','".$name."','".$file_url."','".$file_type."')";
    }
    $db->setQuery($query);
		
		return $db->query();    
  }
}

/**

 * HTML View class for the HelloWorld Component

 */

class QuantumFpViewUploadFile extends JView {

    // Overwriting JView display method
    function display($tpl = null)  {

		    $currentUser =& JFactory::getUser();
				$this->allowed = isAllowed(3, $currentUser->authorisedLevels());

			  if(!$this->allowed) {
	         header("Location: http://www.quantumfp.co.za/home");
           return;
        }
        
			  if(isset($_POST['import'])) {
			     
	         $quantumFiles = new quantumFiles();   
	         $result = $quantumFiles->upload($currentUser->get('name'), $currentUser->get('email'));
	         
	         echo $result;
           return;
        }

        $this->users = $this->get('Users');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }
        // Display the view
        parent::display($tpl);
     }
}

