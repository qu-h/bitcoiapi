<?php if ( ! defined('BASEPATH')) exit('No direct script core allowed');

class CoinMarketCap_Model extends CI_Model
{
    var $currency_rate_tbl = 'currencies_rate';
    var $currency_tbl = 'currency';
    var $market_tbl = 'market';
    var $history_tbl = "history";
    var $page_limit = 30;
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function bitcoin_latest(){
        $rate = $this->currency_rate();

        if( empty($rate) ){
            $latest_id = $this->update_bitcoin_rate();
            $rate = $this->currency_rate($latest_id);
        }
        return $rate;
    }

    public function update_bitcoin_rate($latest_datetime=0){
        ini_set("max_execution_time", 0);
        $data = json_decode(file_get_contents('https://api.coinmarketcap.com/v1/ticker/bitcoin/'));
        $bitcoin = $data[0];
        $currency = array(
            'code'=>$bitcoin->id,
            'name'=>$bitcoin->name,
            'symbol'=>$bitcoin->symbol,
            'rank'=>$bitcoin->rank
        );
        $currency_row = $this->currency_update($currency);

        if( $latest_datetime > 0 && $bitcoin->last_updated > $latest_datetime ){
            $currency_rate = (array)$bitcoin;
            unset($currency_rate["id"]);
            unset($currency_rate["rank"]);
            unset($currency_rate["name"]);
            unset($currency_rate["symbol"]);
            unset($currency_rate["price_usd"]);
            unset($currency_rate["price_btc"]);

            $currency_rate["last_updated"] = date( 'Y-m-d H:i:s', $bitcoin->last_updated );
            $currency_rate["currency"] = $currency_row->code;
            $currency_rate["usd_rate"] = $bitcoin->price_usd;
            $currency_rate["btc_rate"] = $bitcoin->price_btc;
            $currency_rate["created"] = date( 'Y-m-d H:i:s');

            $this->db->insert($this->currency_rate_tbl,$currency_rate);

            return $this->db->insert_id();
        }
        return 0;

    }

    public function currency_update($data){
        $query = $this->db->where("code",$data["code"])->get($this->currency_tbl);
        if( $query->num_rows() < 1 ){
            $this->db->insert($this->currency_tbl,$data);

            $row = $this->db->where("id",$this->db->insert_id())->get($this->currency_tbl)->row();
        } else {
            $row = $query->row();
        }
        return $row;
    }

    public function currency_rate($id=0, $limit = 1)
    {
        $this->db->from($this->currency_rate_tbl." AS r")->select("r.*, r.usd_rate AS price_usd, r.btc_rate AS price_btc");
        $this->db->join($this->currency_tbl." AS c",'c.code = r.currency','LEFT')->select("c.name, c.symbol, c.max_supply");
        $this->db->order_by("r.id DESC");
        if( $id > 0 ){
            $this->db->where("r.id",$id);
        }
        if( $limit < 2 ){
            return $this->db->get()->row();
        }
        return $this->db->limit($limit)->get()->result();
    }

    public function bitcoin_market_reset_order(){
        $this->db->update($this->market_tbl, ['order'=>0] );
        return true;
    }

    public function bitcoin_market_update($data)
    {
        if( !is_array($data) || empty($data) || !array_key_exists("source",$data) || !array_key_exists("pair",$data) ){
            return FALSE;
        }

        $query = $this->db->where("source",$data["source"])
                      ->where("pair",$data["pair"])
            ->get($this->market_tbl);

        if( $query->num_rows() < 1 ){
            $this->db->insert($this->market_tbl,$data);

            return $this->db->insert_id();
        } else {
            $old_data = $query->row();
            $data["modified"] = date( 'Y-m-d H:i:s');
            $this->db->where("source",$data["source"])
                ->where("pair",$data["pair"])
                ->update($this->market_tbl,$data);
            return $old_data->id;
        }
    }

    public function market_items($currency_code="bitcoin",$page=1,$limit = 30){
        
        if( $page < 1 ){
            $page = 1;
        }
        $this->db->where("currency",$currency_code);
        $this->db->where("order >",0);

        $this->db->limit($limit, $limit*($page-1));

        $this->db->select("*")
                ->select("ROUND(price_usd, 2) AS price")
        ;
        

        $data = $this->db->order_by("order ASC")->get($this->market_tbl);

        return $data->result();;
    }

    public function history_update($data){
        if( !is_array($data) || empty($data) || !array_key_exists("currency",$data) || !array_key_exists("date",$data) ){
            return FALSE;
        }
        $where = array(
            "currency"=>$data["currency"],
            "date"=>$data["date"]
        );
        $query = $this->db->where($where)->get($this->history_tbl);

        if( $query->num_rows() < 1 ){
            $this->db->insert($this->history_tbl,$data);
            return $this->db->insert_id();
        } else {
            $old_data = $query->row();
            if( $old_data->date != $data["date"] ){
                $data["modified"] = date( 'Y-m-d H:i:s');
                $this->db->where($where)->update($this->history_tbl,$data);
            }
            return $old_data->id;
        }
    }

    public function history_items($currency_code="bitcoin"){
        $this->db->where("currency",$currency_code);

        $this->db->limit($this->page_limit);
        return $this->db->get($this->history_tbl)->result();
    }
}