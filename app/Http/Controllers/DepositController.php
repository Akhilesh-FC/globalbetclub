<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class DepositController extends Controller
{
//     public function deposit_index($id)
//     {
//         //  $deposits= DB::select("SELECT payins.*,users.username AS uname,users.id As userid, users.mobile As mobile FROM `payins` LEFT JOIN users ON payins.user_id=users.id WHERE payins.status = '$id'");
//         $deposits=DB::select("SELECT payins.*, users.username AS uname, users.id AS userid, users.mobile AS mobile FROM payins LEFT JOIN users ON payins.user_id = users.id WHERE payins.status = $id AND users.id IS NOT NULL");
//          //dd($deposits);
//         return view('work_order_assign.deposit')->with('deposits',$deposits)->with('id',$id);
        
//     }
    
//     public function payin_success(Request $request, $id)
// {
//   // dd($id);
//     $pin = 2020;  // Predefined pin

//     // Retrieve the pin input from the request
//     $inputPin = $request->input('pin');
//   //dd($inputPin);
//     // Check if the input pin matches the predefined pin
//     if ($inputPin == $pin) {
//         if ($request->session()->has('id')) {
//             //dd("hii");
//             // Fetch payin details
//             $payin_details = DB::SELECT("SELECT * FROM `payins` WHERE `id` = ?", [$id]);
            

//             if (empty($payin_details)) {
                
//                 return redirect()->back()->with('error', 'Payin details not found!');
//             }

//             $amount = $payin_details[0]->cash;
//             $userid = $payin_details[0]->user_id;
            
//             // Update payin status
//             $update = DB::table('payins')
//                         ->where('id', '=', $id)
//                         ->update(['status' => 2]);

//             if ($update) {
//                 // Check if the user has already made their first recharge
//                 $user = DB::table('users')->where('id', '=', $userid)->first();

//                 // If first_recharge is 0, update it to 1
//                 if ($user->first_recharge == 0) {
//                     DB::table('users')
//                         ->where('id', '=', $userid)
//                         ->update(['first_recharge' => 1]);
//                 }

//                 // Update the user's wallet
//                 DB::table('users')
//                     ->where('id', '=', $userid)
//                     ->update(['wallet' => DB::raw('wallet + ' . $amount)]);

//                 return redirect()->back()->with('success', 'Payin approved successfully!');
//             } else {
//                 return redirect()->back()->with('error', 'Failed to update payin status!');
//             }
//         } else {
//             // Session does not exist
//             return redirect()->back()->with('error', 'Operation Failed!');
//         }
//     } else {
//         // Pin does not match, return an invalid pin message
//         return redirect()->back()->with('error', 'Invalid pin. Please try again.');
//     }
// }

    public function deposit_delete(Request $request,$id)
    {
    
		$value = $request->session()->has('id');
	
        if(!empty($value))
        {
        $data=DB::delete("DELETE FROM `payins` WHERE id=$id");
       
       return redirect()->back()->with('success', 'Deleted successfully!');
			  }
        else
        {
           return redirect()->route('login');  
        }
    }
    
    
    public function deposit_delete_all(Request $request)
    {
        
		$value = $request->session()->has('id');
        if(!empty($value))
        {
        $data=DB::delete("DELETE FROM `payins` WHERE status=1");
       
       return redirect()->back()->with('success', 'All Deleted successfully!');
			  }
        else
        {
           return redirect()->route('login');  
        }
    }
    
//  public function payin_success(Request $request, $id)
//  {
//  $pin = 2025;  // Predefined pin

//     // Retrieve the pin input from the request
//     $inputPin = $request->input('pin');

//     // Check if the input pin matches the predefined pin
//     if ($inputPin == $pin) {
       
//             // Fetch payin details
//     if ($request->session()->has('id')) {
//         // Fetch payin details
//         $payin_details = DB::SELECT("SELECT * FROM `payins` WHERE `id` = $id");
//         $amount = $payin_details[0]->cash;
//         $userid = $payin_details[0]->user_id;
        
//         // Update payin status
//         $update = DB::table('payins')
//                     ->where('id', '=', $id)
//                     ->update(['status' => 2]);

//         if ($update) {
//             // Check if the user has already made their first recharge
//             $user = DB::table('users')->where('id', '=', $userid)->first();

//             // If first_recharge is 0, update it to 1
//             if ($user->first_recharge == 0) {
//                 DB::table('users')
//                     ->where('id', '=', $userid)
//                     ->update(['first_recharge' => 1]);
//             }

//             // Update the user's wallet
//             DB::table('users')
//                 ->where('id', '=', $userid)
//                 ->update(['wallet' => DB::raw('wallet + ' . $amount)]);

//             return redirect()->back()->with('success', 'Payin approved successfully!');
//         }
//     else {
//             // Redirect back with failure message if session does not have 'id'
//             return redirect()->back()->with('error', 'Operation Failed!');
//         }
//     } else {
//         // Pin does not match, return an invalid pin message
//         return redirect()->back()->with('error', 'Invalid pin. Please try again.');
//     }
// }
  
// }


public function deposit_index($id)
    {
        //  $deposits= DB::select("SELECT payins.*,users.username AS uname,users.id As userid, users.mobile As mobile FROM `payins` LEFT JOIN users ON payins.user_id=users.id WHERE payins.status = '$id'");
        $deposits=DB::select("SELECT payins.*, users.username AS uname, users.id AS userid, users.mobile AS mobile FROM payins LEFT JOIN users ON payins.user_id = users.id WHERE payins.status = $id AND users.id IS NOT NULL;");
         //dd($deposits);
        return view('ManualPayment.index')->with('deposits',$deposits)->with('id',$id);
        
    }
    
public function payin_success(Request $request, $id)
{
    if ($request->session()->has('id')) {
        // Update status
        $payin_details = DB::SELECT("SELECT * FROM `payins` WHERE `id` = $id");
        $amount = $payin_details[0]->cash;
        $userid = $payin_details[0]->user_id;

        // Update the status of the payin
        $update = DB::table('payins')
            ->where('id', '=', $id)
            ->update(['status' => 2]);

        if ($update) {
            // Update the wallet of the user
            $updateWallet = DB::table('users')
                ->where('id', '=', $userid)
                ->update([
                    'wallet' => DB::raw('wallet + ' . $amount),
                    'recharge' => DB::raw('recharge + ' . $amount) // Adding amount to recharge column
                ]);
		
            // Check if first_recharge is 0 and update it to 1
            $user = DB::table('users')->where('id', '=', $userid)->first();
            if ($user->first_recharge == 0) {
                DB::table('users')
                    ->where('id', '=', $userid)
                    ->update(['first_recharge' => 1]);
            }

            return redirect()->back()->with('success', 'Payin approved successfully!');
        }
    } else {
        // Redirect back with failure message
        return redirect()->back()->with('error', 'Operation Failed!');
    }
}

// public function deposit_reject(string $id){

//                 DB::table('payins')->where('id', $id)->update([
//                         'status' => 3
//                 ]);

//                 return redirect()->back()->with('success', 'Successfully Updated.');
//         }

public function deposit_reject(Request $request, string $id){
    $request->validate([
        'pin' => 'required|numeric'
    ]);

    // Optional: validate PIN against your system (e.g., compare with session or DB)
    // if($request->pin != 'your-pin') return back()->with('error', 'Invalid PIN.');

    DB::table('payins')->where('id', $id)->update([
        'status' => 3
    ]);

    return redirect()->back()->with('success', 'Successfully Rejected.');
}


}
