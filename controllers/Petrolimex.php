<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Petrolimex extends MX_Controller
{
    function __construct()
    {
        $this->load->module('Layouts');
        $this->template->set_theme('mnabi-portfolio')->set_layout('mnabi');
    }

    public function index(){
    	die("show index");
    }



	static $uri = "https://static.petrolimex.com.vn/Rpc.ashx/vi-VN/6783DC1271FF449E95B74A9520964169/1416751D90D244979F57B5850C40BE0B/FC001E95204947538C944D3E8195D2DD/GetContents/397BDFDD248C47E6845454E2E835DC06/8BB57662940F43D092D15C394CD927EA/Json.rpc?pageNumber=%u";
        
    public function get($page=1){
    	
        $url = sprintf(self::$uri,$page);
        bug("get data page = $page");
        $json = file_get_contents($url);
        $obj = json_decode($json);

        $records = 0;
        if( is_object($obj) && !empty($obj->Objects) ){
        	
        	$page_size = $obj->Info->Pagination->Size;
        	$records = $obj->Info->Pagination->TotalRecords;
        	$page_count = $obj->Info->Pagination->TotalPages;
        	$page_current = $obj->Info->Pagination->Page;

	        foreach ($obj->Objects as $shop) {
	            $this->petrolimex_shop_update($shop);
	        }
	        if( $page_current < $page_count ){
	        	//$this->get($page_current+1);
	        }
        }
        die("finish records = $records");
        
    }
    private function petrolimex_shop_update($shop){
        

        $check = $this->db->from("petrolimex_shop")
                ->where("petrolimex_id",$shop->Id)
                //->where("updated <",$shop->LastUpdated)
                ->get();

        if( $check->num_rows() == 0 ){
            $fields_get = array(
            "Id"=>"petrolimex_id",
            "ShortName"=>"url_name",
            "Title"=>"title",
            "OwnedBy"=>"name",
            "Address"=>"address",
            "County"=>"county",
            "District"=>"district",
            "Province"=>"province",
            "Phone"=>"phone",
            "Email"=>"email",
            "GPSLocation"=>"location",

            "LeaderName"=>"leader_name",
            "LeaderMobile"=>"leader_mobile",
            "WorkingTimes"=>"working_times",
            "Goods"=>"goods",
            "OilDispenser"=>"oil_dispenser",
            "Services"=>"services",
            "PumpsForAutos"=>"pumps_for_autos",
            "StorageCapacity"=>"storage_capacity",
            "SelftService"=>"selft_service",
            "CardAccepts"=>"card_accepts",
            
            'Created'=>"created",
            "LastUpdated"=>"updated"
            );
            $data = array();
            foreach ($fields_get as $key => $field) {
                if( isset($shop->$key)) {
                    $data[$field] = $shop->$key;
                }
            }
           
            $data["created"] = date("Y-m-d H:i:s ", strtotime($data["created"]));
            $data["updated"] = date("Y-m-d H:i:s ", strtotime($data["updated"]));
            $this->db->insert("petrolimex_shop",$data);
            bug($this->db->insert_id());
        }

    }
}