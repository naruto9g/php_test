<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function index()
	{
		$this->load->view('index');
	}

	public function calculate_get()
	{
		$this->load->database();

		$data = [];
		$data["result"] = "success";
		$data["message"] = "";
		$sql = "SELECT * from public.calculate_record";
		$result = $this->db->query($sql)->result_array();
		$data['data'] = $result;

		echo json_encode($data);
	}

	public function calculate_post()
	{
		$this->load->database();

		$inputJSON = file_get_contents('php://input');
		$input = json_decode($inputJSON, TRUE);


		$currency = $input['currency'];
		$price = $input['price'];
		$discount = $input['discount'];

		$data = [];


		$curl = curl_init(); 

		curl_setopt_array($curl, array(   
			CURLOPT_URL => "https://tw.rter.info/capi.php",   
			CURLOPT_RETURNTRANSFER => true,   
			CURLOPT_ENCODING => "",   
			CURLOPT_MAXREDIRS => 10,   
			CURLOPT_TIMEOUT => 30,   
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,   
			CURLOPT_CUSTOMREQUEST => "GET",   
		)); 
		 
		$response = curl_exec($curl); 
		$err = curl_error($curl); 
		 
		curl_close($curl);

		$response = json_decode($response, true);
		 
		if ($err) 
		{   
		 	echo "cURL Error #:" . $err; 
		} 
		else 
		{
			if($currency == "USD")
			{
				$data['currency'] = $currency;
				$data['rate'] = $response['USDTWD']['Exrate'];
				$data['price'] = $price;
				$data['discount'] = $discount;
				$data['result'] = round($price*$response['USDTWD']['Exrate'] ,2);
			}
			else
			{
				$data['currency'] = $currency;
				$data['rate'] = $response['USDTWD']['Exrate']/$response['USD'.$currency]['Exrate'];
				$data['price'] = $price;
				$data['discount'] = $discount;

				//轉換成對台幣匯率
				$rate = $response['USDTWD']['Exrate']/$response['USD'.$currency]['Exrate'];

				if($currency == "JPY")
				{
					$data['result'] = round($price*$rate,2);
				}
				else
				{
					$data['result'] = round($price*$rate*((100-$discount)/100),2);
				}
			}   
			
			$this->db->insert('public.calculate_record',$data);

			echo json_encode($data);
		}
	}
}
