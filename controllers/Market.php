<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Market extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        
    }

    public function bigsale_get(){
        $this->load->library('Simple_html_dom');
        $img_path = "F:/PHP/xampp/htdocs/myshopify-template/bigsale-01/";
        $file = "pages/product-item.html";
        $html = file_get_html($img_path.$file);

        $source = "http://cdn.shopify.com/s/files/1/1498/2346/";
        foreach($html->find("img") as $img) {
            $src = $img->attr['src'];
            $src = $this->before ('?', $src);
            $src = $this->after ('../', $src);

            if( !file_exists($img_path.$src) ){
                
                copy($source.$src,$img_path.$src);
                
                //bug($img_path.$src);die;
            }
            
        }


        $link_remove = [
            '/collections/fashion',
            '/collections/all/fashion',
            '/collections/all/computer',
            '/collections/all/mobile',
            '/collections/all/smart',
            '/collections/all/furniture',
            '/collections/all/homeware',
            '/collections/all/sport',
        ];
        foreach($html->find("a") as $a) {
            if( in_array($a->attr['href'],$link_remove)){
                $a->attr['href'] = "#";
            }
        }
        $html->save($img_path.$file);
        die("end check");
    }

    function before ($str, $inthat)
    {
        return substr($inthat, 0, strpos($inthat, $str));
    }
    function after_last ($str, $inthat)
    {
        if ( !is_bool(strrevpos($inthat, $str)) ){
            return substr($inthat, strrevpos($inthat, $str)+strlen($str));    
        }
        
    }
    function after ($str, $inthat)
    {
        if (!is_bool(strpos($inthat, $str))){
        return substr($inthat, strpos($inthat,$str)+strlen($str));    
        }
        
    }

    public function update_get()
    {
        $this->load->library('Simple_html_dom');

        $url = "https://coinmarketcap.com/currencies/bitcoin/#markets";
        $html = file_get_html($url);
        $this->CoinMarketCap_Model->bitcoin_market_reset_order();
        foreach($html->find("table",0)->find('tr') as $line_num => $row) {
            if( !$row->find('td',0) ){
                continue;
            }

            $rowData = [ "order"=>$line_num, "currency"=>"bitcoin" ];

            foreach($row->find('td') as $col_index => $cell) {
               
                switch ($col_index){
                    case 1:
                        $rowData["source"] = $cell->plaintext; break;
                    case 2:
                        $rowData["pair"] = $cell->plaintext;
                        $rowData["pair_link"] = $cell->find("a",0)->attr['href'];
                        break;
                    case 3:
                        $rowData["volume_24_usd"] = $cell->find("span",0)->attr['data-usd'];
                        $rowData["volume_24_btc"] = $cell->find("span",0)->attr['data-btc'];
                        $rowData["volume_24_native"] = $cell->find("span",0)->attr['data-native'];
                        break;
                    case 4:
                        $rowData["price_usd"] = $cell->find("span",0)->attr['data-usd'];
                        $rowData["price_btc"] = $cell->find("span",0)->attr['data-btc'];
                        $rowData["price_native"] = $cell->find("span",0)->attr['data-native'];
                        break;
                    case 5:
                        $rowData["volume_change"] = $cell->plaintext;
                        break;
                    case 6:
                        $rowData["update_status"] = $cell->plaintext;
                        break;
                }
            }
            
            if( isset($rowData["update_status"]) && strlen($rowData["update_status"]) > 0){
                $this->CoinMarketCap_Model->bitcoin_market_update($rowData);    
            }
            
        }

        $this->response([
            'status' => TRUE,
            'message' => "Update Markets Success"
        ], REST_Controller::HTTP_OK);
    }

    public function bitcoin_get(){
        $page = $this->input->get("page");
        $limit = intval($this->input->get("limit"));

        if( $limit < 1 ){

            $limit = 20;
        }
        $coin = $this->CoinMarketCap_Model->market_items("bitcoin",$page, $limit);
        if( empty($coin) ){
            $this->response([
                'status' => FALSE,
                'message' => 'No Currency were found'
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->set_response($coin, REST_Controller::HTTP_OK);
        }
    }
}