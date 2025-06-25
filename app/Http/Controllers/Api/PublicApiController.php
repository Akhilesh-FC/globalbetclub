<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Payin;
use App\Models\Withdraw_history;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\Setting;
use App\Models\All_image;
use App\Models\Slider;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Account_detail;
use DateTime;
use App\Models\Wallet_history;
use Illuminate\Support\Facades\Http;
use App\Models\CustomerService;
use Illuminate\Support\Facades\Log;
use App\Helper\jilli;
use URL;



class PublicApiController extends Controller
{
    public function getAllNotices()
    {
        $notices = DB::select('SELECT title, content, image, status FROM Notice');
    
        return response()->json([
            'status' => 'success',
            'data' => $notices,
        ]);
    }
    
    public function country(Request $request)
	{
		$search = $request->input('search');

		// Fetch all columns from the country table with search on multiple columns
		$query = DB::table('country');

		if (!empty($search)) {
			$query->where('sortname', 'LIKE', "%{$search}%")
				->orWhere('name', 'LIKE', "%{$search}%")
				->orWhere('phone_code', 'LIKE', "%{$search}%");
		}

		$countries = $query->get();

		return response()->json([
			'status' => 'success',
			'data' => $countries,
		]);
	}
	

	  protected function generateRandomUID() {
					$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$digits = '0123456789';

					$uid = '';

					// Generate first 4 alphabets
					for ($i = 0; $i < 4; $i++) {
						$uid .= $alphabet[rand(0, strlen($alphabet) - 1)];
					}

					// Generate next 4 digits
					for ($i = 0; $i < 4; $i++) {
						$uid .= $digits[rand(0, strlen($digits) - 1)];
					}

					return $this->check_exist_memid($uid);
					
				}

	  protected function check_exist_memid($uid){
					$check = DB::table('users')->where('u_id',$uid)->first();
					if($check){
						return $this->generateRandomUID(); // Call the function using $this->
					} else {
						return $uid;
					}
				}


public function login(Request $request) {
    // Define validation rules
    $rules = [
        'identity' => 'required|string', // either email or mobile
        'password' => 'required|string|min:6',
        'country_code' => 'required|string',
        'login_token' => 'nullable|string', // login_token is optional
    ];

    // Validate the request input using the defined rules
    $validator = Validator::make($request->all(), $rules);

    // Check if validation fails
    if ($validator->fails()) {
        // Return validation errors if any
        return response()->json([
            'message' => $validator->errors()->first(),
            'status' => '400'
        ], 400);
    }

    // Get validated input
    $identity = $request->input('identity');
    $password = $request->input('password');
    $login_token = $request->input('login_token');
    $country_code = $request->input('country_code');

    // Check if the provided credentials are valid, including country code and token
    $user = $this->getUserByCredentials($identity, $password, $country_code, $login_token);

    if ($user) {
        // If login_token is provided, update it in the database
        if ($login_token) {
            // Assuming you have a method to update the token
            $this->updateLoginToken($user->id, $login_token);
        }

        $response = [
            'message' => 'Login successful',
            'status' => '200',
            'id' => $user->id,
        ];
        return response()->json($response, 200);
    } else {
        $response = [
            'message' => 'Invalid credentials or country code mismatch. Contact admin..!',
            'status' => '401',
        ];
        return response()->json($response, 401);
    }
}


// // Update login token for the user
public function updateLoginToken($user_id, $login_token) {
    // Update the user's login token in the database using DB facade
    $updated = DB::table('users') // Assuming 'users' is the table name
        ->where('id', $user_id)
        ->update(['login_token' => $login_token]);

    return $updated;
}


private function generateSecureRandomString($length = 8)
{
	//$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Only uppercase letters
    $characters = '0123456789'; // You can expand this to include more characters if needed.
    $randomString = '';

    // Loop to generate the random string
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $randomString;
}



private function getUserByCredentials($identity, $password) {
    $user = User::where(function ($query) use ($identity) {
                $query->where('email', $identity)
                      ->orWhere('mobile', $identity);
            })
            ->where('password', $password)
            ->where('status', 1)
            ->first();

    return $user;
}



public function register(Request $request)
{
    // Validate input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|unique:users,email',
        'country_code' => 'required',
        'mobile' => 'required|numeric|digits:10|unique:users,mobile',
        'password' => 'required|min:8',
        'password_confirmation' => 'required|min:8|same:password',
        'referral_code' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 200);
    }

    // Generate random name and referral code
    $randomName = 'User_' . strtoupper(Str::random(5));
    $randomReferralCode = 'ZUP' . strtoupper(Str::random(4));
    $baseUrl = URL::to('/');

    // Generate UID
    $uid = $this->generateRandomUID(); // Make sure this function exists and returns unique UID

    // Referral user ID
    $referralUserId = 1;

    // If referral_code is filled, try to find referrer
    if ($request->filled('referral_code')) {
        $referrer = DB::table('users')->where('referral_code', $request->referral_code)->first();
        if ($referrer) {
            $referralUserId = $referrer->id;
        }
    }

    // Insert into database
    $userId = DB::table('users')->insertGetId([
        'username' => $randomName,
        'u_id' => $uid,
        'email' => $request->email,
        'mobile' => $request->mobile,
        'password' => $request->password, // Plain text password (NOT RECOMMENDED for production)
        // 'userimage' => URL::to('/') . "/image/download.png",
         'userimage' => $baseUrl . "/image/download.png",
        'status' => 1,
        'referral_code' => $randomReferralCode,
        'referral_user_id' => $referralUserId,
        'wallet' => 28,
        'country_code' => $request->country_code,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    if ($userId) {
        return response()->json([
            'status' => 200,
            'message' => 'Registration successful',
            'data' => [
                'userId' => $userId,
                'username' => $randomName,
                'referral_code' => $randomReferralCode,
                'referral_user_id' => $referralUserId
            ],
        ], 200);
    }

    return response()->json([
        'status' => 400,
        'message' => 'Failed to register.',
    ], 400);
}


// public function register(Request $request)
// {
//     // Validate the incoming request
//     $validator = Validator::make($request->all(), [
//         'email' => 'required|email|unique:users,email',
//         'country_code' => 'required',
//         'mobile' => 'required|numeric|digits:10|unique:users,mobile',
//         'password' => 'required|min:8',
//         'password_confirmation' => 'required|min:8|same:password',
//         'referral_code' => 'nullable|string|exists:users,referral_code',
//     ]);

//     // Return validation error if it fails
//     if ($validator->fails()) {
//         return response()->json([
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ], 200);
//     }

//     // Generate random name and referral code
//     $randomName = 'User_' . strtoupper(Str::random(5));
//     $randomReferralCode = 'ZUP' . strtoupper(Str::random(4));
// //dd($randomReferralCode);
//     // Get base URL
//     $baseUrl = URL::to('/');
//     $uid = $this->generateRandomUID();

//     // Prepare data for insertion using Eloquent model
//     $data = [
//         'username' => $randomName,
//         'u_id' => $uid,  // Make sure to add 'u_id' here
//         'mobile' => $request->mobile,
//          'password' =>$request->password, 
//         'image' => $baseUrl . "/image/download.png",
//         'status' => 1,
//         'referral_code' => $randomReferralCode,
//         'wallet' => 28,
//         'country_code' => $request->country_code,
//     ];
// //dd($data);
//     // Check for referral code and set referrer ID if applicable
//     if ($request->has('referral_code')) {
//         $referrer = User::where('referral_code', $request->referral_code)->first();
//         if ($referrer) {
//             $data['referrer_id'] = $referrer->id;
//         }
//     }

//     // Create user
//     $user = User::create($data);
//     if ($user) {
//         $success['userId'] = $user->id;
//         $success['token'] = $user->createToken('UserApp')->plainTextToken;
//         return response()->json([
//             'status' => 200,
//             'message' => 'Registration successful',
//             'data' => $success,
//         ], 200);
//     }

//     // Handle API errors
//     return response()->json([
//         'status' => 400,
//         'message' => 'Failed to register.',
//     ], 400);
// }



 public function Account_view(Request $request)
       {
       
         $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
    
    $userid=$request->user_id;
       $accountDetails = DB::select("SELECT * FROM `account_details` WHERE user_id=$userid
");



        if ($accountDetails) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $accountDetails
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
///// Add Bank details ///
public function add_account(Request $request)
{
    if ($request->isMethod('post')) {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'name' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'bank_name' => 'required',
            'branch' => 'required',
            'upi_id' => 'required',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Fetch user account details
        $user_id = $request->user_id;
        $upi_id = $request->upi_id;
        $user_details = DB::table('account_details')->where('user_id', $user_id)->first();

        // Check if user details exist
        if (empty($user_details)) {
            // Insert new account details
            $data = [
                'user_id' => $request->input('user_id'),
                'name' => $request->input('name'),
                'account_number' => $request->input('account_number'),
                'ifsc_code' => $request->input('ifsc_code'),
                'bank_name' => $request->input('bank_name'),
                'branch' => $request->input('branch'),
                'upi_id' => $request->input('upi_id'),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $inserted = DB::table('account_details')->insert($data);

            if ($inserted) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Account added successfully!'
                ], 200);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Internal error!'
                ], 400);
            }
        } else {
            // Update existing account details
            $updated = DB::table('account_details')
                ->where('user_id', $user_id)
                ->update(['upi_id' => $upi_id]);

            if ($updated) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Account updated successfully!'
                ], 200);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Internal error during update!'
                ], 400);
            }
        }
    } else {
        return response()->json(['error' => 'Unsupported request method'], 405);
    }
}

////// Withdraw /////
public function withdraw_oldd(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'account_id' => 'required',
        'type' => 'required',
        'amount' => 'required|numeric',
        //'upi_id' => 'required',
    ]);

    $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                      'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }

    $userid = $request->input('user_id');
    $accountid = $request->input('account_id');
    $amount = $request->input('amount');
    $type = $request->input('type');
   // $upi_id = $request->input('upi_id');
    
     $date = date('YmdHis');
        $rand = rand(11111, 99999);
        $orderid = $date . $rand;
    if ($amount >= 200 && $amount<=25000) {
      //($amount >= 550) 
        $wallet=DB::select("SELECT `wallet`,`recharge`,`first_recharge`,`winning_wallet` FROM `users` WHERE id=$userid");
      $user_wallet=$wallet[0]->wallet;
      $user_recharge=$wallet[0]->recharge;
      //dd($user_recharge);
      $first_recharge=$wallet[0]->first_recharge;
      if($user_recharge == 0){
          if($first_recharge == 1){
        if($user_wallet >= $amount){
      $data= DB::table('withdraw_histories')->insert([
    'user_id' => $userid,
    'amount' => $amount,
    'account_id' => $accountid,
    'type' => $type,
   // 'upi_id' =>$upi_id,
    'order_id' => $orderid,
    'status' => 1,
	'typeimage'=>"https://root.globalbet24.live/uploads/fastpay_image.png",
    'created_at' => now(),
    'updated_at' => now(),
]);
      DB::select("UPDATE `users` SET `wallet`=`wallet`-$amount,`winning_wallet`=`winning_wallet`-$amount WHERE id=$userid;");
 if ($data) {
             $response = [
        'status' =>200,
        'message' => 'Withdraw Request Successfully ..!',
    ];

    return response()->json($response,200);

        } else {
             $response = [
        'status' =>400,
        'message' => 'Internal error..!',
    ];

    return response()->json($response,400);
            
        }
        }else{
      $response = [
        'status' =>400,
        'message' => 'insufficient Balance..!',
    ];

    return response()->json($response,400);
 }  
          }else{
      $response = [
        'status' =>400,
        'message' => 'first rechage is mandatory..!',
    ];

    return response()->json($response,400);
 }     
      }else {
         $response = [
        'status' =>400,
        'message' => 'need to bet amount 0 to be able to Withdraw',
    ];

    return response()->json($response,400);   
      }
        
    }else{
        $response['message'] = "minimum Withdraw 200 And Maximum Withdraw 25000";
            $response['status'] = "400";
            return response()->json($response,400);
    }

    

}

