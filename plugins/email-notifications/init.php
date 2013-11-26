<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Email Notification Refreak Plugin
 *
 * @package	Refreak
 * @subpackage	plugin
 * @category	plugin
 * @author	Víctor <victor@ebavs.net> fromcouch
 * @link	https://github.com/fromcouch/refreak
 */
class Email_Notification extends RF_Plugin {
    
    /**
     * Constructor
     * 
     * @return void
     */
    public function __construct() {
        
        parent::__construct();
       
    }
    
    public function edit() {
	    
		$this->css_add(base_url() . 'plugins' . DIRECTORY_SEPARATOR . $this->directory . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'edit.css');
		$this->lang_load('email-notifications');
		
    }
        
}