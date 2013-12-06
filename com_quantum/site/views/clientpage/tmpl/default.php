<?php
defined('_JEXEC') or die('Restricted access');
$document = &JFactory::getDocument();
$document->addStyleSheet('components/com_quantumfp/files/style.css'); 
echo $this->loadTemplate('head');
echo $this->loadTemplate('body');
echo $this->loadTemplate('foot');
