<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\{AccountDetail,WithdrawHistory,User};
// use App\Models\Project_maintenance;

class WidthdrawlController extends Controller
    
     {
  public function successbycamlenio(Request $request, $id)
{
    // Validate PIN input
    $pin = 2020;
    $inputPin = $request->input('pin');

    if ($inputPin != $pin) {
        return redirect()->route('widthdrawl', '1')
            ->withInput()
            ->withErrors(['pin' => 'Invalid pin. Please try again.']);
    }

    // Check session for user authentication
    $value = $request->session()->has('id');
    if (empty($value)) {
        return redirect()->route('login');
    }

    // Fetch withdrawal details from the database
    $data = DB::select("SELECT account_details.*, users.email AS email, users.mobile AS mobile, withdraw_histories.amount AS amount, admin_settings.longtext AS mid, 
                        (SELECT admin_settings.longtext FROM admin_settings WHERE id = 14 ) AS orderid 
                        FROM account_details 
                        LEFT JOIN users ON account_details.user_id = users.id 
                        LEFT JOIN withdraw_histories ON withdraw_histories.user_id = users.id AND withdraw_histories.account_id = account_details.id 
                        WHERE withdraw_histories.id = ?", [$id]);

    if (empty($data)) {
        return redirect()->route('widthdrawl', '1')->with('error', 'No withdrawal data found for the specified ID.');
    }

    // Extract the necessary details for payout
    $object = $data[0];
    $name = $object->name;
    $ac_no = $object->account_number;
    $ifsc = $object->ifsc_code;
    $bankname = $object->bank_name;
    $email = $object->email;
    $mobile = $object->mobile;
    $amount = $object->amount;
    $mid = $object->mid;
    $token = $object->token;
    $orderid = $object->orderid;

    // Generate the HMAC signature
    $timestamp = time();
    $path = "/api/v1/payout/payoutprocess";
    $query_string = "";  // If there are any query parameters, include them here
    $body = json_encode([
        "address" => $ac_no,
        "payment_type" => 3,  // 3 is for IMPS, adjust according to your requirement
        "amount" => $amount,
        "email" => $email,
        "name" => $name,
        "mobile_number" => $mobile,
        "account_number" => $ac_no,
        "ifsc_code" => $ifsc,
        "merchant_order_id" => $orderid
    ]);

    $signature = $this->generate_hmac($token, $timestamp, $body, $path, $query_string);

    // Prepare the cURL request data
    $headers = [
        "Content-Type: application/json",
        "User-Agent: team testing",
        "ApiKey: <Your_ApiKey>",
        "signature: $signature",
        "SecretKey: <Your_SecretKey>",
        "UserId: <Your_UserID>"
    ];

   
    $jsonData = [
        "address" => $ac_no,
        "payment_type" => 3, // Change payment type as per your requirement (NEFT, UPI, etc.)
        "amount" => $amount,
        "email" => $email,
        "name" => $name,
        "mobile_number" => $mobile,
        "account_number" => $ac_no,
        "ifsc_code" => $ifsc,
        "merchant_order_id" => $orderid
    ];

    // Initialize cURL session
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://partner.camlenio.com/api/v1/payout/payoutprocess',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($jsonData),
        CURLOPT_HTTPHEADER => $headers,
    ]);

    // Execute cURL request and capture the response
    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        return response()->json([
            'status' => 500,
            'message' => 'cURL Error: ' . curl_error($curl)
        ]);
    }

    // Close the cURL session
    curl_close($curl);

    // Decode the response from the API
    $data = json_decode($response, true);

    // Check for success
    if ($data['status'] == 'success') {
        // Update the withdrawal history status in the database
        DB::select("UPDATE `withdraw_histories` SET `status` = '2', `response` = ? WHERE id = ?", [$response, $id]);

        // Redirect to the withdrawal page with success message
        return redirect()->route('widthdrawl', '1')->with('message', 'Payout successful!');
    } else {
        // Handle errors if payout fails
        return redirect()->route('widthdrawl', '1')->with('error', $data['message']);
    }
}

// // Function to generate the HMAC signature
// public function generate_hmac($secret, $timestamp, $body, $path, $query_string = '', $method = 'POST')
// {
//     // Create the message to be signed
//     $message = $method . "\n" . $path . "\n" . $query_string . "\n" . $timestamp . "\n" . $body;
    
