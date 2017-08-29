<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Market extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        
    }

    public function update_get()
    {
        $this->load->library('Simple_html_dom');

        $url = "https://coinmarketcap.com/currencies/bitcoin/historical-data/";
        $html = file_get_html($url);

        foreach($html->find("table",0)->find('tr') as $row) {
            if( !$row->find('td',0) ){
                continue;
            }

            $rowData = array();
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
            $rowData["currency"] = "bitcoin";
            $this->CoinMarketCap_Model->bitcoin_market_update($rowData);
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