////// Withdraw /////
public function withdraw(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'account_id' => 'required',
        'type' => 'required',
        'amount' => 'required|numeric'
    ]);
    $validator->stopOnFirstFailure(); 
    if($validator->fails()){
         $response = [
                        'status' => 400,
                      'message' => $validator->errors()->first() 
                      ]; 
		
		return response()->json($response,400);
		
    }

    $userid = $request->input('user_id');
    $accountid = $request->input('account_id');
    $amount = $request->input('amount');
    $type = $request->input('type');
   
    $user_details = DB::table('account_details')->where('user_id', $userid)->first();
       $upiid=$user_details->upi_id;
        if (empty($upiid)) {
             $response = [
                        'status' => 500,
                      'message' => "upi_id is required"
                      ]; 
		
		return response()->json($response,400);
        }
 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     // Check if there's a pending withdrawal
    $lastWithdrawal = DB::table('withdraw_histories')
        ->where('user_id', $userid)
        ->orderBy('created_at', 'desc')
        ->first();

    if ($lastWithdrawal && $lastWithdrawal->status == 1) { // Assuming 1 is for pending
        return response()->json([
            'status' => 400,
            'message' => 'You cannot withdraw again until your previous request is approved or rejected.'
        ], 400);
    }

    // Limit to three withdrawals per day
    $withdrawCount = DB::table('withdraw_histories')
        ->where('user_id', $userid)
        ->whereDate('created_at', now())
        ->where('status', 2) // Assuming 2 is for successful withdrawal
        ->count();

    if ($withdrawCount >= 3) {
        $response = [
            'status' => 400,
            'message' => 'You can only withdraw 3 times in a day.'
        ];
        return response()->json($response, 400);
    }

    
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
     $date = date('YmdHis');
     
        $rand = rand(11111, 99999);
        $orderid = $date . $rand;
    if ($amount >= 200 && $amount<=25000) {
      
        $wallet=DB::select("SELECT `wallet`,`recharge`,`first_recharge` FROM `users` WHERE id=$userid");
        
      $user_wallet=$wallet[0]->wallet;
      $user_recharge=$wallet[0]->recharge;
      //dd($user_recharge);
      $first_recharge=$wallet[0]->first_recharge;
      if($user_recharge == 0){
          if($first_recharge == 1){
        if($user_wallet >= $amount){
      $data= DB::table('withdraw_histories')->insert([
    'user_id' => $userid,
    'amount' => $amount,
    'account_id' => $accountid,
    'type' => $type,
   // 'upi_id' =>$upi_id,
    'order_id' => $orderid,
    'status' => 1,
	'typeimage'=>"https://root.globalbet24.live/uploads/fastpay_image.png",
    'created_at' => now(),
    'updated_at' => now(),
]);
     // DB::select("UPDATE `users` SET `wallet`=`wallet`-$amount WHERE id=$userid;");
     DB::update("UPDATE `users` SET `wallet` = `wallet` - ? WHERE id = ?", [$amount, $userid]);

 if ($data) {
             $response = [
        'status' =>200,
        'message' => 'Withdraw Request Successfully ..!',
    ];

    return response()->json($response,200);

        } else {
             $response = [
        'status' =>400,
        'message' => 'Internal error..!',
    ];

    return response()->json($response,400);
            
        }
        }else{
      $response = [
        'status' =>400,
        'message' => 'insufficient Balance..!',
    ];

    return response()->json($response,400);
 }  
          }else{
      $response = [
        'status' =>400,
        'message' => 'first rechage is mandatory..!',
    ];

    return response()->json($response,400);
 }     
      }else {
         $response = [
        'status' =>400,
        'message' => 'need to bet amount 0 to be able to Withdraw',
    ];

    return response()->json($response,400);   
      }
        
    }else{
        $response['message'] = "minimum Withdraw 200 And Maximum Withdraw 25000";
            $response['status'] = "400";
            return response()->json($response,200);
    }

}

public function usdtwithdraw(Request $request)
{
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'usdt_wallet_address' => 'required',
            'amount_inr' => 'required|numeric',
            'amount' => 'required|numeric',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $userid = $request->user_id;
        $amount = $request->amount_inr;
        $usdt_amount = $request->amount;

        // 1. Minimum and maximum amount check
        if ($usdt_amount < 10 || $usdt_amount > 5000) {
            return response()->json([
                'status' => 400,
                'message' => 'Minimum Withdraw is $10 and Maximum is $5000.'
            ], 400);
        }

        // 2. Check if there's a pending withdrawal
        $lastWithdrawal = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastWithdrawal && $lastWithdrawal->status == 1) {
            return response()->json([
                'status' => 400,
                'message' => 'You cannot withdraw again until your previous request is approved or rejected.'
            ], 400);
        }

        // 3. Limit to three withdrawals per day
        $withdrawCount = DB::table('withdraw_histories')
            ->where('user_id', $userid)
            ->whereDate('created_at', now())
            ->where('status', 2) // Assuming 2 is for successful
            ->count();

        if ($withdrawCount >= 3) {
            return response()->json([
                'status' => 400,
                'message' => 'You can only withdraw 3 times in a day.'
            ], 400);
        }

        // 4. Check first recharge and betting condition
        $user = DB::table('users')->where('id', $userid)->first();

        if ($user->recharge > 0) {
            return response()->json([
                'status' => 400,
                'message' => 'Need to bet amount 0 to be able to Withdraw.'
            ], 400);
        }

        if ($user->first_recharge != 1) {
            return response()->json([
                'status' => 400,
                'message' => 'First recharge is mandatory.'
            ], 400);
        }

        // 5. Check wallet balance
        if ($user->wallet < $amount) {
            return response()->json([
                'status' => 400,
                'message' => 'Insufficient Balance.'
            ], 400);
        }

        // Generate 20-digit numeric order_id
        $order_id = 
            mt_rand(1000, 9999) . 
            mt_rand(1000, 9999) . 
            mt_rand(1000, 9999) . 
            mt_rand(1000, 9999) . 
            mt_rand(10, 99);

        // Insert withdrawal request
        $withdraw = DB::table('withdraw_histories')->insert([
            'user_id' => $userid,
            'usdt_wallet_address' => $request->usdt_wallet_address,
            'amount' => $request->amount_inr,
            'usdt_amount' => $request->amount,
            'type' => $request->type,
            'order_id' => $order_id,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Deduct from wallet and winning_wallet
        DB::update("UPDATE users SET wallet = wallet - ?  WHERE id = ?", [
            $amount,  $userid
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'USDT Withdrawal request submitted successfully'
        ], 200);

    } 
}



function usdtwithdrawoldddd(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'usdt_wallet_address' => 'required',
            'amount_inr' => 'nullable|numeric',
            'usdt_amount' => 'nullable|numeric',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 200);
        }

        // 20-digit ka pure numeric order_id generate karna
        $order_id = 
            mt_rand(1000, 9999) .  // 4-digit
            mt_rand(1000, 9999) .  // 4-digit
            mt_rand(1000, 9999) .  // 4-digit
            mt_rand(1000, 9999) .  // 4-digit
            mt_rand(10, 99);       // 2-digit (total 20-digit)

        $withdraw = DB::table('withdraw_histories')->insertGetId([
            'user_id' => $request->user_id,
            'usdt_wallet_address' => $request->usdt_wallet_address,
            'amount' => $request->amount_inr,
            'usdt_amount' => $request->usdt_amount,
            'type' => $request->type,
            'order_id' => $order_id, // 20-digit numeric string
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usdt Withdrawal request submitted successfully',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong'
        ], 200);
    }
}

///// Gift Claim List ////

 public function claim_list(Request $request)
       {
       
         $validator = Validator::make($request->all(), [
            'userid' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
    
    $userid=$request->userid;
       $accountDetails = DB::select("SELECT * FROM `gift_claim` WHERE `userid`=$userid order by id DESC");



        if ($accountDetails) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $accountDetails
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }

//// Gift Card Apply ///

public function giftCartApply(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required',
        'code' => 'required',
    ]);

     $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }

  
        $userid = $request->input('userid');
        $code = $request->input('code');

        $data = DB::table('gift_cart')->where('code', $code)->where('status', 1)->first();

        if ($data) {
            $fixPeople = $data->number_people;
            $availedPeople = $data->availed_num;

            if ($availedPeople < $fixPeople) {
                $claimUser = DB::table('gift_claim')->where('gift_code', $code)->where('userid', $userid)->first();

                if (!$claimUser) {
                    date_default_timezone_set('Asia/Kolkata');
                    $datetime = date('Y-m-d H:i:s');

                    $giftCartAmount = $data->amount;

                    if (!empty($giftCartAmount)) {
                        DB::table('gift_claim')->insert(['userid' => $userid, 'gift_code' => $code, 'amount' => $giftCartAmount]);
                        DB::table('users')->where('id', $userid)->update(['wallet' => DB::raw('wallet + ' . $giftCartAmount), 'bonus' => DB::raw('bonus + ' . $giftCartAmount)]);
                        DB::table('gift_cart')->where('id', $data->id)->update(['availed_num' => DB::raw('availed_num + 1')]);

                        $data = [
                            'userid' => $userid,
                            'amount' => $giftCartAmount,
                            'subtypeid' => 20,
                            'created_at' => $datetime,
                            'updated_at' => $datetime
                        ];
                        DB::table('wallet_history')->insert($data);

                        $response['message'] = " Add $giftCartAmount Rs. Successfully";
                        $response['status'] = "200";
                        return response()->json($response,200);
                    } else {
                        $response['message'] = "No record found";
                        $response['status'] = "400";
                        return response()->json($response,400);
                    }
                } else {
                    $response['message'] = "You have already availed this offer!";
                    $response['status'] = "400";
                    return response()->json($response,400);
                }
            } else {
                $response['message'] = "No longer available this offer.";
                $response['status'] = "400";
                return response()->json($response,400);
            }
        } else {
            $response['message'] = "Invalid Gift Code!";
            $response['status'] = "400";
            return response()->json($response,400);
        }
    
}

///// FeedBack ////

 public function feedback(Request $request)
{
 
        $validator = Validator::make($request->all(), [
            'userid' => 'required',
            'description' => 'required',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];

            return response()->json($response,400);
        }

        $data = array(
            'userid' => $request->input('userid'),
            'description' => $request->input('description'),
            'status' => 1,
            'created_at' => now(),
             'updated_at' => now(),
        );

        $data1 = DB::table('feedbacks')->insert($data);

        if ($data1) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $data1
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'Failed','status' => 400], 400);
        }
}

////Pay Modes ////


public function pay_modes(Request $request)
{
    if ($request->isMethod('get')) {
        $userid = $request->input('userid');
		$type = $request->input('type');
		if($type == ''){
        $check = DB::table('users')->where('first_recharge', '1')->where('id', $userid)->first();

        $pay_modes = DB::table('pay_modes')->where('status', '1')->get();

        if ($pay_modes->isNotEmpty()) {
            $response['msg'] = "Successfully";
            $response['data'] = $pay_modes->toArray();

            if ($check && $check->first_recharge == '1') {
                $response['minimum'] = 500;
                $response['status'] = "200";
            } else {
                $response['minimum'] = 100;
                $response['status'] = "400";
            }

            return response()->json($response);
        } else {
            // If no data is found, set an appropriate response
            $response['msg'] = "No record found";
            $response['status'] = "400";
            return response()->json($response);
        }
	 } else {
        $check = DB::table('users')->where('first_recharge', '1')->where('id', $userid)->first();

        $pay_modes = DB::table('pay_modes')->where('status', '1')->where('type', $type)->get();

        if ($pay_modes->isNotEmpty()) {
            $response['msg'] = "Successfully";
            $response['data'] = $pay_modes->toArray();

            if ($check && $check->first_recharge == '1') {
                $response['minimum'] = 500;
                $response['status'] = "200";
            } else {
                $response['minimum'] = 100;
                $response['status'] = "400";
            }

            return response()->json($response);
        } else {
            // If no data is found, set an appropriate response
            $response['msg'] = "No record found";
            $response['status'] = "400";
            return response()->json($response);
        }
    }
    } else {
        return response()->json(['error' => 'Unsupported request method'], 400);
    }
}

