<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Product;
use App\Jobs\TransferBalance;
use App\Models\Transaction;

use Validator;

class ClientController extends Controller
{
        // error Msg
        public function returnError($errNum, $msg)
        {
            return response()->json([ 'status' => 400,'errNum' => $errNum,'msg' => $msg ]);
        }
    
        //Validation Error
        public function returnValidationError($code = "E001", $validator)
        {
            return $this->returnError($code, $validator->errors()->first());
        }
    
        // Api returnResponse
        private function returnResponse($code, $sub, $return, $message)
        {
            $response = array("status" => $code, "sub_message" => $sub, "return" => $return, "message" => $message);
            return response($response);
        }
        public function index(){

            $clients = Client::all();
            return $this->returnResponse(200, 'success', $clients, 'found');
    
        }

        public function store(Request $request)
        {

    
            $rules = [
                "name" => "required",
                'email' => 'required|email|unique:clients',
                'balance' => 'required|numeric',

            ];
            $message = [];


            $validator = Validator::make($request->all(), $rules,$message);
            if ($validator->fails()) {
                return $this->returnValidationError('400', $validator);
            }


            $client = Client::create($request->all());
    
            return $this->returnResponse(200, 'success', 'Client Created Successfully', 'found');

        }

        //Buying 
        public function buying(Request $request){

              
            $rules = [
                "client_id" => "required",
                'product_id' => 'required',
                'details' => 'required',

            ];
            $message = [];


            $validator = Validator::make($request->all(), $rules,$message);
            if ($validator->fails()) {
                return $this->returnValidationError('400', $validator);
            }  
            $data = [];
            $client = Client::where('id',$request->client_id)->first(); 
            if($client){
                $data['fromclientId'] = '';
                $data['toclientId'] = $request->client_id;            
            }else{
                return $this->returnResponse(200, 'success', 'This User Not Found ', 'found');
            }
             $product = Product::where('id',$request->product_id)->first();
             if($product){

                $data['amount'] = $product->price;
                if($client->balance >= $request->amount){
                    
                    $data['balance_before'] =$client->balance;
                    $data['balance_after'] =$client->balance - $product->price;
                    $client->balance =    $data['balance_after'];
                    $client->save(); 
                  }else{
                    return $this->returnResponse(200, 'success', 'Balance Not Valid ', 'found');

                  } 
              }else{
                return $this->returnResponse(200, 'success', 'Product Not Found', 'found');

              }   
              $data['details'] = $request->details;
              $data['product_id'] =$request->product_id;
             
              \dispatch(new TransferBalance($data['fromclientId'],$data['toclientId'],$data['product_id'],$data['amount'],$data['balance_before'],$data['balance_after'],$data['details']));
             
              return $this->returnResponse(200, 'success', "Transaction Created Successfully", 'found');

        }

        public function charging(Request $request){
            $rules = [
                "from" => "required",
                'to' => 'required',
                'amount' => 'required',
                'details' => 'required',

            ];
            $message = [];


            $validator = Validator::make($request->all(), $rules,$message);
            if ($validator->fails()) {
                return $this->returnValidationError('400', $validator);
            } 
            $data = [];
            $from = Client::where('id',$request->from)->first(); 
            if($from){
                $data['fromclientId'] = $from->id;
            }else{
                return $this->returnResponse(200, 'success', 'This User Not Found ', 'found');
            } 

            //To
            $to = Client::where('id',$request->to)->first(); 
            if($to){
                $data['toclientId'] = $to->id;
            }else{
                return $this->returnResponse(200, 'success', 'This Client Not Found ', 'found');
            } 

            $data['balance_before'] =$to->balance;
            $data['balance_after'] =$to->balance + $request->amount;
            $to->balance =    $data['balance_after'];
            $data['amount'] = $request->amount;
                
            $to->save(); 

            $data['details'] = $request->details;
            \dispatch(new TransferBalance($data['fromclientId'],$data['toclientId'],$request->product_id,$request->amount,$data['balance_before'],$data['balance_after'],$data['details']));
             
            return $this->returnResponse(200, 'success', "Transaction Created Successfully", 'found');


        }

        public function transfer(Request $request){
            $rules = [
                "from" => "required",
                'to' => 'required',
                'amount' => 'required',
                'details' => 'required',

            ];
            $message = [];


            $validator = Validator::make($request->all(), $rules,$message);
            if ($validator->fails()) {
                return $this->returnValidationError('400', $validator);
            } 
            $data = [];
            $from = Client::where('id',$request->from)->first(); 
            if($from){
                $data['fromclientId'] = $from->id;
            }else{
                return $this->returnResponse(200, 'success', 'This User Not Found ', 'found');
            } 

            //To
            $to = Client::where('id',$request->to)->first(); 
            if($to){
                $data['toclientId'] = $to->id;
            }else{
                return $this->returnResponse(200, 'success', 'This Client Not Found ', 'found');
            } 

            $data['balance_before'] =$to->balance;
            $data['balance_after'] =$to->balance + $request->amount;
            $to->balance =    $data['balance_after'];
            $data['amount'] = $request->amount;
                
            $to->save(); 

            $data['details'] = $request->details;
            \dispatch(new TransferBalance($data['fromclientId'],$data['toclientId'],$request->product_id,$request->amount,$data['balance_before'],$data['balance_after'],$data['details']));
             
            return $this->returnResponse(200, 'success', "Transaction Created Successfully", 'found');


        }

        public function reports(){

           $transactions = Transaction::get();
           $data=[];
           foreach($transactions as $key =>$trans){

            $client_name = Client::where('id',$trans->to_client_id)->first();
            $user = Client::where('id',$trans->from_client_id)->first();
            $user_name = '';
            if($user){
                $user_name = $user->name;

            }
            $data[$key]['Datetime'] = date("y-m-d H:i s",strtotime($trans->created_at));
            $data[$key]['client name'] = $client_name->name;
            $data[$key]['Balance Before'] = $trans->balance_before;
            $data[$key]['Balance After'] = $trans->balance_after;
            $data[$key]['Details'] = $trans->details;
            $data[$key]['User'] = $user_name;


           }
           return $this->returnResponse(200, 'success', $data, 'found');
        }
}
