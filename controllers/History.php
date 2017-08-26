<?php defined('BASEPATH') OR exit('No direct script access allowed');

class History extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->methods['bitcoin_get']['limit'] = 20;
    }

    public function update_get()
    {
        $this->load->library('Simple_html_dom');

        $url = "https://coinmarketcap.com/currencies/bitcoin/historical-data/?start=20130101&end=".date("Ymd");
        $url = "https://coinmarketcap.com/currencies/bitcoin/historical-data";

        $html = file_get_html($url);
        foreach($html->find("table",0)->find('tr') as $row) {
            if (!$row->find('td', 0)) {
                continue;
            }

            $rowData = array();
            foreach ($row->find('td') as $col_index => $cell) {
                $cell->plaintext = str_replace(",","",$cell->plaintext);
                switch ($col_index) {
                    case 0:
                        $rowData["date"] = date("Y-m-d",strtotime($cell->plaintext)); break;
                    case 1:
                        $rowData["open"] = floatval($cell->plaintext); break;
                    case 2:
                        $rowData["high"] = floatval($cell->plaintext); break;
                    case 3:
                        $rowData["low"] = floatval($cell->plaintext); break;
                    case 4:
                        $rowData["close"] = floatval($cell->plaintext); break;
                    case 5:
                        $rowData["volume"] = floatval($cell->plaintext); break;
                    case 6:
                        $rowData["market_cap"] = floatval($cell->plaintext); break;
                    default:
                        $rowData[] = $cell->outertext;
                }
            }
            $rowData["currency"] = "bitcoin";
            $this->CoinMarketCap_Model->history_update($rowData);
        }

        $this->response([
            'status' => TRUE,
            'message' => "Update Markets Success"
        ], REST_Controller::HTTP_OK);
    }

    public function bitcoin_get($page=1){
        $items = $this->CoinMarketCap_Model->history_items("bitcoin");
        if( empty($items) ){
            $this->response([
                'status' => FALSE,
                'message' => 'No Currency were found'
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->set_response($items, REST_Controller::HTTP_OK);
        }
    }
}