/// transaction_history_list Api ///
	
      public function transaction_history_list()
      {
      $subtype=DB::select("SELECT `id`,`name` FROM `subtype` WHERE 1");

        if ($subtype) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $subtype
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
    
////// Result Api ////
public function transaction_history(Request $request)
{
    $validator = Validator::make($request->all(), [
        'userid' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
    }
    
    $userid = $request->userid;
    $subtype = $request->subtypeid;
     //$offset = $request->offset ?? 0;
    $from_date = $request->created_at;
    //$to_date = $request->created_at;
    //$status = $request->status;

// $status=DB::SELECT("SELECT `status` FROM `users` WHERE id=$userid"); 
// //dd($status);
// 	$ddd=$status[0]->status;
// 	//dd($ddd);
// if($ddd == 1){hea
    $where = [];

    if (!empty($userid)) {
        $where[] = "wallet_history.`userid` = '$userid'";
    }

    if (!empty($from_date)) {
		$newDateString = date("Y-m-d", strtotime($from_date));
		
        $where[] = "DATE(`wallet_history`.`created_at`) = '$newDateString'";
		
    }
    if (!empty($subtype)) {
        $where[] = "`wallet_history`.`subtypeid` = '$subtype'";
    }
    //
    //
    
    $query = "
       SELECT subtype.name as type , wallet_history.amount as amount, wallet_history.created_at as datetime FROM `wallet_history` LEFT JOIN `subtype` on wallet_history.subtypeid = subtype.id
    ";

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY wallet_history.id DESC";

    $results = DB::select($query);
    //dd($results);
if(!empty($results)){
    return response()->json([
        'status' => 200,
        'message' => 'Data found',
        'data' => $results
    ]);
}else{
     return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
}
// }else{
    
//  $response['message'] = "User block by admin..!";
//                 $response['status'] = "401";
//                 return response()->json($response,401);
    
// }
    
}

public function image_all(){
      
         $user = DB::select("SELECT `image` FROM `all_images`
");
          if($user){
          $response =[ 'success'=>"200",'data'=>$user,'message'=>'Successfully'];return response ()->json ($response,200);
      }
      else{
       $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
      } 
    }

      public function forget_pass(Request $request)
      {
		
		   $validator = Validator::make($request->all(), [
          'mobile' => ['required', 'string', 'regex:/^\d{10}$/','exists:users,mobile'], // Ensure 10 digits
	      'password' => 'required|string|min:6'
    ]);

	    $validator->stopOnFirstFailure();
	   
    if($validator->fails()){
		
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ]);
    }
	   
			
	  $user = DB::table('users')->where('mobile',$request->mobile)
		  ->update([
		   'password'=>$request->password
		  ]);
	  
	   return response()->json([
	      'status'=>200,
		  'message'=>'Password reset successfully.',
	   ]);
	   
	   
		
	}
public function profile_old(Request $request) 
      {
          $ldate = new DateTime('now');
        
          
          //dd($login_time);
          $id=$request->id;
    if (empty($id)) {
        return response()->json([
            'status' => 400,
            'message' => 'ID Required'
        ]);
    }
   // $ldate = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    // echo $ldate->format('Y-m-d H:i:s');

    try {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['success' => 400, 'message' => 'User not found..!'], 200);
        }

        // Check if user is blocked (status = 0)
        if ($user->status == 0) {
            return response()->json([
                'success' => 423,
                'message' => 'User block by admin..!'
            ], 423); // 👈 HTTP 204 Status Code
        }
			
            return response()->json([
                'success' => 200,
                'message' => 'User found..!',
                'data' => $user,
                'aviator_link' => "https://aviatorudaan.com/",
                'aviator_event_name' => "jupiter",
                'apk_link' => env('APP_URL') . "jupiter.apk",
                'usdt_payin_amount' => 94,
                'usdt_payout_amount' => 92,
                'telegram' => "https://t.me/Help_jupiter",
                'referral_code_url' => env('APP_URL') . "registerwithref/" . $user->referral_code,
                'last_login_time' => $ldate->format('Y-m-d H:i:s'),
				 // Static India Pay Data
                'india_pay' => [
                    'min_amount' => 110,
                    'max_amount' => 50000
                ],

                // Static USDT Data
                'usdt' => [
                    'min_amount' => 10,
                    'max_amount' => 5000
					]
        ]);
    } catch (Exception $e) {
        return response()->json(['error' => 'API request failed: ' . $e->getMessage()], 500);
    }
}
public function profile(Request $request) 
{
    $ldate = new DateTime('now');
  
    $uid = $request->id;

    if (empty($uid)) {
        return response()->json([
            'status' => 400,
            'message' => 'UID Required'
        ]);
    }
    
    

    // Fetch user data with the necessary join and data
    $data = DB::table('users as u')
        ->select('u.*', 'a1.longtext as minimum_withdraw', 'a2.longtext as maximum_withdraw')
        ->leftJoin('admin_settings as a1', function ($join) {
            $join->on('a1.id', '=', DB::raw(15));
        })
        ->leftJoin('admin_settings as a2', function ($join) {
            $join->on('a2.id', '=', DB::raw(16));
        })
        ->where('u.id', $uid)
        ->limit(1)
        ->first();

    if ($data === null) {
        return response()->json([
            'status' => 400,
            'message' => 'No data found'
        ]);
    }

    // If the user is not blocked (status is 1)
    $status = $data->status;
    if ($status == 1) {

        // Process the wallets
        $thirdpartywallet = isset($data->third_party_wallet) ? $data->third_party_wallet : 0;
        $main_wallet = isset($data->wallet) ? $data->wallet : 0;
        $total_wallet = $thirdpartywallet + $main_wallet;

        // Create the response data array
        $responseData = [
            'id' => $data->id,
            'mobile' => $data->mobile,
            'email' => $data->email,
            'username' => $data->username,
            'userimage' => $data->userimage,
            'recharge' => $data->recharge,
            'u_id' => $data->u_id,
            'login_token' => $data->login_token,
            'referral_code' => $data->referral_code,
            'wallet' => $main_wallet,
            'third_party_wallet' => $thirdpartywallet,
            'total_wallet' => $total_wallet,
            'winning_amount' => $data->winning_wallet,
            'minimum_withdraw' => $data->minimum_withdraw,
            'maximum_withdraw' => $data->maximum_withdraw,
            'last_login_time' => $ldate->format('Y-m-d H:i:s'),
            'apk_link' => "https://root.globalbet24.club/globalbet.apk",
            'referral_code_url' => "https://root.globalbet24.club/registerwithref/" . $data->referral_code,
            'aviator_link' => "https://foundercodetech.com",
            'aviator_event_name' => "gbaviator",
            'wingo_socket_url' => "https://aviatorudaan.com/",
            'wingo_socket_event_name' => "globalbet",
            'status' => "1",
            'type' => "0"
        ];

        return response()->json([
            'success' => 200,
            'message' => 'Data found',
            'data' => $responseData
        ]);
    } else {
        // If the user is blocked
        return response()->json([
            'status' => 401,
            'message' => 'User blocked by admin'
        ]);
    }
}

	
//       public function profile(Request $request) 
//       {
//           $ldate = new DateTime('now');
        
          
//           //dd($login_time);
//           $uid=$request->id;
//     if (empty($uid)) {
//         return response()->json([
//             'status' => 400,
//             'message' => 'UID Required'
//         ]);
//     }

//     // Casting $uid to string
//   // $data = DB::table('users')->where('id',$uid)->first();
// //   $data =DB::select("SELECT u.*, a1.longtext AS minimum_withdraw, a2.longtext AS maximum_withdraw FROM users u LEFT JOIN admin_settings a1 ON a1.id = 15 LEFT JOIN admin_settings a2 ON a2.id = 16 WHERE u.id = $uid LIMIT 1");

// $data = DB::table('users as u')
//             ->select('u.*', 'a1.longtext as minimum_withdraw', 'a2.longtext as maximum_withdraw')
//             ->leftJoin('admin_settings as a1', function ($join) {
//                 $join->on('a1.id', '=', DB::raw(15));
//             })
//             ->leftJoin('admin_settings as a2', function ($join) {
//                 $join->on('a2.id', '=', DB::raw(16));
//             })
//             ->where('u.id', $uid)
//             ->limit(1)
//             ->first();


    
//     $thirdpartywallet = isset($data->third_party_wallet) ? $data->third_party_wallet : 0;
//     $main_wallet = isset($data->wallet) ? $data->wallet : 0;

//     $total_wallet = $thirdpartywallet + $main_wallet;

// 		$status=$data->status;
// 		if($status == 1)
//     {
//     if ($data !== null) {
//         return response()->json([
//             'status' => 200,
//             'message' => 'Data found',
//             'id' => $data->id,
// 			'mobile'=>$data->mobile,
// 			'email'=>$data->email,
// 			'username'=>$data->username,
// 			'userimage'=>$data->userimage,
// 			'recharge'=>$data->recharge,
// 			'u_id'=>$data->u_id,
// 			'login_token'=>$data->login_token,
// 			'referral_code'=>$data->referral_code,
// 			'main_wallet'=>$main_wallet,
// 			'third_party_wallet'=>$thirdpartywallet,
// 			'total_wallet'=>$total_wallet,
// 			'winning_amount'=>$data->winning_wallet,
// 			'minimum_withdraw'=>$data->minimum_withdraw,
// 			'maximum_withdraw'=>$data->maximum_withdraw,
// 			'last_login_time'=>$ldate->format('Y-m-d H:i:s'),
// 			'apk_link'=> "https://root.globalbet24.live/globalbet.apk",
// 			'referral_code_url'=> "https://root.globalbet24.live/registerwithref/".$data->referral_code,
// 			'aviator_link'=>"https://foundercodetech.com",
// 			'aviator_event_name'=>"gbaviator",
// 			'wingo_socket_url'=>"https://aviatorudaan.com/",
// 			'wingo_socket_event_name'=>"globalbet"
//         ]);
//     } else {    
//         return response()->json([
//             'status' => 400,
//             'message' => 'No data found'
//         ]);
//     }
//     }else{
//          $response['message'] = "User block by admin..!";
//                 $response['status'] = "401";
//                 return response()->json($response,401);
//     }
// }

public function Status_list(){
      
      $status= array(
           array(
        'id' => '0',
        'name' => 'All'
    ),
    array(
        'id' => '1',
        'name' => 'Processing'
    ),
    array(
        'id' => '2',
        'name' => 'Completed'
    ),
    array(
        'id' => '3',
        'name' => 'Reject'
    )
);
      
        //  $status = DB::select("SELECT `id`,`name` FROM `status` WHERE 1");
          if($status){
          $response =[ 'success'=>"200",'data'=>$status,'message'=>'Successfully'];return response ()->json ($response,200);
      }
      else{
       $response =[ 'success'=>"400",'data'=>[],'message'=>'Not Found Data'];return response ()->json ($response,400); 
      } 
    }

 public function deposit_history(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        // 'type' is now optional
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    // Extract parameters
    $user_id = $request->user_id;
    $status = $request->status;
    $type = $request->type;
    $date = $request->created_at;

    // Start building the query
    $query = DB::table('payins')
                ->select('cash', 'type', 'status', 'order_id', 'typeimage', 'created_at')
                ->orderByDesc('payins.id');

    // Apply filters based on parameters provided
    if (!empty($user_id)) {
        $query->where('payins.user_id', '=', $user_id);
    }

    if (!empty($status)) {
        $query->where('payins.status', '=', $status);
    }

    // Apply the 'type' filter only if it's provided
    if (isset($type)) {
        // If 'type' is provided, apply the filter
        if (is_numeric($type)) {
            $query->where('payins.type', '=', (int)$type);
        } else {
            // You can handle this in case 'type' is a string
            $query->where('payins.type', '=', $type);
        }
    }

    if (!empty($date)) {
        $newDateString = date("Y-m-d", strtotime($date));
        $query->whereDate('payins.created_at', '=', $newDateString);
    }

    // Execute the query
    $payin = $query->get();

    if ($payin->isNotEmpty()) {
        return response()->json([
            'message' => 'Successfully',
            'status' => 200,
            'data' => $payin
        ], 200);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status' => 400,
            'data' => []
        ], 400);
    }
}


//       public function withdraw_history(Request $request)
//       {
//          $validator = Validator::make($request->all(), [
//         'user_id' => 'required',
// 		'type'=>'required',
//     ]);

//     $date = date('Y-m-d h:i:s');
//      $validator->stopOnFirstFailure();
	   
//     if($validator->fails()){
		
//         return response()->json([
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ],400);
//     }
   
//      $user_id = $request->user_id;
//      $status = $request->status;
//       $type = $request->type;  
// 		$created_at = $request->created_at; 
//          $where = [];

//     if (!empty($user_id)) {
//         $where[] = "withdraw_histories.`user_id` = '$user_id'";
//     }

//     if (!empty($status)) {
//         $where[] = "`withdraw_histories`.`status` = '$status'";
//     }
// 	if (!empty($type)) {
//         $where[] = "`withdraw_histories`.`type` = '$type'";
//     }
// 	if (!empty($created_at)) {
// 		$newDateString = date("Y-m-d", strtotime($date));
//         $where[] = "DATE(`withdraw_histories`.`created_at`) = '$newDateString'";

//     }
//     //
//     //
    
//     $query = "SELECT `id`,`user_id`,`amount`,`type`,`status`,`typeimage`,`order_id`,`created_at` FROM withdraw_histories ";

//     if (!empty($where)) {
//         $query .= " WHERE " . implode(" AND ", $where);
//     }

//     $query .= " ORDER BY withdraw_histories.id DESC";

//     $payin = DB::select($query);

//         if ($payin) {
//             $response = [
//                 'message' => 'Successfully',
//                 'status' => 200,
//                 'data' => $payin
//             ];

//             return response()->json($response,200);
//         } else {
//             return response()->json(['message' => 'No record found','status' => 200,'data' => []], 400);
//         }
//     }

public function withdraw_history(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'type' => 'nullable',
        'created_at' => 'nullable|date', // Ensure date is valid
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    $user_id = $request->user_id;
    $status = $request->status;
    $type = $request->type;
    $created_at = $request->created_at;
    $where = [];

    // User ID condition
    if (!empty($user_id)) {
        $where[] = "withdraw_histories.`user_id` = '$user_id'";
    }

    // Status condition
    if (!empty($status)) {
        $where[] = "`withdraw_histories`.`status` = '$status'";
    }

    // Type condition including type = 0
    if ($type !== null && $type !== '') {
        $where[] = "`withdraw_histories`.`type` = '$type'";
    }

    // Date filter condition
    if (!empty($created_at)) {
        $newDateString = date("Y-m-d", strtotime($created_at));
        $where[] = "DATE(`withdraw_histories`.`created_at`) = '$newDateString'";
    }

    $query = "SELECT `id`, `user_id`, `amount`, `type`, `status`, `typeimage`, `order_id`, `created_at` FROM withdraw_histories";

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY withdraw_histories.id DESC";

    $payin = DB::select($query);

    if ($payin) {
        $response = [
            'message' => 'Successfully',
            'status' => 200,
            'data' => $payin
        ];
        return response()->json($response, 200);
    } else {
        return response()->json(['message' => 'No record found', 'status' => 200, 'data' => []], 400);
    }
}


