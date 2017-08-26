<?php if ( ! defined('BASEPATH')) exit('No direct script core allowed');

class Config_Model extends CI_Model {
	var $table = 'config';
	function __construct(){
		parent::__construct();
		$this->load->database();
	}
	public function get_val($fieldname=""){
		$query = $this->db->where("field",$fieldname)->get($this->table);
		return $query->num_rows() > 0 ? $query->row()->value : NULL;
	}
	
	public function get_skills($group=""){
		$query = $this->db->where("group",$group)->order_by("order ASC")->get($this->skill_tbl);
		return $query->num_rows() > 0 ? $query->result() : NULL;
	}
	
	public function get_opensources(){
		$query = $this->db->get($this->opensource_tbl);
		return $query->num_rows() > 0 ? $query->result() : NULL;
	}

	public function get_opensource_types(){
		$query = $this->db->from($this->opensource_tbl)->select("type")->group_by("type")->get();
		return $query->num_rows() > 0 ? $query->result() : NULL;
	}

	public function get_languages(){
		$this->db->from($this->language_tbl);
		$this->db->where("status",1);
		$query = $this->db->get();
		return $query->num_rows() > 0 ? $query->result() : NULL;	
	}
	


}