//     // Generate the HMAC SHA-512 signature
//     return hash_hmac('sha512', $message, $secret);
// }

    public function widthdrawl_index($id)
    {
		
        // Fetch all records from the Project_maintenance model
        $widthdrawls = DB::select("SELECT withdraw_histories.*, users.username AS uname, users.mobile AS mobile, account_details.account_number AS acno, account_details.upi_id AS upi_id, account_details.bank_name AS bname, account_details.ifsc_code AS ifsc FROM withdraw_histories LEFT JOIN users ON withdraw_histories.user_id = users.id LEFT JOIN account_details ON account_details.id = withdraw_histories.account_id WHERE withdraw_histories.`status`=$id AND users.id IS NOT NULL order by withdraw_histories.id desc ;");

        // Pass the data to the view and load the 'project_maintenance.index' Blade file
        return view('widthdrawl.index', compact('widthdrawls'))->with($id,'id');
	 
			
    }
    
    public function success_by_upi(Request $request, $id)
{
     //dd($request);
    $value = $request->session()->has('id');
   
    $pin = 2020;
    $inputPin = $request->input('pin');
    
    if ($inputPin == $pin) {
        if (!empty($value)) {
            $data = DB::select("SELECT account_details.*, users.email AS email, users.mobile AS mobile, withdraw_histories.amount AS amount, admin_settings.longtext AS mid, 
                                (SELECT admin_settings.longtext FROM admin_settings WHERE id = 13) AS token, 
                                (SELECT admin_settings.longtext FROM admin_settings WHERE id = 14 ) AS orderid 
                                FROM account_details 
                                LEFT JOIN users ON account_details.user_id = users.id 
                                LEFT JOIN withdraw_histories ON withdraw_histories.user_id = users.id AND withdraw_histories.account_id = account_details.id 
                                LEFT JOIN admin_settings ON admin_settings.id = 12 
                                WHERE withdraw_histories.id = ?", [$id]);
   
            if (empty($data)) {
                return redirect()->route('widthdrawl', '1')->with('error', 'No withdrawal data found for the specified ID.');
            }
   
            $object = $data[0];  
            $upiid = $object->upi_id;
            $amount = $object->amount;
            $mid = $object->mid;
            $token = $object->token;

            $rand = rand(11111111111111, 99999999999999);
            $randid = "$rand";

            $curl = curl_init();
dd($curl);
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://indianpay.co.in/admin/PayViaUpi?upiid=$upiid&amount=$amount&merchantId=$mid&token=$token&orderid=$randid",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);
           //dd($response);

            if (curl_errno($curl)) {
                return redirect()->back()->with('error', 'CURL Error: ' . curl_error($curl));
            }

            curl_close($curl);

            // Check if response is not empty and is valid JSON
            if (empty($response)) {
                return redirect()->back()->with('error', 'Empty response from the server');
            }

            $datta = json_decode($response);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->with('error', 'Invalid JSON response');
            }

            // Ensure that $datta is not null before accessing properties
            if (is_object($datta) && isset($datta->status)) {
                $status = $datta->status;
                $error = $datta->error;

                if ($status == 400) {
                    return redirect()->back()->with('error', $error);
                }

                DB::update("UPDATE `withdraw_histories` SET `status` = ?, `response` = ?, `remark` = 'by upi' WHERE id = ?", [2, $response, $id]);
                return redirect()->route('widthdrawl', '1')->with('key', 'value');
            } else {
                return redirect()->back()->with('error', 'Unexpected response structure');
            }

        } else {
            return redirect()->route('login');
        }
    } else {
        return redirect()->route('widthdrawl', '1')
            ->withInput()  
            ->withErrors(['pin' => 'Invalid pin. Please try again.']);
    }
}


    
//   public function success_by_upi(Request $request, $id)
// {
//     $value = $request->session()->has('id');
    
//     $pin = 2020;

//     $inputPin = $request->input('pin');
//     if ($inputPin == $pin) {
//     if (!empty($value)) {
       
//         $data = DB::select("SELECT account_details.*, users.email AS email, users.mobile AS mobile, withdraw_histories.amount AS amount, admin_settings.longtext AS mid, 
//                             (SELECT admin_settings.longtext FROM admin_settings WHERE id = 13) AS token, 
//                             (SELECT admin_settings.longtext FROM admin_settings WHERE id = 14 ) AS orderid 
//                             FROM account_details 
//                             LEFT JOIN users ON account_details.user_id = users.id 
//                             LEFT JOIN withdraw_histories ON withdraw_histories.user_id = users.id AND withdraw_histories.account_id = account_details.id 
//                             LEFT JOIN admin_settings ON admin_settings.id = 12 
//                             WHERE withdraw_histories.id = ?", [$id]);
   
