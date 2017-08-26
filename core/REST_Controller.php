<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require BASEPATH."/third_party/REST/Controller.php";

class REST_Controller extends API_Controller {

     protected $user, $permissions=array(), $group;

     const PERM_READ = 'read';
     const PERM_EDIT = 'edit';
     const PERM_DELETE = 'delete';


     public function __construct(){
         parent::__construct();
     }

}