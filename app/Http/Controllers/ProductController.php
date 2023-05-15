<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Validator;

class ProductController extends Controller
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
   
        public function index()
        {
            $products = Product::all();

            return $this->returnResponse(200, 'success', $products, 'found');

        }
        public function store(Request $request)
        {

    
            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'stock' => 'required|integer',

            ];
            $message = [];


            $validator = Validator::make($request->all(), $rules,$message);
            if ($validator->fails()) {
                return $this->returnValidationError('400', $validator);
            }
            $products = Product::create($request->all());
    
            return $this->returnResponse(200, 'success', $products, 'found');

        }
}