//         if (empty($data)) {
           
//             return redirect()->route('widthdrawl', '1')->with('error', 'No withdrawal data found for the specified ID.');
//         }
       
//         $object = $data[0];  
//         $upiid = $object->upi_id;
        
//         $amount = $object->amount;
//         $mid = $object->mid;
//         $token = $object->token;
//         //$orderid = $object->orderid;

//         $rand = rand(11111111111111, 99999999999999);
//         $randid = "$rand";
// //dd($upiid,$amount,$mid,$token,$randid);
//         $curl = curl_init();

//         curl_setopt_array($curl, array(
//           CURLOPT_URL => "https://indianpay.co.in/admin/PayViaUpi?upiid=$upiid&amount=$amount&merchantId=$mid&token=$token&orderid=$randid",
//           CURLOPT_RETURNTRANSFER => true,
//           CURLOPT_ENCODING => '',
//           CURLOPT_MAXREDIRS => 10,
//           CURLOPT_TIMEOUT => 0,
//           CURLOPT_FOLLOWLOCATION => true,
//           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//           CURLOPT_CUSTOMREQUEST => 'GET',
//           CURLOPT_HTTPHEADER => array(
//             'Content-Type: application/json'
//           ),
//         ));
//         //dd($curl);
//         $response = curl_exec($curl);
//       //echo $response;die;

//           $datta=json_decode($response);
//           $status = $datta->status;
//         $error = $datta->error;
        
//         if ($status == 400) {
//             return redirect()->back()->with('error', $error);
//     }
//         if (curl_errno($curl)) {
//             echo 'Error: ' . curl_error($curl);
//         } else {
//             echo $response;
//         }
//         curl_close($curl);
//         // DB::select("UPDATE `withdraw_histories` SET `status` = '2', `response` = ?,`remark`= 'by upi' WHERE id = ?", [$response, $id]);
//       DB::update("UPDATE `withdraw_histories` SET `status` = ?, `response` = ?, `remark` = 'by upi' WHERE id = ?", [2, $response, $id]);

//         return redirect()->route('widthdrawl', '1')->with('key', 'value');
//         } else {
//             return redirect()->route('login');
//         }
    
