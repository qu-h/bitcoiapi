<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ticker extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->methods['bitcoin_get']['limit'] = 20;
    }

    public function bitcoin_get(){
        $coin = $this->CoinMarketCap_Model->bitcoin_latest();
        $coin = array($coin);
        if( empty($coin) ){
            $this->response([
                'status' => FALSE,
                'message' => 'No Currency were found'
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->set_response($coin, REST_Controller::HTTP_OK);
        }
    }

    public function update_get(){
        $data = $this->CoinMarketCap_Model->bitcoin_latest();
        $rate_id = $this->CoinMarketCap_Model->update_bitcoin_rate(strtotime($data->last_updated));
        $data_latest = $this->CoinMarketCap_Model->currency_rate($rate_id);

        $this->response([
            'status' => TRUE,
            'message' => 'Last update is '.$data_latest->last_updated
        ], REST_Controller::HTTP_OK);
    }

    public function users_get()
    {

        $users = [
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'fact' => 'Loves coding'],
            ['id' => 2, 'name' => 'Jim', 'email' => 'jim@example.com', 'fact' => 'Developed on CodeIgniter'],
            ['id' => 3, 'name' => 'Jane', 'email' => 'jane@example.com', 'fact' => 'Lives in the USA', ['hobbies' => ['guitar', 'cycling']]],
        ];

        $id = $this->get('id');

        // If the id parameter doesn't exist return all the users

        if ($id === NULL)
        {
            // Check if the users data store contains users (in case the database result returns NULL)
            if ($users)
            {
                // Set the response and exit
                $this->response($users, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
            }
            else
            {
                // Set the response and exit
                $this->response([
                    'status' => FALSE,
                    'message' => 'No users were found'
                ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
            }
        }

        // Find and return a single record for a particular user.

        $id = (int) $id;

        // Validate the id.
        if ($id <= 0)
        {
            // Invalid id, set the response and exit.
            $this->response(NULL, REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
        }

        // Get the user from the array, using the id as key for retrieval.
        // Usually a model is to be used for this.

        $user = NULL;

        if (!empty($users))
        {
            foreach ($users as $key => $value)
            {
                if (isset($value['id']) && $value['id'] === $id)
                {
                    $user = $value;
                }
            }
        }

        if (!empty($user))
        {
            $this->set_response($user, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }
        else
        {
            $this->set_response([
                'status' => FALSE,
                'message' => 'User could not be found'
            ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }
    }
}