////notification ////

 public function notification()
       {
       

       $notification = DB::select("SELECT `name`,`disc` FROM `notifications` WHERE `status`=1
");



        if ($notification) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $notification
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }

      public function Privacy_Policy()
       {
      
       $privacyPolicy = Setting::where('id', 1)
          ->where('status', 1)
          ->select('name', 'description')
          ->first();



        if ($privacyPolicy) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $privacyPolicy
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }

	/// About us Api ///
	
      public function about_us(Request $request)
      {
		  $validator = Validator::make($request->all(), [
        'type' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $type = $request->type;
 
		  
		  
        $about_us = DB::select("SELECT `name`,`description` FROM `settings` WHERE `type`=$type;
");

        if ($about_us) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $about_us
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found', 'status' => 400,
                'data' => []], 400);
        }
    }
	
	////Customer Service /////
	  public function customer_service()
      {
        $customer_service = DB::select("SELECT `name`, `Image`, `link`
FROM `customer_services`
WHERE `status` = 1");


        if ($customer_service) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $customer_service
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found', 'status' => 400,
                'data' => []], 400);
        }
    }
	
	
	/// Contact us Api ///
	
      public function contact_us()
      {
        $contact = Setting::where('id', 4)
             ->where('status', 1)
             ->select('name', 'description', 'link')
             ->first();


        if ($contact) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $contact
            ];

            return response()->json($response);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
	
    /// Support Api ///
    	
          public function support()
          {
            $support = Setting::where('id', 5)
                      ->where('status', 1)
                      ->select('name', 'link')
                      ->first();
    
    
            if ($support) {
                $response = [
                    'message' => 'Successfully',
                    'status' => 200,
                    'data' => $support
                ];
    
                return response()->json($response);
            } else {
                return response()->json(['message' => 'No record found','status' => 400,
                    'data' => []], 400);
            }
        }
    	
    	// profile Update Api //
    public function update_profile(Request $request)
    {
         $validator = Validator::make($request->all(), [
        'id' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }
        
        $id = $request->id;
        
        $value = User::findOrFail($id);
        $status=$value->status;
        
        	if($status == 1)
        {
        if (!empty($request->username)) {
            $value->username = $request->username;
        }
        
        if (!empty($request->userimage) && $request->userimage != "null") {
            $value->userimage = $request->userimage;
        }
    
        // Save the changes
        $value->save();
    
        $response = [
            'status' => 200,
            'message' => "Successfully updated"
        ];
    
        return response()->json($response, 200);
        }else{
             $response['message'] = "User block by admin..!";
                    $response['status'] = "401";
                    return response()->json($response,401);
        }
    }
    
    
    ///// main wallet transfer //
    
    public function main_wallet_transfer(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id'
        ]);
        
        $id = $request->id;
        
        $user = User::findOrFail($id);
        $status = $user->status;
        $main_wallet = $user->wallet;
        $thirdpartywallet = $user->third_party_wallet;
        $add_main_wallet = $main_wallet + $thirdpartywallet;
        
        if ($status == 1) {
            $user->wallet = $add_main_wallet;
            $user->third_party_wallet = 0;
            $user->save();
    
            $response = [
                'status' => 200,
                'message' => "Wallet transfer Successfully ....!"
            ];
    
            return response()->json($response, 200);
        } else {
            $response = [
                'status' => 401,
                'message' => "User blocked by admin..!"
            ];
            return response()->json($response, 401);
        }
    }
    
    public function total_bet_details(Request $request)
{
    // Validate incoming data
    $validator = Validator::make($request->all(), [
        'userid' => 'required|exists:users,id',
        'type' => 'required|in:1,2,3,4'
		
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 200);
    }

    $userid = $request->userid;
    $type = $request->type;
	
	

    // Prepare the SQL query based on the 'type' parameter
    switch ($type) {
        case 1:
            // For today
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) = CURDATE()", [$userid]);
            break;

        case 2:
            // For yesterday
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) = CURDATE() - INTERVAL 1 DAY", [$userid]);
            break;

        case 3:
            // For the past week
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) >= CURDATE() - INTERVAL 1 WEEK", [$userid]);
            break;

        case 4:
            // For the past month
            $betDetails = DB::select("SELECT 
                                        COALESCE(SUM(`amount`), 0) AS total_bet_amount, 
                                        COALESCE(COUNT(`id`), 0) AS total_bet_count, 
                                        COALESCE(SUM(`win_amount`), 0) AS total_win_amount 
                                      FROM `bets` 
                                      WHERE `userid` = ? AND DATE(`created_at`) >= CURDATE() - INTERVAL 1 MONTH", [$userid]);
            break;

        default:
            return response()->json([
                'status' => 400,
                'message' => 'Invalid type provided'
            ], 200);
    }
	
	
	$grand_total=$betDetails[0]->total_bet_amount;

    // If no bets found, send response with 0 values
    if (empty($betDetails)) {
        return response()->json([
            'status' => 200,
            'message' => 'No bets found',
            'lottery_data' => [
                'total_bet_amount' => 0,
                'total_bet_count' => 0,
                'total_win_amount' => 0
            ]
        ], 200);
    }

    // Return the bet details
    return response()->json([
        'status' => 200,
        'message' => 'Bet details fetched successfully',
		'grand_total' => $grand_total,
        'lottery_data' => $betDetails[0] // Assuming only one record is returned
		
    ], 200);
}



    
    ///// Change Password ////
    
    
    public function changePassword(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'userid' => 'required',
                'old_password' => 'required',
                'new_password' => 'required',
                'confirm_password' => 'required|same:new_password',
            ]);
    
            $validator->stopOnFirstFailure();
    	   
        if($validator->fails()){
    		
            return response()->json([
                'status' => 400,
                'msg' => $validator->errors()->first()
            ],400);
        }
            $userid = $request->input('userid');
            $oldPassword = $request->input('old_password');
            $newPassword = $request->input('new_password');
    
            $user = User::find($userid);
    
            if (!$user) {
                return response()->json([
                    'msg' => 'User not found',
                    'status' => 404
                ], 404);
            }
    
            if ($oldPassword != $user->password) {
                return response()->json([
                    'msg' => 'Incorrect old password',
                    'status' => 400
                ], 400);
            }
    
            $user->password = $newPassword;
            $user->save();
    
            return response()->json([
                'msg' => 'Password changed successfully!',
                'status' => 200
            ], 200);
        
    }
    
    // 	Slider Image Policy //
	
  public function slider_image_view()
    {
       

       $slider = DB::select("SELECT sliders.title as name,sliders.image as image,sliders.activity_image as activity_image FROM `sliders` WHERE `status`=1");
           
  //dd($slider);
        if ($slider) {
            $response = [
                'message' => 'Successfully',
                'status' => 200,
                'data' => $slider
            ];

            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'No record found','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function attendance_List(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $list = DB::select("SELECT COALESCE(COUNT(at_claim.`userid`),0) AS attendances_consecutively , COALESCE(SUM(attendances.attendance_bonus),0) AS accumulated FROM `at_claim` LEFT JOIN attendances ON at_claim.attendance_id =attendances.id WHERE at_claim.userid=$userid");

    $day = $list[0]->attendances_consecutively;
    $bonus_amt = $list[0]->accumulated;


        $attendanceList = DB::select("
   SELECT a.`id` AS `id`, a.`accumulated_amount` as accumulated_amount ,a.`attendance_bonus` as attendance_bonus, COALESCE(c.`status`, '1') AS `status`, COALESCE(a.`created_at`, 'Not Found') AS `created_at` FROM `attendances` a LEFT JOIN `at_claim` c ON a.`id` = c.`attendance_id` AND c.`userid` =$userid  ORDER BY a.`id` ASC LIMIT 7
");
  

        if (!empty($attendanceList)) {
            $response = [
                'message' => 'Attendance List',
                'status' => 200,
                'attendances_consecutively' => $day,
                'accumulated' =>$bonus_amt,
                'data' => $attendanceList,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    public function attendance_history(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $list1 = DB::select("SELECT at_claim.id AS id,attendances.attendance_bonus AS attendance_bonus,at_claim.created_at FROM attendances LEFT JOIN at_claim ON at_claim.attendance_id=attendances.id WHERE at_claim.userid=$userid");

    
  

        if (!empty($list1)) {
            $response = [
                'message' => 'Attendance History',
                'status' => 200,
                'data' => $list1,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    //// Attendance Claim ////
    	public function attendance_claim(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
    
        $results = DB::select("SELECT a.`id` AS `id`, a.`accumulated_amount` AS accumulated_amount, a.`attendance_bonus` AS attendance_bonus, COALESCE(c.`status`, '1') AS `status`, COALESCE(a.`created_at`, 'Not Found') AS `created_at`, u.`wallet` FROM `attendances` a LEFT JOIN `at_claim` c ON a.`id` = c.`attendance_id` AND c.`userid` = $userid JOIN `users` u ON u.id = $userid WHERE COALESCE(c.`status`, '1') = '1' ORDER BY a.`id` ASC LIMIT 7");
    //dd($results);
        if (count($results) > 0) {
            $bonus = $results[0]->attendance_bonus;
            $id = $results[0]->id;
            $accumulated_amount =$results[0]->accumulated_amount;
            $wallet = $results[0]->wallet;
    if($wallet >= $accumulated_amount){
            $count = DB::select("SELECT COALESCE(COUNT(userid), 0) AS userid FROM `at_claim` WHERE userid = $userid AND DATE(created_at) = CURDATE()");
		//dd($count);
        
       // dd($count);
            $datetime = now();
            if ($count[0]->userid == 0) {
				//dd("hii");
                DB::table('at_claim')->insert([      
                    'userid' => $userid,
                    'attendance_id' => $id,   
                    'status' => '0',
                    'created_at' => $datetime,
                    'updated_at' => $datetime    
                ]);
    
                // Assuming you have `$datetime` defined somewhere
                // DB::table('users')->where('id', $userid)->increment('wallet', $bonus);
             DB::table('users')
    ->where('id', $userid)
    ->increment('wallet', $bonus);  // Increments wallet by $bonus

DB::table('users')
    ->where('id', $userid)
    ->increment('recharge', $bonus);  // Increments recharge by $bonus



                DB::table('wallet_history')->insert([
                    'userid' => $userid,
                    'amount' => $bonus,
                    'subtypeid' => 14,
                    'created_at' => $datetime,
                    'updated_at' => $datetime
                ]);
    
                $response = [
                    'message' => 'Today Claimed Successfully ...!',
                    'status' => 200,
                ];
                return response()->json($response, 200);
            } else {
                return response()->json(['message' => 'Today You Have Already Claimed', 'status' => 400], 400); 
            }
    }else{
      return response()->json(['message' => 'You can not claim due to insufficient Balance...!', 'status' => 400], 400);  
    }
            
        } else {
            return response()->json(['message' => 'User Not Found!', 'status' => 400], 400);
        }
    }
  
      //// activity rewards /////
      
//       public function activity_rewards(Request $request)
// {
//     date_default_timezone_set('Asia/Kolkata');
//     $datetime = date('Y-m-d H:i:s');
//     $date = now()->format('Y-m-d');

//     $validator = Validator::make($request->all(), [
//         'userid' => 'required|numeric'
//     ]);

//     $validator->stopOnFirstFailure();

//     if ($validator->fails()) {
//         $response = [
//             'status' => 400,
//             'message' => $validator->errors()->first()
//         ]; 
//         return response()->json($response, 400);
//     }

//     $userid = $request->userid;  

//     // Calculate total bet amount for the user on the given date
//     $bet_amount = DB::table('bets')
//         ->where('userid', $userid)
//         ->whereDate('created_at', '=', $date)
//         ->sum('amount');

//     // Retrieve activity rewards for the user
//     $invite_bonus = DB::select("
//         SELECT 
//             a.id AS activity_id,
//             a.amount,
//             a.range_amount,
//             a.name,
//             COALESCE(c.status, '1') AS status,
//             COALESCE(a.created_at, 'Not Found') AS created_at
//         FROM 
//             activity_rewards a
//         LEFT JOIN 
//             activity_rewards_claim c 
//         ON 
//             a.id = c.acyivity_reward_id 
//         AND 
//             c.userid = ?
//         ORDER BY 
//             a.id ASC
//     ", [$userid]);

//     // Check if the bet amount is greater than or equal to the range amount and update status
//     if (!empty($invite_bonus)) {
//         foreach ($invite_bonus as $bonus) {
//             if ($bet_amount >= $bonus->range_amount) {
//                 // If the bet amount is greater than or equal to the range amount, set status to 2
//                 $bonus->status = 2;
//             }
//         }

//         $response = [
//             'message' => 'Activity rewards list',
//             'status' => 200,
//             'bet_amount' => $bet_amount,
//             'data' => $invite_bonus
//         ];

//         return response()->json($response);
//     } else {
//         return response()->json(['message' => 'Not found..!','status' => 400, 'data' => []], 400);
//     }
// }

public function activity_rewards(Request $request)
{
    date_default_timezone_set('Asia/Kolkata');
    $date = now()->format('Y-m-d');

    $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first()
        ], 400);
    }

    $userid = $request->userid;  

    // Calculate total bet amount for the user on the given date
    $bet_amount = DB::table('bets')
        ->where('userid', $userid)
        ->whereDate('created_at', '=', $date)
        ->sum('amount');

    // Retrieve activity rewards for the user
    $invite_bonus = DB::select("
        SELECT 
            a.id AS activity_id,
            a.amount,
            a.range_amount,
            a.name,
            COALESCE(c.status, '1') AS status,
            COALESCE(a.created_at, 'Not Found') AS created_at
        FROM 
            activity_rewards a
        LEFT JOIN 
            activity_rewards_claim c 
        ON 
            a.id = c.acyivity_reward_id 
        AND 
            c.userid = ?
        ORDER BY 
            a.id ASC
    ", [$userid]);

    if (!empty($invite_bonus)) {
        foreach ($invite_bonus as $bonus) {
            if ($bet_amount >= $bonus->range_amount) {
                // Check if already claimed but still status is 0
                $claim = DB::table('activity_rewards_claim')
                    ->where('userid', $userid)
                    ->where('acyivity_reward_id', $bonus->activity_id)
                    ->first();

                if ($claim && $claim->status == 0) {
                    $bonus->status = 0; // already eligible and pending claim
                } else {
                    $bonus->status = 2; // eligible to claim
                }
            }
        }

        return response()->json([
            'message' => 'Activity rewards list',
            'status' => 200,
            'bet_amount' => $bet_amount,
            'data' => $invite_bonus
        ]);
    } else {
        return response()->json([
            'message' => 'Not found..!',
            'status' => 400,
            'data' => []
        ], 400);
    }
}


    
   
    public function activity_rewards_old(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        $datetime = date('Y-m-d H:i:s');
        //  $date = date('Y-m-d');
        //  date_default_timezone_set('Asia/Kolkata');
        $date = now()->format('Y-m-d');
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric'
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
       // $userid = $request->input('userid');
      $bet_amount = DB::table('bets')
    ->where('userid', $userid)
    ->whereDate('created_at', '=', $date)
    ->sum('amount');

$invite_bonus = DB::select("
        SELECT 
            a.id AS activity_id,
            a.amount,
            a.range_amount,
            a.name,
           
            COALESCE(c.status, '1') AS status,
            COALESCE(a.created_at, 'Not Found') AS created_at
        FROM 
            activity_rewards a
        LEFT JOIN 
            activity_rewards_claim c 
        ON 
            a.id = c.acyivity_reward_id 
        AND 
            c.userid = ?
        ORDER BY 
            a.id ASC
    ", [$userid]);
   
    

    
        if (!empty($invite_bonus)) {
            $response = [
                'message' => 'activity rewards List',
                'status' => 200,
                'bet_amount'=>$bet_amount,
                'data' => $invite_bonus
                
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    
    ///// activity_rewards history ////
    
     public function activity_rewards_history(Request $request)
    {
           $validator = Validator::make($request->all(), [
         'userid' => 'required|numeric',
         'subtypeid'=>'required',
         
    ]);

	
	$validator->stopOnFirstFailure();
	
    if($validator->fails()){
		
		        		     $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
     $userid = $request->userid;  
     $subtypeid = $request->subtypeid;  
       // $userid = $request->input('userid');

       $act_reward_hist=DB::select("SELECT wallet_history.*,subtype.name as name FROM `wallet_history` LEFT JOIN subtype ON wallet_history.subtypeid=subtype.id WHERE wallet_history.userid=$userid && wallet_history.subtypeid=$subtypeid");
       
  

        if (!empty($act_reward_hist)) {
            $response = [
                'message' => 'activity rewards List',
                'status' => 200,
                'data' => $act_reward_hist,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
        public function activity_rewards_claim(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric',
            'amount' => 'required',
            'activity_id'=>'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
        $amount = $request->amount;
        $activity_id=$request->activity_id;
        $bonusClaim = DB::table('activity_rewards_claim')
                    ->where('userid', $userid)
                    ->where('acyivity_reward_id', $activity_id)
                    ->get();
                    // dd($bonusClaim);
                    
    if($bonusClaim->isEmpty()){
    $user = DB::table('users')->where('id', $userid)->first();
    if (!empty($user)) {
       $usser= DB::table('users')->where('id', $userid)->update([
            'wallet' => $user->wallet + $amount, // Add amount to wallet
        ]);
    }else{
     return response()->json([
    				'message' => 'user not found ..!',
    				'status' => 400,
                    ], 400);
     }
     if (!empty($usser)) {
        // Insert into wallet_histories
        $bonuss=DB::table('wallet_history')->insert([
            'userid'     => $userid,
            'amount'      => $amount,
            'description' => 'Invitation Bonus',
            'subtypeid'     => 11, // Define type_id as 1 for bonus claim
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        
         $bonuss=DB::table('activity_rewards_claim')->insert([
            'userid'     => $userid,
            'acyivity_reward_id' => $activity_id,
            'status' => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
     }else{
     
     }
         if (!empty($bonuss)) {
                $response = [
                    'message' => 'invitation bonus claimed successfully!',
                    'status' => 200,
                ];
                return response()->json($response,200);
            } else {
                return response()->json([
    				'message' => 'Bonus not claimed ..!',
    				'status' => 400,
                    ], 400);
            }
            
           } else{
             return response()->json([
    				'message' => 'Already claimed ..!',
    				'status' => 400,
                    ], 400);  
           }
    	}
        
        /// invitation Bonus List/////
        
   	public function invitation_bonus_list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
       $total_refer = DB::table('users')->where('referral_user_id', $userid)->count();
      
    // Fetch all users referred by the user with the given $userid
    $refer_users = DB::table('users')->where('referral_user_id', $userid)->get();
    
    $count_users = 0; // Initialize the count of users whose total deposit > 500
    
    // Loop through each referred user to calculate the total deposit sum
    foreach ($refer_users as $refer_user) {
        $user_id = $refer_user->id;
        
        // Calculate the total deposit amount for this user from the 'payins' table
        $deposit_count = DB::select("SELECT SUM(`cash`) as total_amount FROM `payins` WHERE `user_id` = :user_id", ['user_id' => $user_id]);
        
        // Get the total deposit amount for the user (or 0 if null or no rows)
        $total_deposit = $deposit_count[0]->total_amount ?? 0;
    
        // Only count users whose total deposit is greater than 500
        if ($total_deposit >= 500) {
            $count_users++; // Increment the count of users whose total deposit > 500
        }
    }
    
    
    $invite_bonus = DB::select("
        SELECT 
            a.id AS bonus_id,
            a.amount,
            a.claim_amount,
            a.no_of_user,
            CASE 
                WHEN c.userid = ? AND c.invite_id = a.id AND a.no_of_user <= ? THEN 0
                WHEN a.no_of_user <= ? THEN 2 
                ELSE COALESCE(c.status, '1') 
            END AS status,
            COALESCE(a.created_at, 'Not Found') AS created_at
        FROM 
            invite_bonus a
        LEFT JOIN 
            invite_bonus_claim c 
        ON 
            a.id = c.invite_id 
        AND 
            c.userid = ?
        ORDER BY 
            a.id ASC
    ", [$userid, $count_users, $count_users, $userid]);
    
    
    
        if (!empty($invite_bonus)) {
            $response = [
                'message' => 'invitation_bonus_list',
                'status' => 200,
                'data' => collect($invite_bonus)->map(function ($bonus) use ($total_refer, $count_users) {
                    return [
                        'bonus_id' => $bonus->bonus_id,
                        'amount' => $bonus->amount,
                        'claim_amount' => $bonus->claim_amount,
                        'no_of_user' => $bonus->no_of_user,
                        'status' => $bonus->status,
                        'created_at' => $bonus->created_at,
                        'no_of_invitees' => $total_refer,
                        'refer_invitees' => $count_users
                    ];
                })
            ];
            return response()->json($response);
        } else {
            return response()->json([
                'message' => 'Not found..!',
                'status' => 400,
                'data' => []
            ], 400);
        }
    }
    
    
    public function invitation_bonus_list_old(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
       $total_refer = DB::table('users')->where('referral_user_id', $userid)->count();
      
    // Fetch all users referred by the user with the given $userid
    $refer_users = DB::table('users')->where('referral_user_id', $userid)->get();
    
    $count_users = 0; // Initialize the count of users whose total deposit > 500
    
    // Loop through each referred user to calculate the total deposit sum
    foreach ($refer_users as $refer_user) {
        $user_id = $refer_user->id;
        
        // Calculate the total deposit amount for this user from the 'payins' table
        $deposit_count = DB::select("SELECT SUM(`cash`) as total_amount FROM `payins` WHERE `user_id` = :user_id", ['user_id' => $user_id]);
        
        // Get the total deposit amount for the user (or 0 if null or no rows)
        $total_deposit = $deposit_count[0]->total_amount ?? 0;
    
        // Only count users whose total deposit is greater than 500
        if ($total_deposit >= 500) {
            $count_users++; // Increment the count of users whose total deposit > 500
        }
    }
    
    
    $invite_bonus = DB::select("
        SELECT 
            a.id AS bonus_id,
            a.amount,
            a.claim_amount,
            a.no_of_user,
            CASE 
                WHEN c.userid = ? AND c.invite_id = a.id AND a.no_of_user <= ? THEN 0
                WHEN a.no_of_user <= ? THEN 2 
                ELSE COALESCE(c.status, '1') 
            END AS status,
            COALESCE(a.created_at, 'Not Found') AS created_at
        FROM 
            invite_bonus a
        LEFT JOIN 
            invite_bonus_claim c 
        ON 
            a.id = c.invite_id 
        AND 
            c.userid = ?
        ORDER BY 
            a.id ASC
    ", [$userid, $count_users, $count_users, $userid]);
    
    
    
        if (!empty($invite_bonus)) {
            $response = [
                'message' => 'invitation_bonus_list',
                'status' => 200,
                'data' => collect($invite_bonus)->map(function ($bonus) use ($total_refer, $count_users) {
                    return [
                        'bonus_id' => $bonus->bonus_id,
                        'amount' => $bonus->amount,
                        'claim_amount' => $bonus->claim_amount,
                        'no_of_user' => $bonus->no_of_user,
                        'status' => $bonus->status,
                        'created_at' => $bonus->created_at,
                        'no_of_invitees' => $total_refer,
                        'refer_invitees' => $count_users
                    ];
                })
            ];
            return response()->json($response);
        } else {
            return response()->json([
                'message' => 'Not found..!',
                'status' => 400,
                'data' => []
            ], 400);
        }
    }
    
    ///// Invitation_reward_rule ////
    
     public function Invitation_reward_rule(Request $request)
    {
          

       $rule=DB::select("SELECT * FROM `invite_bonus`");
       
  

        if (!empty($rule)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $rule,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
	
    //// Invitation record ////
    
    public function Invitation_records(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'userid' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->userid;
 

       $records=DB::select("SELECT `username`,`u_id`,`first_recharge_amount`,`created_at` FROM `users` WHERE `referral_user_id`=$userid");
       
  

        if (!empty($records)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $records,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
    //// Extra First Deposit bonus history ///
    
  
     public function extra_first_payin(Request $request)
    {
       
         $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'cash' => 'required',
            'type' => 'required',
        ]);
        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];

            return response()->json($response);
        }

        
        
	$cash = $request->cash;
    // $extra_amt = $request->extra_cash;
     $type = $request->type;
    $userid = $request->user_id;
	   //	$total_amt=$cash+$extra_amt+$bonus;
		 
               $date = date('YmdHis');
        $rand = rand(11111, 99999);
        $orderid = $date . $rand;

        $check_id = DB::table('users')->where('id',$userid)->first();
        if($type == 1){
        if ($check_id) {
            $redirect_url = env('APP_URL')."api/checkPayment?order_id=$orderid";
            //dd($redirect_url);
            $insert_payin = DB::table('payins')->insert([
                'user_id' => $request->user_id,
                'cash' => $request->cash,
                'type' => $request->type,
                'order_id' => $orderid,
                'redirect_url' => $redirect_url,
                'status' => 1 // Assuming initial status is 0
            ]);
         // dd($redirect_url);
            if (!$insert_payin) {
                return response()->json(['status' => 400, 'message' => 'Failed to store record in payin history!']);
            }
 
            $postParameter = [
                'merchantid' => "INDIANPAY00INDIANPAY0033",
                'orderid' => $orderid,
                'amount' => $request->cash,
                'name' => $check_id->u_id,
                'email' => "abc@gmail.com",
                'mobile' => $check_id->mobile,
                'remark' => 'payIn',
                'type'=>$request->cash,
                'redirect_url' => env('APP_URL')."api/checkPayment?order_id=$orderid"
               // 'redirect_url' => config('app.base_url') ."/api/checkPayment?order_id=$orderid"
            ];


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://indianpay.co.in/admin/paynow',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0, 
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postParameter),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Cookie: ci_session=1ef91dbbd8079592f9061d5df3107fd55bd7fb83'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
             
			echo $response;
		//	dd($response);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Internal error!'
            ]);
        }
            
        }else{
           return response()->json([
                'status' => 400,
                'message' => 'USDT is Not Supported ....!'
            ]); 
        }
    }

    public function checkPayment1(Request $request)
    {
       // dd($request);
        $orderid = $request->input('order_id');
	//dd($orderid);
     //bonus = gift_cash
        if ($orderid == "") {
            return response()->json(['status' => 400, 'message' => 'Order Id is required']);
        } else {
            $match_order = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->first();
//dd($match_order);
            if ($match_order) {
                $uid = $match_order->user_id;
            
                $cash = $match_order->cash;
                $type = $match_order->type;
               
                $orderid = $match_order->order_id;
                
                $datetime=now();
               // dd("UPDATE payins SET status = 2 WHERE order_id = $orderid AND status = 1 AND user_id = $uid");

              $update_payin = DB::table('payins')->where('order_id', $orderid)->where('status', 1)->where('user_id', $uid)->update(['status' => 2]);
    
                if ($update_payin) {
                    
                    // $wallet = $cash + $bonus + $extra_cash;
                    // $bonusToAdd = $bonus;
                    //dd($uid);
    $referid=DB::select("SELECT referral_user_id,first_recharge FROM `users` WHERE id=$uid");
    //dd($referid);
    $first_recharge=$referid[0]->first_recharge;
    $referuserid=$referid[0]->referral_user_id;
   // dd($first_recharge);
if($first_recharge == 0){
    
    $extra=DB::select("SELECT * FROM `extra_first_deposit_bonus` WHERE `first_deposit_ammount`=$cash"); 
    $id=$extra[0]->id;
    $first_deposit_ammount=$extra[0]->first_deposit_ammount;
    $bonus=$extra[0]->bonus;
    
    $amount=$bonus+$first_deposit_ammount;

    DB::INSERT("INSERT INTO `extra_first_deposit_bonus_claim`( `userid`, `extra_fdb_id`, `amount`, `bonus`, `status`, `created_at`, `updated_at`) VALUES ('$uid','$id','$first_deposit_ammount','$bonus','0','$datetime','$datetime')");
   
                    $updateUser =DB::update("UPDATE users 
    SET 
    wallet = wallet + $amount,
    first_recharge = first_recharge + $cash,
    first_recharge_amount = first_recharge_amount + $cash,
    recharge = recharge + $cash,
    total_payin = total_payin + $cash,
    no_of_payin = no_of_payin + 1,
    deposit_balance = deposit_balance + $cash
    WHERE id = $uid;
    ");
    //dd("hiii");
    // dd("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1,yesterday_first_deposit = yesterday_first_deposit + $cash WHERE id=$referuserid");
    //dd($referuserid);
    DB::UPDATE("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1,yesterday_first_deposit = yesterday_first_deposit + $cash WHERE id=$referuserid");
     return redirect()->away(env('APP_URL').'uploads/payment_success.php');
}else{
    
      $updateUser =DB::update("UPDATE users 
    SET 
    wallet = wallet + $cash,
    recharge = recharge + $cash,
    total_payin = total_payin + $cash,
    no_of_payin = no_of_payin + 1,
    deposit_balance = deposit_balance + $cash
    WHERE id = $uid;
    ");
    
    //dd("hello");
     //dd($referuserid);
    DB::select("UPDATE users SET yesterday_payin = yesterday_payin + $cash,yesterday_no_of_payin  = yesterday_no_of_payin + 1 WHERE id=$referuserid");
     return redirect()->away(env('APP_URL').'uploads/payment_success.php');
}

     
    
                    if ($updateUser) {
                        // Redirect to success page
                        //dd("hello");
                        return redirect()->away(env('APP_URL').'uploads/payment_success.php');
                    } else {
                        return response()->json(['status' => 400, 'message' => 'User balance update failed!']);
                    }
                } else {
                    return response()->json(['status' => 400, 'message' => 'Failed to update payment status!']);
                }
            } else {
                return response()->json(['status' => 400, 'message' => 'Order id not found or already processed']);
            }
        }
    }
	
    
    
        public function extra_first_deposit_bonus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
     
    
            
            $rowCount = DB::table('extra_first_deposit_bonus_claim')->where('userid', $userid)->count();
            
            if ($rowCount > 0) {
                $checkDate = DB::select("SELECT extra_first_deposit_bonus.first_deposit_ammount as first_deposit_ammount, extra_first_deposit_bonus.bonus as bonus, extra_first_deposit_bonus.bonus + extra_first_deposit_bonus.bonus as totalamount, COALESCE(extra_first_deposit_bonus_claim.status, 1) as status FROM extra_first_deposit_bonus LEFT JOIN extra_first_deposit_bonus_claim ON extra_first_deposit_bonus.first_deposit_ammount = extra_first_deposit_bonus_claim.amount AND extra_first_deposit_bonus_claim.userid = ? ORDER BY COALESCE(extra_first_deposit_bonus_claim.status, 1) DESC", [$userid]); 
               
                if (!empty($checkDate)) {
                    return response()->json([
                        'msg' => 'Successfully...!',
                        'status' => '200',
                        'data' => $checkDate
                    ]);
                } else {
                    return response()->json([
                        'msg' => 'Internal error...',
                        'status' => '400'
                    ]);
                }
            } else {
               
                $checkDate = DB::table('extra_first_deposit_bonus')->select('first_deposit_ammount', 'bonus', DB::raw('first_deposit_ammount + bonus as totalamount'), 'status','created_at')->get(); 
               
                
                if (!empty($checkDate)) {
                    return response()->json([
                        'msg' => 'Successfully...!',
                        'status' => '200',
                        'data' => $checkDate
                    ]);
                }
            }
        
    }
     
    //// Frontend MLM Data //
    
    // public function frontend_mlm_data(Request $request)
    // {
    //     header("Access-Control-Allow-Origin: *");
        
            
    //   $validator = Validator::make($request->all(), [
    //         'id' => 'required|integer'
    //     ]);
    
    //     $validator->stopOnFirstFailure();
    
    //     if ($validator->fails()) {
    //         $response = [
    //             'status' => 400,
    //             'message' => $validator->errors()->first()
    //         ];
    //         return response()->json($response, 400);
    //     }
    
    
       
    //     date_default_timezone_set('Asia/Kolkata');
    //     $datetime = date('Y-m-d H:i:s');
    
    //     $userId = $request->input('id');
    
    //     $user_bonus = User::select('bonus')->where('id', $userId)->first();
    //     dd($user_bonus);
    //     $bonus = $user_bonus->bonus;
    
    //     $user_data = User::all()->toArray();
    //     $mlm_level_data = DB::table('mlm_levels')
    //         ->select('mlm_levels.*')
    //         ->get()
    //         ->toArray();
    //         //dd($mlm_level_data);
    //     $level_data = count($mlm_level_data);
    //     $alldata = array();
    //     for ($i = 0; $i < $level_data; $i++) {
    //         $name = $mlm_level_data[$i]->name;
    //         $commission = $mlm_level_data[$i]->commission;
    //         $usermlm = array();
    //         if ($i == 0) {
    //             $usermlm[] = $userId;
    //         } else {
    //             $data = $mlm_level_data[$i - 1]->name;
    //             foreach ($alldata[$data] as $itemss) {
    //                 $usermlm[] = $itemss['user_id'];
    //             }
    //         }
    //         $level = array();
    //         for ($w = 0; $w < count($usermlm); $w++) {
    //             foreach ($user_data as $item) {
    //                 if ($item['referral_user_id'] === $usermlm[$w]) {
    //                     $todays = $item['today_turnover'] * $commission * 0.01 ;
    //                     $level[] = array(
    //                         "user_id" => $item['id'],
    //                         "username" => $item['username'],
    //                         "turnover" => $item['today_turnover'],
    //                         "commission" => number_format((float)$todays, 2, '.', '')
    
    //                     );
    //                 }
    //             }
    //         }
    //         $alldata[$name] = $level;
    //     }
    //     $mlm_level_data = DB::table('mlm_levels')->get()->toArray();
    //     $level_data = count($mlm_level_data);
    //     $alldatacoount = array();
    //     $totalcommission = 0;
    //     $totaluser = 0;
    //     $datalevelcome = [];
    //     for ($i = 0; $i < $level_data; $i++) {
    //         $co = 0;
    //         $name = $mlm_level_data[$i]->name;
    //         $levelcom = 0;
    //         foreach ($alldata[$name] as $obj) {
    //             $totalcommission = $totalcommission + $obj['commission'];
    //             $totaluser = $totaluser + 1;
    //             $levelcom = $levelcom + $obj['commission'];
    //             $co = $co + 1;
    //         }
    //         $datalevelcome[] = array(
    //             'count' => $co,
    //             'name' => $name,
    //             'commission' => number_format($levelcom, 2, '.', ''),
    //         );
    //     }
    
       
    //     return response()->json([
    //         'totalcommission' => number_format($totalcommission, 2, '.', ''),
    //         'bonus' => number_format($bonus, 2, '.', ''),
    //         'totaluser' => "$totaluser",
    //         'levelwisecommission' => $datalevelcome,
    //         'user_id' => $userId,
    //         'userdata' => $alldata
    //     ]);
    // }
    
    
    ////// MLM Level/////
    
    public function  level_getuserbyrefid(Request $request)
    {
    
        
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        date_default_timezone_set('Asia/Kolkata');
        $datetime = date('Y-m-d H:i:s');
    
        $userId = $request->input('id');
        $refer_code = User::where('id', $userId)->value('referral_code');
        $user_data = User::select('id','username', 'today_turnover', 'total_payin', 'no_of_payin', 'referral_user_id', 'yesterday_payin','yesterday_register','referral_code','yesterday_first_deposit','yesterday_no_of_payin','deposit_balance','yesterday_total_commission','u_id','totalbet','first_recharge','turnover')->get()->toArray();
        $mlm_level_data = DB::table('mlm_levels')->get()->toArray();
    
        $alldata = [];
        $lastlevelname = 'Tier 6';
        foreach ($mlm_level_data as $mlm_level) {
            $name = $mlm_level->name;
            $commission = $mlm_level->commission;
            $usermlm = [];
    
            if ($name == 'Tier 1') {
                $usermlm[] = $userId;
            } else {
                $data = $mlm_level_data[array_search($mlm_level, $mlm_level_data) - 1]->name;
                foreach ($alldata[$data] as $itemss) {
                    $usermlm[] = $itemss['user_id'];
                }
            }
    
            $filtered_users = array_filter($user_data, function($item) use ($usermlm) {
                return in_array($item['referral_user_id'], $usermlm);
            });
    
            $level = [];
            foreach ($filtered_users as $item) {
                $todays = $item['today_turnover'] * $commission * 0.01 ;
                $level[] = [
                    "user_id" => $item['id'],
                     "u_id" => $item['u_id'],
                     'totalbet'=> $item['totalbet'],
                    "username" => $item['username'],
                    "first_recharge"=>$item['first_recharge'],
                     "deposit_amount" => $item['deposit_balance'],
                    "turnover" => $item['turnover'],
                    'today_turnover'=>$item['today_turnover'],
                    "commission" => number_format((float)$todays, 2, '.', ''),
                    'total_payin'=> $item['total_payin'],
                    'no_of_payin'=>$item['no_of_payin'],
                    'yesterday_payin'=>$item['yesterday_payin'],
                    'yesterday_register'=>$item['yesterday_register'],
                    'yesterday_no_of_payin'=>$item['yesterday_no_of_payin'],
                    'yesterday_first_deposit'=>$item['yesterday_first_deposit']
                ];
            }
    
            $alldata[$name] = $level;
            $lastlevelname = $name;
        }
    
        $totalcommission = 0;
        $totaluser = 0;
        $datalevelcome = [];
        $indirectTeam = 0;
        $numofpayindirect = 0;
        $numofpayteam = 0;
        $payinAmountDirect = 0;
        $payinAmountTeam = 0;
        $noUserDirect = 0;
        $noUserTeam = 0;
        $noOfFristPayinDirect = 0;
        $noOfFristPayinTeam = 0;
        
        $yesterday_total_commission = 0;
        
        $yesterday_payin_direct = 0;
        $yesterday_register_direct = 0;
        $yesterday_no_of_payin_direct = 0;
        $yesterday_first_deposit_direct = 0;
    
        $yesterday_payin_team = 0;
        $yesterday_register_team = 0;
        $yesterday_no_of_payin_team = 0;
        $yesterday_first_deposit_team = 0;
    
       
            $deposit_number_all=0;
            $deposit_amount_all=0;
            $first_recharge_all=0;
            $no_of_firstrechage_all=0;
            $total_bet_all=0;
            $total_bet_amount_all=0;   
       
       
    
        foreach ($mlm_level_data as $mlm_level) {
            $name = $mlm_level->name;
            $levelcom = 0;
            $deposit_number=0;
            $deposit_amount=0;
            $first_recharge=0;
            $no_of_firstrechage=0;
            $total_bet=0;
            $total_bet_amount=0;
    
            foreach ($alldata[$name] as $obj) {
                $totalcommission += $obj['commission'];
                $deposit_number_all+=$obj['total_payin'];
            $deposit_amount_all+=$obj['no_of_payin'];
            $first_recharge_all+=$obj['first_recharge'];
            $no_of_firstrechage_all+=$no_of_firstrechage;
            $total_bet_all+=$total_bet;
            $total_bet_amount_all+=$total_bet_amount; 
            
            
            
                $totaluser++;
                $levelcom += $obj['commission'];
                if ($name == 'Tier 1') {
                    $payinAmountDirect += $obj['total_payin'];
                    $noUserDirect++;
                    if ($obj['yesterday_payin'] != '0') {
                         $numofpayindirect++;
                        $noOfFristPayinDirect++;
                    }
                    if ($obj['no_of_payin'] != '0') {
                      //  $numofpayindirect++;
                    }
                    
                    $yesterday_payin_direct += $obj['yesterday_payin'];
                    $yesterday_register_direct = $obj['yesterday_register'];
                   // $yesterday_no_of_payin_direct += $obj['yesterday_no_of_payin'];
                    $yesterday_first_deposit_direct += $obj['yesterday_first_deposit'];
    
                } else {
                    $payinAmountTeam += $obj['total_payin'];
                    $noUserTeam++;
                    $indirectTeam++;
                    if ($obj['total_payin'] != '0') {
                        $noOfFristPayinTeam++;
                    }
                    if ($obj['no_of_payin'] != '0') {
                        $numofpayteam++;
                    }
                    if ($name != $lastlevelname) {
                        if($obj['first_recharge'] > 0){
                            
                       $first_recharge += $obj['first_recharge'];
    
                           $no_of_firstrechage++;
                        }
                        $total_bet_amount += $obj['today_turnover']+$obj['turnover'];
                        $total_bet += $obj['totalbet'];
                        
                        
                        
                        $deposit_number += $obj['no_of_payin'];
                        $deposit_amount +=$obj['total_payin'];
                        $yesterday_payin_team += $obj['yesterday_payin'];
                        $yesterday_register_team += $obj['yesterday_register'];
                        $yesterday_no_of_payin_team += $obj['yesterday_no_of_payin'];
                        $yesterday_first_deposit_team += $obj['yesterday_first_deposit'];
                    }
                }
            }
    
            $datalevelcome[] = [
                'count' => count($alldata[$name]),
                'name' => $name,
                'commission' => number_format($levelcom, 2, '.', ''),
                'total_payin'=>$deposit_amount,
                'no_of_payin' =>$deposit_number,
                'first_recharge' =>$first_recharge,
                'no_of_people'=>$no_of_firstrechage,
                'totalbet'=>$total_bet,
                'total_bet_amount'=>$total_bet_amount
                
            ];
          
        }
      $datalevelcome[]=[
            'count' => $totaluser,
            'name' => "all",
            'commission' => number_format($totalcommission, 2, '.', ''),
            'total_payin'=>$deposit_number_all,
            'no_of_payin' =>$deposit_amount_all,
            'first_recharge' =>$first_recharge_all,
            'no_of_people'=>$no_of_firstrechage_all,
            'totalbet'=>$total_bet_all,
            'total_bet_amount'=>$total_bet_amount_all
                ];
        return response()->json([
            'direct_user_count' => $yesterday_register_direct ?? 0,
            'numofpayindirect' => $yesterday_no_of_payin_direct ?? 0,
            'noUserDirect' => $yesterday_register_direct ?? 0,
            'noOfFristPayinDirect' => $numofpayindirect ?? 0,
            'payinAmountDirect' => $yesterday_payin_direct ?? 0,
            'indirect_user_count' => $yesterday_register_team ?? 0,
            'numofpayteam' => $yesterday_no_of_payin_team ?? 0,
            'payinAmountTeam' => $yesterday_payin_team ?? 0,
            'noUserTeam' => $yesterday_register_team ?? 0,
            'noOfFristPayinTeam' => $yesterday_first_deposit_team ?? 0,
            'total_payin_direct'=> $payinAmountDirect ?? 0,
            'total_register_direct'=>$noUserDirect ?? 0,
            'total_no_of_payin_direct'=>$numofpayindirect ?? 0,
            'total_first_deposit_direct'=>$noOfFristPayinDirect ?? 0,
            'total_payin_team'=>$payinAmountTeam ?? 0,
            'total_register_team'=>$noUserTeam ?? 0,
            'total_no_of_payin_team'=>$numofpayteam ?? 0,
            'total_first_deposit_team'=>$noOfFristPayinTeam ?? 0,      
            'totaluser' => "$totaluser" ?? 0,
            'totalcommission' => number_format($totalcommission, 2, '.', ''),
            'yesterday_totalcommission' => number_format($yesterday_total_commission, 2, '.', ''),
            'user_refer_code' => $refer_code,
            'levelwisecommission' => $datalevelcome ?? 0,
            'user_id' => $userId ?? 0,
            'userdata' => $alldata ?? 0,
            ///
            // 'all_total_payin'=>$deposit_number_all,
            // 'all_no_of_payin' =>$deposit_amount_all,
            // 'all_first_recharge' =>$first_recharge_all,
            // 'all_no_of_people'=>$no_of_firstrechage_all,
            // 'all_totalbet'=>$total_bet_all,
            // 'all_total_bet_amount'=>$total_bet_amount_all
        ]);
    }
      
    
    // commission details //////
    
     public function commission_details(Request $request)
        {
             $validator = Validator::make($request->all(), [
            'userid' => 'required|integer',
            'subtypeid'=>'required|integer',
            'date'=>'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
             $userid = $request->userid;
             $subtypeid= $request->subtypeid;
             $date=$request->created_at;
    
           $commission=DB::select("SELECT * FROM `wallet_history` WHERE `userid`=$userid && `subtypeid`=$subtypeid &&`created_at` LIKE '%$date%'");
           
          $data=[];
    foreach ($commission as $item){
        
           
           $amount=$item->amount;
           $description=$item->description;
           $description2=$item->description_2;
           $created_at=$item->created_at;
           $updated_at=$item->updated_at;
        }
        
        
         $data[] = [
             'number_of_bettors'=>$description2,
             'bet_amount'=>$description,
             'commission_payout'=>$amount,
             'date'=>$created_at,    
             'settlement_date'=>$updated_at       
             ];
              
    
            if (!empty($data)) {
                $response = [
                    'message' => 'commission_details',
                    'status' => 200,
                    'data' => $data,
                ];
                return response()->json($response);
            } else {
                return response()->json(['message' => 'Not found..!','status' => 400,
                    'data' => []], 400);
            }
        }
        
     //// All Rules ////
    
    public function all_rules(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'type' => 'required|numeric'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $type = $request->type;
 

      $records=DB::select("SELECT name,list FROM `rules` WHERE `type`=$type");
       
 
        if (!empty($records)) {
            $response = [
                'message' => 'rules list',
                'status' => 200,
                'data' =>$records,
            ];
            return response()->json($response,200);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
     public function subordinate_userlist(Request $request)
    {
         
         $validator = Validator::make($request->all(), [
        'id' => 'required|numeric',
        'type' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        $response = [
            'status' => 400,
            'message' => $validator->errors()->first()
        ];
        return response()->json($response, 400);
    }

    $userid = $request->id;
    $type = $request->type;
 
if($type == 1){
       $list=DB::select("SELECT `u_id` AS user_name, `mobile` AS mobile, `created_at` AS datetime 
FROM `users` 
WHERE referral_user_id =$userid  
AND DATE(`created_at`) = CURDATE();
");
}elseif ($type == 2) {
    $list=DB::select("SELECT `u_id` AS user_name, `mobile` AS mobile, `created_at` AS datetime 
FROM `users` 
WHERE referral_user_id = $userid 
AND DATE(`created_at`) = DATE_SUB(CURDATE(), INTERVAL 1 DAY);
");
}else{
    $list=DB::select("SELECT `u_id` AS user_name, `mobile` AS mobile, `created_at` AS datetime 
FROM `users` 
WHERE referral_user_id = $userid 
AND `created_at` BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 MONTH) AND CURDATE();
");
}
  

        if (!empty($list)) {
            $response = [
                'message' => 'Invitation rewards rule',
                'status' => 200,
                'data' => $list,
            ];
            return response()->json($response);
        } else {
            return response()->json(['message' => 'Not found..!','status' => 400,
                'data' => []], 400);
        }
    }
    
       public function commission_distribution()
    {
        $datetime = now();
        $user_data = User::select('id', 'today_turnover', 'referral_user_id', 'u_id', 'first_recharge', 'turnover')
            ->where('first_recharge', '!=', 0)
            ->get()
            ->toArray();
       
        $mlm_level_data = DB::table('mlm_levels')->get()->toArray();
        
        $userIds = [];
        $bonusWalletIncrement = 0;
    
        $inserts = [];
    
        foreach ($user_data as $item) {
            $user_id = $item['id'];
    
            $commission = $this->commission_distribute_mlm($mlm_level_data, $user_data, $user_id);
            $no_of_bet = $commission['no_of_bet'];
            $betamount = $commission['betamount'];
            $commissions = $commission['commission'];
    
            // Collect user IDs for bulk update
            $userIds[] = $user_id;
    
            // Build insert query
            if($commissions>0){
            $inserts[] = [
                'userid' => $user_id,
                'amount' => $commissions,
                'subtypeid'=>23,
                'description' => $betamount,
                'description_2' => $no_of_bet,
                'created_at' => $datetime
            ];
            }
            // Increment bonus wallet
            $bonusWalletIncrement += $commissions;
        }
    //dd($userIds);
        // Bulk update
        if (!empty($userIds)) {
            $userIdsString = implode(',', $userIds);
            
            $updateQuery = "
                UPDATE users 
                SET 
                    turnover = CASE 
                        " . implode(' ', array_map(function ($userId) {
                            return "WHEN $userId THEN turnover + today_turnover";
                        }, $userIds)) . "
                        ELSE today_turnover 
                    END,
                    today_turnover = 0,
                    bonus_wallet = bonus_wallet + $bonusWalletIncrement,
                    yesterday_payin = 0,
                    today_turnover=0,
                    yesterday_no_of_payin = 0,
                    yesterday_first_deposit = 0,
                    yesterday_total_commission = 0,
                    yesterday_register = 0,
                    recharge = recharge + $bonusWalletIncrement 
                WHERE 
                    id IN ($userIdsString)
            ";
            //dd($updateQuery);
    
            // Execute update query
            DB::statement($updateQuery);
        }
    
        // Bulk insert
        if (!empty($inserts)) {
            DB::table('wallet_history')->insert($inserts);
        }
    }
    	
    private function commission_distribute_mlm($mlm_level_data,$user_data,$user_id)
    {
          $all_data = [];
        $last_level_name = 'Tier 6';  
         $total_commission = 0;
        $user_id = $user_id;
        $no_of_bet=0;
        $betamount=0;
        
    
        foreach ($mlm_level_data as $mlm_level) {
            $name = $mlm_level->name;
            $commission = $mlm_level->commission;
            $user_mlm = [];
    
            if ($name == 'Tier 1') {
                $user_mlm[] = $user_id;
             }
            // else {
            //     $data = $mlm_level_data[array_search($mlm_level, $mlm_level_data) - 1]->name;
            //     foreach ($all_data[$data] as $item) {
            //         $user_mlm[] = $item['user_id'];
            //     }
            // }
            
            $index = array_search($mlm_level, $mlm_level_data);
    if ($index !== false && $index > 0) {
        $data = $mlm_level_data[$index - 1]->name;
        foreach ($all_data[$data] as $item) {
            $user_mlm[] = $item['user_id'];
        }
    }
    
            // Filter users based on MLM structure
            $filtered_users = array_filter($user_data, function ($item) use ($user_mlm) {
                return in_array($item['referral_user_id'], $user_mlm);
            });
    
            // Calculate commission for each user at this level
            $level = [];
            foreach ($filtered_users as $item) {
                if($item['today_turnover']){
                    $no_of_bet++;
                    $betamount+=$item['today_turnover'];
                }
                $todays = $item['today_turnover'] * $commission * 0.01;
              
                $level[] = [
                    "user_id" => $item['id'],
                    "turnover" => $item['turnover'],
                    'today_turnover' => $item['today_turnover'],
                    "commission" => number_format((float)$todays, 2, '.', ''),
                ];
            }
    
            // Store commission data for this level
            $all_data[$name] = $level;
            $last_level_name = $name;
        }
    
        foreach ($mlm_level_data as $mlm_level) {
            $name = $mlm_level->name;
            foreach ($all_data[$name] as $obj) {
                $total_commission += $obj['commission'];
              
            }
        }
          $user_id = $user_id;
        $no_of_bet=0;
        $betamount=0;
        $finaldatas=array(
            'id'=>$user_id,
            'no_of_bet'=>$no_of_bet,
           'betamount'=> $betamount,
           'commission'=>$total_commission
            );
        return $finaldatas;
    }
    
    public function betting_rebate(){
        
        $currentDate = date('Y-m-d');
    		 
    		 $a = DB::select("SELECT sum(amount) as betamount, userid FROM bets WHERE created_at like '$currentDate %' AND status= '2' GROUP BY userid;");
    
    		
    		//$a = DB::select("SELECT `today_turnover` FROM `users` WHERE `id`=$userid ");
    		
    		foreach($a as $item){
    		
    		   $betamount = $item->betamount;
    		   $userid = $item->userid;
    			
    			DB::select("UPDATE users SET wallet = wallet + $betamount * 0.01 WHERE id = $userid");
    		$rebate_rate=0.01;
    		  $insert= DB::table('wallet_history')->insert([
            'userid' => $userid,
            'amount' => $betamount*$rebate_rate,
            'description'=>$betamount,
            'description_2'=>$rebate_rate,
            'subtypeid' => 25,
    		'created_at'=> now(),
            'updated_at' => now()
    		
            ]);
    		
    	   }
    		
    	}		
    	
    	
     public function betting_rebate_history(Request $request)
        {
             
             $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric',
            'subtypeid' => 'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
        $subtypeid = $request->subtypeid;
        
        $value=DB::select("SELECT 
        COALESCE(SUM(amount), 0) as total_rebet,
        COALESCE(SUM(description), 0) as total_amount,
        COALESCE(SUM(CASE WHEN DATE(CURDATE()) = CURDATE() THEN amount ELSE 0 END), 0) as today_rebet 
    FROM 
        wallet_history 
    WHERE 
        userid = $userid && subtypeid =$subtypeid");
        
        $records=DB::select("SELECT 
        `amount` as rebate_amount,description_2 as rebate_rate,created_at as datetime,
        COALESCE((SELECT SUM(description) FROM wallet_history WHERE `userid` = $userid AND subtypeid = $subtypeid), 0) as betting_rebate 
    FROM 
        `wallet_history` 
    WHERE 
        `userid` = $userid AND subtypeid = $subtypeid;");
    
    
           
     
            if (!empty($records)) {
                $response = [
                    'message' => 'Betting Rebet List',
                    'status' => 200,
                    'data1' =>$records,
                    'data' =>$value,
                ];
                return response()->json($response,200);
            } else {
                return response()->json(['message' => 'Not found..!','status' => 400,
                    'data' => []], 400);
            }
     
    
        }	
	
	
	 public function versionApkLink(Request $request)
    {
        
            $data = DB::SELECT("SELECT * FROM `versions` WHERE `id`=1"); // Assuming you have a Version model with 'id' field

            if ($data) {
                
                $response = [
                 'msg' => 'Successfully',
                    'status' => 200,
                    'version' => $data[0]->version,
                    'link' => $data[0]->link
            ];
            return response()->json($response,200);
                
            } else {
                // If no data is found, set an appropriate response
                return response()->json([
                    'msg' => 'No record found',
                    'status' => 400
                ], 400);
            }
        
    }
	
    public function sendSMS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
        $mobile = $request->mobile;
    
        $apikey = 'Vml2ZWtvdHA6OFljNEhDeHo=';
        $type = 'TEXT';
        $sender = 'NSLSMS';
        $entityId = '1201164562188563646';
        $templateId = '1207170323851947619';
    
        $otp = rand(1000, 9999);
    
        $message = "Dear user, the OTP for $otp for Money Makers NSL LIFE";
    
        $existingOTP = DB::table('otp_sms')->where('mobile', $mobile)->first();
    
        if ($existingOTP) {
           
            DB::table('otp_sms')
                ->where('mobile', $mobile)
                ->update([
                    'otp' => $otp,
                    'status' => 1, 
                    'datetime' => now(),
                ]);
        } else {
            // Insert a new record into otp_sms table
            DB::table('otp_sms')->insert([
                'mobile' => $mobile,
                'otp' => $otp,
                'status' => 1, // Assuming 1 for successful status
                'datetime' => now(),
            ]);
        }
    
        // Make the API call
        $response = Http::get('http://login.swarajinfotech.com/domestic/sendsms/bulksms_v2.php', [
            'apikey' => $apikey,
            'type' => $type,
            'sender' => $sender,
            'entityId' => $entityId,
            'templateId' => $templateId,
            'mobile' => $mobile,
            'message' => $message,
        ]);
    
        // Validate the response
        if ($response->successful()) {
            return response()->json(['status' => 200,'message' => 'OTP sent successfully'], 200);
        } else {
            return response()->json(['status' => 400,'message' => 'Failed to send OTP'], 400);
        }
    }
    
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'otp' => 'required|numeric'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $mobile = $request->mobile;
        $otp = $request->otp;
    
        $existingOTP = DB::table('otp_sms')->where('mobile', $mobile)->first();
    
        if ($existingOTP) {
            
            if ($existingOTP->otp == $otp) {
             
              
    
                return response()->json(['status' => 200, 'message' => 'OTP verified successfully'], 200);
            } else {
                return response()->json(['status' => 400, 'message' => 'Invalid OTP'], 400);
            }
        } else {
            return response()->json(['status' => 400, 'message' => 'No OTP found for the provided mobile number'], 400);
        }
    }
    	
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
         
            'mobile' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $user = DB::table('users')
                    ->where('mobile', $request->mobile)
                    ->first();
    
        if (!$user) {
            return response()->json(['status' => 400,'message' => 'User not found'], 400);
        }
    
        $updated = DB::table('users')
                    ->where('mobile', $request->mobile)
                    ->update([
                        'password' => $request->password 
                    ]);
    
            return response()->json(['status' => 200,'message' => 'Password updated successfully'], 200);
       
    }
    
    	public function invitation_bonus_claim(Request $request)
    {
         $validator = Validator::make($request->all(), [
            'userid' => 'required|numeric',
            'amount' => 'required',
            'invite_id'=>'required'
        ]);
    
        $validator->stopOnFirstFailure();
    
        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ];
            return response()->json($response, 400);
        }
    
        $userid = $request->userid;
        $amount = $request->amount;
        $invite_id=$request->invite_id;
        $bonusClaim = DB::table('invite_bonus_claim')
                    ->where('userid', $userid)
                    ->where('invite_id', $invite_id)
                    ->get();
                    // dd($bonusClaim);
                    
    if($bonusClaim->isEmpty()){
    $user = DB::table('users')->where('id', $userid)->first();
    if (!empty($user)) {
       $usser= DB::table('users')->where('id', $userid)->update([
            'wallet' => $user->wallet + $amount, // Add amount to wallet
        ]);
    }else{
     return response()->json([
    				'message' => 'user not found ..!',
    				'status' => 400,
                    ], 400);
     }
     if (!empty($usser)) {
        // Insert into wallet_histories
        $bonuss=DB::table('wallet_history')->insert([
            'userid'     => $userid,
            'amount'      => $amount,
            'description' => 'Invitation Bonus',
            'subtypeid'     => 26, // Define type_id as 1 for bonus claim
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        
         $bonuss=DB::table('invite_bonus_claim')->insert([
            'userid'     => $userid,
            'invite_id' => $invite_id,
            'status' => 0,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
     }else{
     
     }
         if (!empty($bonuss)) {
                $response = [
                    'message' => 'invitation bonus claimed successfully!',
                    'status' => 200,
                ];
                return response()->json($response,200);
            } else {
                return response()->json([
    				'message' => 'Bonus not claimed ..!',
    				'status' => 400,
                    ], 400);
            }
            
           } else{
             return response()->json([
    				'message' => 'Already claimed ..!',
    				'status' => 400,
                    ], 400);  
           }
    	}
	
	public function getPaymentLimits()
{
    $details = DB::select("SELECT `name`, `amount` FROM `payment_limits` WHERE 1");
    //dd($details);

    if ($details) {
        $formattedData = [];
        foreach ($details as $detail) {
            $formattedData[$detail->name] = $detail->amount;
        }
        //dd($formattedData);
//return $formattedData;
        return response()->json([
            'status' => 200,
            'message' => 'Data found',
            'data' => $formattedData
        ]);
    } else {
        return response()->json([
            'message' => 'No record found',
            'status' => 400,
            'data' => []
        ], 400);
    }
}

	
	public function payin_usdt(Request $request)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'amount' => 'required|numeric|gt:0',
        'type' => 'required|in:0',
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }

    // Get input data
    $user_id = $request->user_id;
    $amount = $request->amount;
    $type = $request->type;
    $inr_amt = $amount * 94;
    
    // Get client IP address
   // $clientIp = $request->ip();

    // Dump and die to see IP address
    //dd('Client IP Address:', $clientIp); // Here, you can see the IP

    $email = 'Globalbettech@gmail.com'; 
    $token = '58839776549046321236110964258208'; // Replace with a secure token or config value
    $apiUrl = "https://cryptofit.biz/Payment/coinpayments_api_call";
    $coin = 'USDT.BEP20';

    // Generate unique order ID
    do {
        $orderId = str_pad(mt_rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT);
    } while (DB::table('payins')->where('order_id', $orderId)->exists());

    // User validation
    $user_exist = DB::table('users')->where('id', $user_id)->first();

    // Prepare API data
    $formData = [
        'txtamount' => $amount,
        'coin' => $coin,
        'UserID' => $email,
        'Token' => $token,
        'TransactionID' => $orderId,
    ];

    try {
        // Make API request
        $response = Http::asForm()->post($apiUrl, $formData);

        Log::info('PayIn API Response:', ['response' => $response->body()]);
        Log::info('PayIn API Status Code:', ['status' => $response->status()]);

        // Decode the response
        $apiResponse = json_decode($response->body());
        //dd($apiResponse); // You can dump API response here

        // Check if the API response is successful
        if ($response->successful() && isset($apiResponse->error) && $apiResponse->error === 'ok') {
            // Insert data into payins table
            $inserted_id = DB::table('payins')->insertGetId([
                'user_id' => $user_id,
                'status' => 1,
                'order_id' => $orderId,
                'cash' => $inr_amt,
                'usdt_amount' => $amount,
                'type' => $type,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Payment initiated successfully.',
                'data' => $apiResponse,
            ], 200);
        }

        return response()->json([
            'status' => 400,
            'message' => 'Failed to initiate payment.'
        ], 400);
    } catch (\Exception $e) {
        Log::error('PayIn API Error:', ['error' => $e->getMessage()]);
        return response()->json(['status' => 400, 'message' => 'Internal Server Error'], 400);
    }
}

  

public function payin_call_back(Request $request)
{
    // Validation
    $validator = Validator::make($request->all(), [
        'invoice' => 'required',
        'status_text' => 'required',
        'amount' => 'required'
    ]);

    $validator->stopOnFirstFailure();

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 200);
    }

    // Get input data
    $invoice = $request->invoice;
    $status_text = $request->status_text;
    $amount = $request->amount;

    // Get client IP address
    $clientIp = $request->ip();

    // Dump and die to see IP address
    dd('Client IP Address:', $clientIp); // Here, you can see the IP

    if ($status_text == 'complete') {
        // Update payment status
        $a = DB::table('payins')->where('order_id', $invoice)->update(['status' => 2]);

        if ($a) {
            // Get user details
            $user_detail = Payin::where('order_id', $invoice)
                ->where('status', 2)
                ->first();

            $user_id = $user_detail->user_id;
            $amount1 = $user_detail->cash;

            // Update wallet balance
            $update = User::where('id', $user_id)->update(['wallet' => $amount1]);

            return response()->json(['status' => 200, 'message' => 'Payment successful.'], 200);
        } else {
            return response()->json(['status' => 400, 'message' => 'Failed to update!'], 400);
        }
    } else {
        return response()->json(['status' => 400, 'message' => 'Something went wrong!'], 400);
    }
}

	
	public function getUrlIp()
{
    $url = 'root.globalbet24.live'; // Aapko full URL ke bajaye sirf domain name use karna hoga

    // Get the IPv4 address of the URL using gethostbyname
    $ipv4_address = gethostbyname($url);

    // Dump the IPv4 address to check
    dd('The IPv4 address of ' . $url . ' is: ' . $ipv4_address);
}




////tirangawin fucntion 
public function withdraw_old(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required',
        'account_id' => 'required',
        'type' => 'required',
        'amount' => 'required|numeric',
    ]);

    $validator->stopOnFirstFailure();
	
    if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }

    $userid = $request->input('user_id');
    $accountid = $request->input('account_id');
    $amount = $request->input('amount');
    $type = $request->input('type');
    
     $date = date('YmdHis');
        $rand = rand(11111, 99999);
        $orderid = $date . $rand;
    if ($amount > 99) {
       //($amount >= 550) 
        $wallet=DB::select("SELECT `wallet`,`recharge`,`first_recharge` FROM `users` WHERE id=$userid");
       $user_wallet=$wallet[0]->wallet;
      // dd($user_wallet);
       $user_recharge=$wallet[0]->recharge;
		$user_first_recharge=$wallet[0]->first_recharge;
       if($user_recharge == 0){
		   
		  if($user_first_recharge == 1){
			   $response = [ 'status' =>400, 'message' => 'First recharge Rs. 200 mandatory for withdrawal', ];
				return response()->json($response,400);  
		  }
		   
        if($user_wallet >= $amount){
       $data= DB::table('withdraw_histories')->insert([
    'user_id' => $userid,
    'amount' => $amount,
    'account_id' => $accountid,
    'type' => $type,
    'order_id' => $orderid,
    'status' => 1,
	'typeimage'=>"https://root.nandigame.live/uploads/fastpay_image.png",
    'created_at' => now(),
    'updated_at' => now(),
]);
      DB::select("UPDATE `users` SET `wallet`=`wallet`-$amount WHERE id=$userid;");
 if ($data) {
             $response = [
        'status' =>200,
        'message' => 'Withdraw Request Successfully ..!',
    ];

    return response()->json($response,200);

        } else {
             $response = [
        'status' =>400,
        'message' => 'Internal error..!',
    ];

    return response()->json($response,400);
            
        }
        }else{
       $response = [
        'status' =>400,
        'message' => 'insufficient Balance..!',
    ];

    return response()->json($response,400);
 }  
           
       }else {
         $response = [
        'status' =>400,
        'message' => 'need to bet amount 0 to be able to Withdraw',
    ];

    return response()->json($response,400);   
       }
        
    }else{
        $response['message'] = "minimum Withdraw 100 And Maximum Withdraw 25000";
            $response['status'] = "400";
            return response()->json($response,400);
    }

    
    
}




		
}