//     } else {
//         return redirect()->route('widthdrawl', '1')
//             ->withInput()  
//             ->withErrors(['pin' => 'Invalid pin. Please try again.']);
//     }
// }


	
public function success(Request $request, $id)
{
    $value = $request->session()->has('id');
    
    $pin = 2020;

    $inputPin = $request->input('pin');
    if ($inputPin == $pin) {
    if (!empty($value)) {
       
        $data = DB::select("SELECT account_details.*, users.email AS email, users.mobile AS mobile, withdraw_histories.amount AS amount, admin_settings.longtext AS mid, 
                            (SELECT admin_settings.longtext FROM admin_settings WHERE id = 13) AS token, 
                            (SELECT admin_settings.longtext FROM admin_settings WHERE id = 14 ) AS orderid 
                            FROM account_details 
                            LEFT JOIN users ON account_details.user_id = users.id 
                            LEFT JOIN withdraw_histories ON withdraw_histories.user_id = users.id AND withdraw_histories.account_id = account_details.id 
                            LEFT JOIN admin_settings ON admin_settings.id = 12 
                            WHERE withdraw_histories.id = ?", [$id]);
   
        if (empty($data)) {
           
            return redirect()->route('widthdrawl', '1')->with('error', 'No withdrawal data found for the specified ID.');
        }
       
        // If data exists, proceed with setting up the payout
        $object = $data[0];  // Get the first item from the array (as there should only be one)
        $name = $object->name;
        $ac_no = $object->account_number;
        $ifsc = $object->ifsc_code;
        $bankname = $object->bank_name;
        $email = $object->email;
        $mobile = $object->mobile;
        $amount = $object->amount;
        $mid = $object->mid;
        $token = $object->token;
        $orderid = $object->orderid;

        $rand = rand(11111111111111, 99999999999999);
        $randid = "$rand";

        // Prepare the payout data
        $payoutdata = json_encode([
            "merchant_id" => $mid,
            "merchant_token" => $token,
            "account_no" => $ac_no,
            "ifsccode" => $ifsc,
            "amount" => $amount,
            "bankname" => $bankname,
            "remark" => "payout",
            "orderid" => $randid,
            "name" => $name,
            "contact" => $mobile,
            "email" => $email
        ]);
          //dd($payoutdata);
        // Encode the payout data using base64
        $salt = base64_encode($payoutdata);
        //dd($salt);

        // Prepare the JSON data to send via cURL
        $json = [
            "salt" => $salt
        ];

        // Initialize cURL session
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://indianpay.co.in/admin/single_transaction',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($json),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        // Execute cURL request and get the response
        $response = curl_exec($curl);
        //  echo $response;
        // die;
          $datta=json_decode($response);
         // dd($datta);
           $status = $datta->status;
        $error = $datta->error;
        
        // Check if the status is 400
        if ($status == 400) {
            return redirect()->back()->with('error', $error);
    }

        // Check for errors
        if (curl_errno($curl)) {
            echo 'Error: ' . curl_error($curl);
        } else {
            // Print the response
            echo $response;
        }

        // Close cURL session
        curl_close($curl);

        // Update the withdraw history status with the response
        DB::select("UPDATE `withdraw_histories` SET `status` = '2', `response` = ? WHERE id = ?", [$response, $id]);
        //dd("$datta");
       
       // $this->upi($request); 
       
        return redirect()->route('widthdrawl', '1')->with('key', 'value');
        } else {
            return redirect()->route('login');
        }
    
    } else {
        // Pin does not match, return an invalid pin message
        return redirect()->route('widthdrawl', '1')
            ->withInput()  // Keep user input in the form
            ->withErrors(['pin' => 'Invalid pin. Please try again.']);
    }
}

		
		
		
    public function reject(Request $request,$id)
  {
		
  $rejectionReason = $request->input('msg');
		
		$data=DB::select("SELECT * FROM `withdraw_histories` WHERE id=$id;");
	
		$amt=$data[0]->amount;
		$useid=$data[0]->user_id;
         $value = $request->session()->has('id');
			
     if(!empty($value))
        {
            // dd("UPDATE `withdraw_histories` SET `status`='3' WHERE id=$id;");
     $ss= DB::select("UPDATE `withdraw_histories` SET `status`='3',`rejectmsg`='$rejectionReason' WHERE id=$id;");
    //dd("UPDATE `users` SET `wallet`=`wallet`+'$amt' WHERE id=$useid;");
	DB::select("UPDATE `users` SET `wallet`=`wallet`+'$amt' WHERE id=$useid;");
	//DB::select("UPDATE `users` SET `wallet`=`wallet`+'$amt',`winning_wallet`=`winning_wallet`+'$amt' WHERE id=$useid;");
		         //return view('widthdrawl.index', compact('widthdrawls'))->with($id,'0');
return redirect()->route('widthdrawl', '1')->with('key', 'value');
		  }
		 else
        {
           return redirect()->route('login');  
        }
			

       // return redirect()->route('widthdrawl/0');
  }

    public function all_successold()    
        {           
    		$value = $request->session()->has('id');
    		
    		
         if(!empty($value))
            {
          DB::select("UPDATE `withdraw_histories` SET `status`='2' WHERE `status`='1';");
    		         return view('widthdrawl.index', compact('widthdrawls'))->with($id,'1');
    	 }
    else
            {
               return redirect()->route('login');  
            }
    			
          //return redirect()->route('widthdrawl/0');
    }

    
public function only_success(Request $request,$id)    
{           
    $value = $request->session()->has('id');
    // dd($id);
     $pin = 2020;

    // Retrieve the pin input from the request (e.g., assuming the input name is 'pin')
    $inputPin = $request->input('pin');
//dd($pin , $inputPin);
    // Check if the input pin matches the predefined pin
    if ($inputPin == $pin) {
    
    if(!empty($value))
    {
        // Update withdraw histories status
        DB::update("UPDATE `withdraw_histories` SET `status` = '2' WHERE `status` = '1' AND `id` = ?", [$id]);
        
        // Fetch the withdraw histories data if necessary
       // $widthdrawls = DB::table('withdraw_histories')->get();
        
        // Pass $widthdrawls to the view
         return redirect()->back()->with('success', 'withdraw approved successfully!');
    }
    else
    {
        // If no session ID is found, redirect to login
        return redirect()->route('login');  
    }
    } else {
        // Pin does not match, return an invalid pin message
        return redirect()->route('widthdrawl', '1')
            ->withInput()  // Keep user input in the form
            ->withErrors(['pin' => 'Invalid pin. Please try again.']);
    }
}

// public function processWithdrawal(Request $request)
// {
//     // Validation
//     $request->validate([
//         'upi_id' => 'required|string',
//         'amount' => 'required|numeric|min:1',
//         'type' => 'required|numeric', // Type (1 for IndianPay, 2 for UPI)
//     ]);

//     $type = $request->input('type');

//     // Checking the type and calling respective function
//     if ($type == 1) {
//         // Call to IndianPay API (existing logic)
//         $upiId = $request->input('upi_id');
//         $amount = $request->input('amount');
//         $merchantId = env('MERCHANT_ID');
//         $apiToken = env('API_TOKEN');
//         $orderId = uniqid('WD_'); // Unique withdrawal order ID

//         $url = "https://indianpay.co.in/admin/WithdrawViaUpi?upiid=$upiId&amount=$amount&merchantId=$merchantId&token=$apiToken&orderid=$orderId";

//         // API request to IndianPay
//         $response = Http::get($url);

//         // Handle response from IndianPay API
//         if ($response->successful()) {
//             DB::table('transactions')->insert([
//                 'upi_id' => $upiId,
//                 'amount' => $amount,
//                 'merchant_id' => $merchantId,
//                 'order_id' => $orderId,
//                 'status' => 'processing',
//                 'type' => 'withdraw',
//                 'created_at' => now(),
//                 'updated_at' => now(),
//             ]);

//             return response()->json([
//                 'message' => 'IndianPay Withdrawal request initiated',
//                 'order_id' => $orderId,
//                 'response' => $response->json(),
//             ], 200);
//         }

//         return response()->json([
//             'message' => 'IndianPay Withdrawal request failed',
//             'error' => $response->body(),
//         ], 400);
//     }

//     if ($type == 2) {
//         // Call the private upi() function when type is 2
//         return $this->upi($request); // Calling the private upi function
//     }

//     return response()->json([
//         'message' => 'Invalid type',
//     ], 400);
// }
	
// 	public function indiaonlin_payout(Request $request,$id)
//     {
// 		$value = $request->session()->has('id');
		
//      if(!empty($value))
//         {
        
//          $data=DB::select("SELECT account_details.*, users.email AS email, users.mobile AS mobile, withdraw_histories.amount AS amount, admin_settings.longtext AS mid, (SELECT admin_settings.longtext FROM admin_settings WHERE id = 13) AS token, (SELECT admin_settings.longtext FROM admin_settings WHERE id = 14 ) AS orderid FROM account_details LEFT JOIN users ON account_details.user_id = users.id LEFT JOIN withdraw_histories ON withdraw_histories.user_id = users.id && withdraw_histories.account_id=account_details.id LEFT JOIN admin_settings ON admin_settings.id = 12 WHERE withdraw_histories.id=$id;");
       
//          foreach ($data as $object) {
            
//             $name= $object->name;
//             $ac_no= $object->account_number;
//             $ifsc=$object->ifsc_code;
//             $bankname= $object->bank_name;
//             $email= $object->email;
//             $mobile=$object->mobile;
//             $amount=$object->amount;
           
//             $token=$object->token;
//             $orderid=$object->orderid;
//         }
// $rand = rand(11111111, 99999999);
// $date = date('YmdHis');
// $invoiceNumber = $date . $rand;
		 
// 		$data = [
//     "merchantId" => 204,
//     "secretKey" => "1a89da05-0607-4f7b-b3fe-6311ce14cb1c",
//     "apiKey" => "5692d831-decd-450c-8ff5-d1d11943dc82",
//     "invoiceNumber" => $invoiceNumber,
//     "customerName" => $name,
//     "phoneNumber" => $mobile,
//     "payoutMode" => "IMPS",
//     "payoutAmount" => 1,
//     "accountNo" => $ac_no,
//     "ifscBankCode" => $ifsc,
//     "ipAddress" => "35.154.155.190"
// ];

		 
//          $encodeddata=json_encode($data);
		
// 			$curl = curl_init();

// 			curl_setopt_array($curl, array(
// 			  CURLOPT_URL => 'https://indiaonlinepay.com/api/iop/payout',
// 			  CURLOPT_RETURNTRANSFER => true,
// 			  CURLOPT_ENCODING => '',
// 			  CURLOPT_MAXREDIRS => 10,
// 			  CURLOPT_TIMEOUT => 0,
// 			  CURLOPT_FOLLOWLOCATION => true,
// 			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
// 			  CURLOPT_CUSTOMREQUEST => 'POST',
// 			  CURLOPT_POSTFIELDS =>$encodeddata,
// 			  CURLOPT_HTTPHEADER => array(
// 				'Content-Type: application/json',
// 				'Cookie: Path=/'
// 			  ),
// 			));

// 			$response = curl_exec($curl);

// 			curl_close($curl);
		 
// 			echo  $response; 
// 		 $dataArray = json_decode($response, true);

//          $referenceId=$dataArray['Data']['ReferenceId'];
// 		 $Status=$dataArray['Data']['Status'];
// 		 if($Status == "Received"){
		 
   
//          DB::select("UPDATE `withdraw_histories` SET `referenceId`='$referenceId',`response`='$response',status='2' WHERE id=$id;");
// 		 return redirect()->route('widthdrawl', '1')->with('key', 'value');
// 		 }
//       return redirect()->route('widthdrawl', '1')->with('key', 'value');
//     }
// 		else
//         {
//           return redirect()->route('login');  
//         }
			
			
//     }
	
	
	
	

//     public function sendEncryptedPayoutRequest()
//     {
        
//     $url = 'https://dashboard.skill-pay.in/crmpre/PayoutBulkRaised';
    
//     // Secret key and encryption method
//     $secretKey = 'tE2Pl4nM4Bj1Ez4lA9kP9fu7Qc5jG4jT'; // Replace with your secret key
//     $cipherMethod = 'AES-256-CBC'; // Encryption method
//     $iv = openssl_random_pseudo_bytes(16); // Generate an initialization vector (IV)
    
//     $orderid = now()->format('YmdHis') . rand(11111, 99999);

//     // The data to be encrypted (JSON)
//     $payload = [
//         "type" => "WITHDRAW_SETTELEMENT",
//         "description" => "Payout",
//         "AuthID" => "M00006488",
//         "paymentRequests" => [
//             [
//                 "amount" => "10",
//                 "ClientTxnId" => $orderid,
//                 "txnMode" => "IMPS",
//                 "account_number" => "6319094757",
//                 "account_Ifsc" => "IDIB000K236",
//                 "bank_name" => "Bank of india",
//                 "account_holder_name" => "Aneeta Jaiswal",
//                 "beneficiary_name" => "Aneeta Jaiswal",
//                 "vpa" => "NA",
//                 "adf1" => "9695454109",
//                 "adf2" => "test@gmail.com",
//                 "adf3" => "NA",
//                 "adf4" => "NA",
//                 "adf5" => "NA"
//             ]
//         ]
//     ];

//     // Convert the payload to JSON
//     $jsonData = json_encode($payload);

//     // Encrypt the data using AES-256-CBC
//      $encryptedData = openssl_encrypt($jsonData, $cipherMethod, $secretKey, 0, $iv);
//     // Base64 encode the IV and encrypted data for safe transmission
//     $encryptedPayload = base64_encode($iv . $encryptedData);
    
//     $data = [
//         "AuthID" => "M00006488",
//         "EncReq" => $encryptedPayload
//     ];

//     // Send the encrypted data to the server
//     // $response = Http::withHeaders([
//     //     'Content-Type' => 'application/json',
//     // ])->post($url, [
//     //     'data' => $encryptedPayload
//     // ]);
//     $jsonDataaa = json_encode($data);
    
//       // Send the POST request with JSON data
//     $response = Http::withHeaders([
//         'Content-Type' => 'application/json',
//     ])->post($url, $jsonDataaa);
    

    
//     // Handle the JSON response
//     if ($response->successful()) {
//         // Decode the response body as JSON
//         $responseData = $response->json();
        
      

//         // Example: Accessing different parts of the response
//         if (isset($responseData['status']) && $responseData['status'] === 'success') {
//             return response()->json([
//                 'message' => 'Payout request was successful!',
//                 'transaction_id' => $responseData['transaction_id'],
//                 'details' => $responseData['details']
//             ]);
//         } else {
//             return response()->json([
//                 'message' => 'Payout request failed!',
//                 'error' => $responseData['message'] ?? 'No error message provided',
//                 'error_code' => $responseData['error_code'] ?? 'Unknown error'
//             ], 400);  // Bad request
//         }
//     } else {
//         // If the request fails, return the status code and error message
//         return response()->json([
//             'message' => 'Failed to connect to API',
//             'status_code' => $response->status()
//         ], 500);  // Internal server error
//     }
// }


// public function sendEncryptedPayoutRequest($id)
// {
//     $validator = Validator::make(['id' => $id], [
//     'id' => 'required|exists:withdraw_histories,id',
//     ]);
    
//     $withdrawHistory = WithdrawHistory::with('account','user')->where('id',$id)->where('type',3)->where('status',1)->first();
    
  
    
//     $url = 'https://dashboard.skill-pay.in/crmpre/PayoutBulkRaised';

//     // Secret key and encryption method
//     $secretKey = 'tE2Pl4nM4Bj1Ez4lA9kP9fu7Qc5jG4jT'; // Replace with your secret key
//     $cipherMethod = 'AES-256-CBC'; // Encryption method
//     $iv = openssl_random_pseudo_bytes(16); // Generate a random IV
    
//     $orderid = now()->format('YmdHis') . rand(11111, 99999);

//     // The data to be encrypted (JSON)
//     $payload = [
//         "type" => "WITHDRAW_SETTELEMENT",
//         "description" => "Payout",
//         "AuthID" => "M00006488",
//         "paymentRequests" => [
//             [
//                 "amount" => $withdrawHistory->amount,
//                 "ClientTxnId" => $orderid,
//                 "txnMode" => "IMPS",
//                 "account_number" => $withdrawHistory->account->account_number,
//                 "account_Ifsc" => $withdrawHistory->account->ifsc_code,
//                 "bank_name" => $withdrawHistory->account->bank_name,
//                 "account_holder_name" => $withdrawHistory->account->name,
//                 "beneficiary_name" => $withdrawHistory->account->name,
//                 "vpa" => "NA",
//                 "adf1" => $withdrawHistory->user->username,
//                 "adf2" => $withdrawHistory->user->mobile,
//                 "adf3" => "NA",
//                 "adf4" => "NA",
//                 "adf5" => "NA"
//             ]
//         ]
//     ];

//     // Convert the payload to JSON
//     $jsonData = json_encode($payload);

//     // Encrypt the JSON data using AES-256-CBC
//     $encryptedData = openssl_encrypt($jsonData, $cipherMethod, $secretKey, 0, $iv);
    
//     // Base64 encode the IV and the encrypted data for transmission
//     $encryptedPayload = base64_encode($iv . $encryptedData);

//     // Prepare the data to be sent
//     $data = [
//         "AuthID" => "M00006488",
//         "EncReq" => $encryptedPayload
//     ];

//     // Send the POST request with JSON-encoded data
//     $response = Http::withHeaders([
//         'Content-Type' => 'application/json',
//     ])->post($url, $data);
    

//   if ($response->successful()) {
//         // Decode the response body as JSON
//         $responseData = $response->json();

//         // Check if the response contains encrypted data
//         if (isset($responseData['EncData'])) {
//             // Extract the encrypted data
//             $encryptedData = base64_decode($responseData['EncData']);

//             // Extract IV (initial 16 bytes) and encrypted data
//             $iv = substr($encryptedData, 0, 16);
//             $encryptedData = substr($encryptedData, 16);

//             // Decrypt the data using AES-256-CBC
//             $secretKey = 'tE2Pl4nM4Bj1Ez4lA9kP9fu7Qc5jG4jT'; // Replace with your secret key
//             $cipherMethod = 'AES-256-CBC';
//             $decryptedData = openssl_decrypt($encryptedData, $cipherMethod, $secretKey, 0, $iv);

//             // Parse the decrypted JSON
//             $decryptedJson = json_decode($decryptedData, true);

//             // Now you can access the decrypted data
//              echo json_encode($decryptedJson);
//              die;
//         }

//         // Example: Accessing different parts of the response
//         if (isset($responseData['Status']) && $responseData['Status'] === 'Success') {
//             // return response()->json([
//             //     'message' => 'Payout request was successful!',
//             //     'PayReqId' => $responseData['PayReqId'],
//             //     'details' => $responseData['ResponseData'] ?? []
//             // ]);
            
//             return redirect()->back()->with('success','Payout request was successful!');
            
//         } else {
            
//             user::where('id',$withdrawHistory->user_id)->where('status',1)
//                     ->update(['wallet' => DB::raw("wallet + $withdrawHistory->amount")]);
                    
//             WithdrawHistory::where('id',$id)->where('type',3)->where('status',1)->update(['status' => 3]);;
            
//             // return response()->json([
//             //     'message' => 'Payout request failed!',
//             //     'error' => $responseData['RespMessage'] ?? 'No error message provided',
//             // ], 400); // Bad request
            
//             return redirect()->back()->with('error','Payout request failed!');
//         }
//     } else {
//         // If the request fails, return the status code and error message
//         return response()->json([
//             'message' => 'Failed to connect to API',
//             'status_code' => $response->status()
//         ], 500); // Internal server error
//     }

// }



    // Encryption and Decryption Functions
    private function encryptData($data, $key, $iv)
    {
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        if ($encrypted === false) {
            abort(500, 'Encryption failed');
        }
        return $encrypted;
    }

    private function decryptData($data, $key, $iv)
    {
        $decrypted = openssl_decrypt($data, 'aes-256-cbc', $key, 0, $iv);
        if ($decrypted === false) {
            abort(500, 'Decryption failed');
        }
        return $decrypted;
    }

    public function sendEncryptedPayoutRequest($id)
    {
        
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:withdraw_histories,id',
        ]);
        
        $withdrawHistory = WithdrawHistory::with('account','user')->where('id',$id)->where('type',1)->where('status',1)->first();
      
       
       
        $transactionId = now()->format('YmdHis') . rand(11111, 99999);
        
        $authId = 'M00006488';
        $authKey = 'tE2Pl4nM4Bj1Ez4lA9kP9fu7Qc5jG4jT';
    
        $amount = $withdrawHistory->amount;

        // Prepare JSON Data
        $jsonData = json_encode([
            "type" => "test",
            "description" => "Salary Payout",
            "AuthID" => $authId,
            "paymentRequests" => [
                [
                    "amount" => "$amount",
                    "ClientTxnId" => $withdrawHistory->order_id,
                    "txnMode" => "IMPS",
                    "account_number" => $withdrawHistory->account->account_number,
                    "account_Ifsc" => $withdrawHistory->account->ifsc_code,
                    "bank_name" => $withdrawHistory->account->bank_name,
                    "account_holder_name" => $withdrawHistory->account->name,
                    "beneficiary_name" => $withdrawHistory->account->name,
                    "vpa" => "NA",
                    "adf1" => $withdrawHistory->user->mobile,
                    "adf2" => $withdrawHistory->user->email,
                    "adf3" => "NA",
                    "adf4" => "NA",
                    "adf5" => "NA"
                ]
            ]
        ]);
        
        
        // dd($jsonData);

        if (!$jsonData) {
             return response()->json(['error' => 'Failed to encode JSON data'], 500);
            
        }

        // Encrypt Data
        $iv = substr($authKey, 0, 16);
        $encryptedData = $this->encryptData($jsonData, $authKey, $iv);

        // Prepare POST Data
        $postData = [
            'EncReq' => $encryptedData,
            'AuthID' => $authId
        ];

        // Send POST Request
        $url = 'https://dashboard.skill-pay.in/crmpre/PayoutBulkRaised';

        try {
            $response = Http::post($url, $postData);
            
            if ($response->failed()) {
                
                 user::where('id',$withdrawHistory->user_id)->where('status',1)
                    ->update(['wallet' => DB::raw("wallet + $withdrawHistory->amount")]);
                    
             WithdrawHistory::where('id',$id)->where('type',3)->where('status',1)->update(['status' => 3]);
             
             return redirect()->back()->with('error','Payout request failed!');
                
               // return response()->json(['error' => 'Failed to send request'], 500);
            }

            // Decode the response
            $responseData = $response->json();
            
           // return response()->json($responseData);
           
           WithdrawHistory::where('id',$id)->where('type',3)->where('status',1)->update(['status' => 2]);
           
           return redirect()->back()->with('success','Payout request was successful!');

        } catch (\Exception $e) {
            return response()->json(['error' => 'Request failed: ' . $e->getMessage()], 500);
        }
    }



      public function withdraw_response()
{
    // Get the withdraw responses from the database
    $withdraw_responses = DB::select("SELECT `id`, `response` FROM `withdraw_histories` WHERE `response` IS NOT NULL;");
    
    // Loop through the results
    foreach ($withdraw_responses as $response) {
        $user_id = $response->id;
        $response_text = $response->response;
        $response_data = json_decode($response_text);
        $status = $response_data->status;
        //dd($status);
        // Update the status in the database based on the response status
        if ($status == 200) {
            DB::update("UPDATE `withdraw_histories` SET `status` = ? WHERE `id` = ?", [4, $user_id]);
        } else {
            DB::update("UPDATE `withdraw_histories` SET `status` = ? WHERE `id` = ?", [5, $user_id]);
        }
    }
}


	

	
	

}
