<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Models\User;
//use App\Models\referrals;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use DateTime;

use Illuminate\Support\Facades\Http;


 
class AgencyPromotionController extends Controller
{
	public function promotion_data($id) 
{
    try {
        $user = User::findOrFail($id);
        $currentDate = Carbon::now()->subDay()->format('Y-m-d');
        //dd($currentDate);
        $directSubordinateCount = $user->referrals()->count();
        $totalCommission = $user->commission;
        $referralCode = $user->referral_code;
        $yesterdayTotalCommission = $user->yesterday_total_commission;

       
		$teamSubordinateCountResult = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?

        UNION ALL

        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level < 15
    )
    SELECT COUNT(*) as count
    FROM subordinates
", [$user->id]);

$teamSubordinateCount = $teamSubordinateCountResult[0]->count ?? 0;


//dd($teamSubordinateCount);
        $register = User::where('referral_user_id', $user->id)
                        ->whereDate('created_at', $currentDate)
                        ->count();


$depositStats = DB::selectOne("
    SELECT COUNT(p.id) AS deposit_number, SUM(p.cash) AS deposit_amount
    FROM payins p
    WHERE p.user_id IN (
        SELECT id FROM users WHERE referral_user_id = ?
    )
    AND DATE(p.created_at) = ?
    AND p.status = 2
", [$user->id, $currentDate]);

$depositNumber = $depositStats->deposit_number;
$depositAmount = $depositStats->deposit_amount;


           
    
    $firstDepositCount = DB::table('payins')
    ->whereIn('user_id', function($query) use ($user) {
        $query->select('id')->from('users')->where('referral_user_id', $user->id);
    })
    ->whereDate('created_at', $currentDate)
    ->distinct('user_id')
    ->count('user_id');

//dd($firstDepositCount);
      $subordinatesRegister = DB::selectOne("
    WITH RECURSIVE Subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?  
        AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN Subordinates s ON u.referral_user_id = s.id
        WHERE DATE(u.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    )
    SELECT COUNT(*) AS count FROM Subordinates
", [$user->id]);

 // Subordinate register count on the current day
       $subordinatesRegisters = DB::select("SELECT COUNT(*) as count FROM users WHERE referral_user_id IN ( SELECT id FROM users WHERE referral_user_id = $user->id ) AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY;");
//dd($subordinatesRegister);
// Direct integer value extract karna
$totalCount = $subordinatesRegister->count;


        // Fetch referred users (subordinates)
        $referUserIds = DB::table('users')
            ->where('referral_user_id', $user->id)
            ->pluck('id');  // Get the ids of all referred users


        if ($referUserIds->isNotEmpty()) {
            $subordinatesDeposit = DB::select("WITH RECURSIVE team_members AS (
    SELECT id
    FROM users
    WHERE referral_user_id = $user->id  -- starting from the user whose team we're analyzing
    UNION
    SELECT u.id
    FROM users u
    JOIN team_members tm ON tm.id = u.referral_user_id  -- recursively include all team members' downlines
)
SELECT COUNT(*) AS deposit_number, SUM(cash) AS deposit_amount
FROM payins
WHERE user_id IN (SELECT id FROM team_members)  -- consider deposits made by the entire team
AND DATE(created_at) = CURDATE() - INTERVAL 1 DAY
AND status = 2;
");
}


$depositNumber = $subordinatesDeposit[0]->deposit_number ?? 0;
$depositAmount = $subordinatesDeposit[0]->deposit_amount ?? 0;
//dd($subordinatesDeposit);

        $subordinatesFirstDepositCount =// Get first deposit count for subordinates
            $totalFirstDepositCount = DB::table('payins')
                ->whereIn('user_id', $referUserIds)
                ->whereDate('created_at', $currentDate)
                ->count();
        
//dd($subordinatesFirstDepositCount);
        // Result array to return
        $result = [
            'yesterday_total_commission' => $yesterdayTotalCommission ?? 0,
            
            'register' => $register,
            'deposit_number' => $depositStats->deposit_number ?? 0,
            'deposit_amount' => $depositStats->deposit_amount ?? 0,
            'first_deposit' => $firstDepositCount,
            'subordinates_register' => $totalCount,
            'subordinates_deposit_number' => $depositNumber ?? 0,
            'subordinates_deposit_amount' => $depositAmount ?? 0,
            'subordinates_first_deposit' => $subordinatesFirstDepositCount,
            'direct_subordinate' => $directSubordinateCount,
            'total_commission' => $totalCommission,
			'weekly_commission'=>0,
            'team_subordinate' => $teamSubordinateCount,
            'referral_code' => $referralCode,
        ];
        //dd($subordinatesDeposit);
       // dd($depositNumber);

//         return response()->json(['status' => 200,'message' => 'data fetch successfully','data' =>$result], 200);
//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }


                return response()->json($result,200);
		
     
    } catch (\Exception $e) {
       
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
	

	public function new_subordinate(Request $request){
	try {
		
		 $validator = Validator::make($request->all(), [
            'id' => 'required',
			'type' => 'required',
        ]);

        $validator->stopOnFirstFailure();
	
        if($validator->fails()){
         $response = [
                        'status' => 400,
                       'message' => $validator->errors()->first()
                      ]; 
		
		return response()->json($response,400);
		
    }
       
        $users = User::findOrFail($request->id);
        $user_id = $users->id;
		
		$currentDate = Carbon::now()->format('Y-m-d');
		$yesterdayDate  = Carbon::yesterday()->format('Y-m-d');
		$startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');
		
		if($request->type == 1){
		$subordinate_data = DB::table('users')->select('mobile', 'u_id', 'created_at')
    ->where('referral_user_id', $user_id)
    ->where('created_at', 'like', $currentDate . '%')
    ->get();
			
			if($subordinate_data->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $subordinate_data]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
			
		}elseif($request->type == 2){
			
				$subordinate_data = DB::table('users')->select('mobile', 'u_id', 'created_at')
    ->where('referral_user_id', $user_id)
    ->where('created_at', 'like', $currentDate . '%')
    ->get();
			
			if($subordinate_data->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $subordinate_data]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
			
		}elseif($request->type == 3){
				$subordinate_data = DB::table('users')->select('mobile', 'u_id', 'created_at')
    ->where('referral_user_id', $user_id)
    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
    ->get();
			
			if($subordinate_data->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $subordinate_data]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
		}
		
		
		 } catch (\Exception $e) {
       
        return response()->json(['error' => $e->getMessage()], 500);
    }
 }

	public function tier(){
		try {
			
		//$tier =	DB::table('mlm_levels')->select('name')->get();
		$tier = DB::table('mlm_levels')->select('*')->get();
			
			if($tier->isNotEmpty()){
					 $response = ['status' => 200,'message' => 'Successfully..!', 'data' => $tier]; 
		
		               return response()->json($response,200);
			}else{
				 $response = ['status' => 400, 'message' => 'data not fount' ]; 
		
		        return response()->json($response,400);
			}
			
			} catch (\Exception $e) {
       
        	 return response()->json(['error' => $e->getMessage()], 500);
      }
		
		
	}

	public function subordinate_data_working(Request $request) {
	
    try {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'tier' => 'required|integer|min:1',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            $response = [
                'status' => 400,
                'message' => $validator->errors()->first()
            ]; 
            return response()->json($response, 400);
        }

        $user_id = $request->id; 
        $tier = $request->tier; 
		$search_uid = $request->u_id;
        $currentDate = Carbon::now()->subDay()->format('Y-m-d');
		
		  if (!empty($search_uid)) {
           $subordinates_deposit = \DB::select("
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(SUM(bets.amount), 0) AS bet_amount, 
        COALESCE(SUM(payins.cash), 0) AS total_cash, 
        users.commission AS commission, 
        DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date 
    FROM users
    LEFT JOIN bets ON users.id = bets.userid AND bets.created_at LIKE ?
    LEFT JOIN payins ON users.id = payins.user_id AND payins.created_at LIKE ?
    WHERE users.u_id LIKE ?
    GROUP BY users.id, users.u_id, users.commission;
", [$currentDate . ' %', $currentDate . ' %', $search_uid .'%']);
			  
			 
			  $subordinates_data = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(payin_summary1.total_payins, 0) AS payin_count,
        COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
        COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
        COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid
    
    LEFT JOIN (
        SELECT user_id, SUM(cash) AS total_payin_cash
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary2 ON users.id = payin_summary2.user_id
    
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_payins
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary1 ON users.id = payin_summary1.user_id

    LEFT JOIN (
        SELECT userid, COUNT(DISTINCT userid) AS total_bettors
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bettor_count ON users.id = bettor_count.userid
    WHERE users.id IN (
        SELECT id FROM subordinates WHERE level = ?
    )
    GROUP BY 
        users.id, 
        users.u_id, 
        payin_summary1.total_payins,
        bettor_count.total_bettors,
        bet_summary.total_bet_amount,
        payin_summary2.total_payin_cash
", [$user_id, $tier, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier]);



			  

        } else {
		
       $subordinates_deposit = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount, 
        COALESCE(payin_summary.total_cash, 0) AS total_cash,  
        users.commission AS commission, 
        DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date 
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid 
    LEFT JOIN (
        SELECT user_id, SUM(cash) AS total_cash 
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary ON users.id = payin_summary.user_id
    WHERE users.id IN (
        SELECT id FROM subordinates WHERE level = ?
    )
    GROUP BY users.id, users.u_id, users.commission, bet_summary.total_bet_amount, payin_summary.total_cash
",[$user_id, $tier, $currentDate . ' %', $currentDate . ' %', $tier]);
		
	$subordinates_data = \DB::select("
    WITH RECURSIVE subordinates AS (
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        users.u_id, 
        COALESCE(payin_summary1.total_payins, 0) AS payin_count,
        COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
        COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
        COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount
    FROM users
    LEFT JOIN (
        SELECT userid, SUM(amount) AS total_bet_amount 
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bet_summary ON users.id = bet_summary.userid
    
    LEFT JOIN (
        SELECT user_id, SUM(cash) AS total_payin_cash
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary2 ON users.id = payin_summary2.user_id
    
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS total_payins
        FROM payins 
        WHERE status = 2 AND created_at LIKE ? 
        GROUP BY user_id
    ) AS payin_summary1 ON users.id = payin_summary1.user_id

    LEFT JOIN (
        SELECT userid, COUNT(DISTINCT userid) AS total_bettors
        FROM bets 
        WHERE created_at LIKE ? 
        GROUP BY userid
    ) AS bettor_count ON users.id = bettor_count.userid
    WHERE users.id IN (
        SELECT id FROM subordinates WHERE level = ?
    )
    GROUP BY 
        users.id, 
        users.u_id, 
        payin_summary1.total_payins,
        bettor_count.total_bettors,
        bet_summary.total_bet_amount,
        payin_summary2.total_payin_cash
", [$user_id, $tier, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier]);



		 }

        $result = [
			'number_of_deposit' => $subordinates_data[0]->payin_count,
			'payin_amount' => $subordinates_data[0]->payin_amount,
			'number_of_bettor' => $subordinates_data[0]->bettor_count,
			'bet_amount' => $subordinates_data[0]->bet_amount,
			'first_deposit ' => $subordinates_data[0]->total_first_recharge ?? 0,
			'first_deposit_amount' => $subordinates_data[0]->total_first_deposit_amount ?? 0,
			
            'subordinates_data' => $subordinates_deposit ?? 0,
        ];

        return response()->json($result, 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
		
    }
    
}

public function subordinate_data(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'tier' => 'required|integer|min:0',
        ]);

        $validator->stopOnFirstFailure();

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $user_id = $request->id;
        $tier = $request->tier;
        $search_uid = $request->u_id;
        $currentDate = Carbon::now()->subDay()->format('Y-m-d');

        // Recursive sub-query
        $subQuery = "
            WITH RECURSIVE subordinates AS (
                SELECT id, referral_user_id, 1 AS level
                FROM users
                WHERE referral_user_id = ?
                UNION ALL
                SELECT u.id, u.referral_user_id, s.level + 1
                FROM users u
                INNER JOIN subordinates s ON s.id = u.referral_user_id
                WHERE s.level + 1 <= ?
            )
        ";

        // Fix: use AND instead of second WHERE
        $levelCondition = ($tier == 0) ? '' : ' AND sub.level = ?';

        // If u_id is searched
        if (!empty($search_uid)) {
            $subordinates_data = \DB::select("
                $subQuery
                SELECT 
                    users.id, 
                    users.u_id, 
                    COALESCE(payin_summary1.total_payins, 0) AS payin_count,
                    COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
                    COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
                    COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount,
                    sub.level
                FROM users
                JOIN subordinates sub ON users.id = sub.id
                LEFT JOIN (
                    SELECT userid, SUM(amount) AS total_bet_amount 
                    FROM bets 
                    WHERE created_at LIKE ? 
                    GROUP BY userid
                ) AS bet_summary ON users.id = bet_summary.userid
                LEFT JOIN (
                    SELECT user_id, SUM(cash) AS total_payin_cash
                    FROM payins 
                    WHERE status = 2 AND created_at LIKE ? 
                    GROUP BY user_id
                ) AS payin_summary2 ON users.id = payin_summary2.user_id
                LEFT JOIN (
                    SELECT user_id, COUNT(*) AS total_payins
                    FROM payins 
                    WHERE status = 2 AND created_at LIKE ? 
                    GROUP BY user_id
                ) AS payin_summary1 ON users.id = payin_summary1.user_id
                LEFT JOIN (
                    SELECT userid, COUNT(DISTINCT userid) AS total_bettors
                    FROM bets 
                    WHERE created_at LIKE ? 
                    GROUP BY userid
                ) AS bettor_count ON users.id = bettor_count.userid
                WHERE users.u_id LIKE ?
                $levelCondition
                GROUP BY 
                    users.id, users.u_id, payin_summary1.total_payins,
                    bettor_count.total_bettors, bet_summary.total_bet_amount,
                    payin_summary2.total_payin_cash, sub.level
            ", 
            ($tier == 0)
                ? [$user_id, 10, $currentDate.'%', $currentDate.'%', $currentDate.'%', $currentDate.'%', $search_uid.'%']
                : [$user_id, $tier, $currentDate.'%', $currentDate.'%', $currentDate.'%', $currentDate.'%', $search_uid.'%', $tier]
            );

            $subordinates_deposit = $subordinates_data;

        } else {
            // No u_id searched
            $subordinates_data = \DB::select("
                $subQuery
                SELECT 
                    users.id, 
                    users.u_id, 
                    COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount, 
                    COALESCE(payin_summary.total_cash, 0) AS total_cash,  
                    users.commission AS commission, 
                    DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date,
                    sub.level
                FROM users
                JOIN subordinates sub ON users.id = sub.id
                LEFT JOIN (
                    SELECT userid, SUM(amount) AS total_bet_amount 
                    FROM bets 
                    WHERE created_at LIKE ? 
                    GROUP BY userid
                ) AS bet_summary ON users.id = bet_summary.userid 
                LEFT JOIN (
                    SELECT user_id, SUM(cash) AS total_cash 
                    FROM payins 
                    WHERE status = 2 AND created_at LIKE ? 
                    GROUP BY user_id
                ) AS payin_summary ON users.id = payin_summary.user_id
                WHERE users.u_id LIKE ?
                $levelCondition
                GROUP BY users.id, users.u_id, users.commission, bet_summary.total_bet_amount, payin_summary.total_cash, sub.level
            ",
            ($tier == 0)
                ? [$user_id, 10, $currentDate.'%', $currentDate.'%', '%']
                : [$user_id, $tier, $currentDate.'%', $currentDate.'%', '%', $tier]
            );

            $subordinates_deposit = $subordinates_data;
        }

        $result = [
            'number_of_deposit' => $subordinates_data[0]->payin_count ?? 0,
            'payin_amount' => $subordinates_data[0]->payin_amount ?? 0,
            'number_of_bettor' => $subordinates_data[0]->bettor_count ?? 0,
            'bet_amount' => $subordinates_data[0]->bet_amount ?? 0,
            'first_deposit' => $subordinates_data[0]->total_first_recharge ?? 0,
            'first_deposit_amount' => $subordinates_data[0]->total_first_deposit_amount ?? 0,
            'subordinates_data' => $subordinates_deposit ?? []
        ];

        return response()->json($result, 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


// public function subordinate_data(Request $request) {
//     try {
//         $validator = Validator::make($request->all(), [
//             'id' => 'required',
//             'tier' => 'required|integer|min:0',
//         ]);
// //dd($request);
//         $validator->stopOnFirstFailure();

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => $validator->errors()->first()
//             ], 400);
//         }

//         $user_id = $request->id;
//         $tier = $request->tier;
//         $search_uid = $request->u_id;
//         $currentDate = Carbon::now()->subDay()->format('Y-m-d');

//         // RECURSIVE subquery part is reused for both cases
//         $subQuery = "
//             WITH RECURSIVE subordinates AS (
//                 SELECT id, referral_user_id, 1 AS level
//                 FROM users
//                 WHERE referral_user_id = ?
//                 UNION ALL
//                 SELECT u.id, u.referral_user_id, s.level + 1
//                 FROM users u
//                 INNER JOIN subordinates s ON s.id = u.referral_user_id
//                 WHERE s.level + 1 <= ?
//             )
//         ";

//         $levelCondition = ($tier == 0) ? '' : 'WHERE sub.level = ?';

//         if (!empty($search_uid)) {
//             // If u_id is searched
//             $subordinates_deposit = \DB::select("
//                 SELECT 
//                     users.id, 
//                     users.u_id, 
//                     COALESCE(SUM(bets.amount), 0) AS bet_amount, 
//                     COALESCE(SUM(payins.cash), 0) AS total_cash, 
//                     users.commission AS commission, 
//                     DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date,
//                     1 AS level
//                 FROM users
//                 LEFT JOIN bets ON users.id = bets.userid AND bets.created_at LIKE ?
//                 LEFT JOIN payins ON users.id = payins.user_id AND payins.created_at LIKE ?
//                 WHERE users.u_id LIKE ?
//                 GROUP BY users.id, users.u_id, users.commission
//             ", [$currentDate . ' %', $currentDate . ' %', $search_uid . '%']);

//             $subordinates_data = \DB::select("
//                 $subQuery
//                 SELECT 
//                     users.id, 
//                     users.u_id, 
//                     COALESCE(payin_summary1.total_payins, 0) AS payin_count,
//                     COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
//                     COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
//                     COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount,
//                     sub.level
//                 FROM users
//                 JOIN subordinates sub ON users.id = sub.id
//                 LEFT JOIN (
//                     SELECT userid, SUM(amount) AS total_bet_amount 
//                     FROM bets 
//                     WHERE created_at LIKE ? 
//                     GROUP BY userid
//                 ) AS bet_summary ON users.id = bet_summary.userid
//                 LEFT JOIN (
//                     SELECT user_id, SUM(cash) AS total_payin_cash
//                     FROM payins 
//                     WHERE status = 2 AND created_at LIKE ? 
//                     GROUP BY user_id
//                 ) AS payin_summary2 ON users.id = payin_summary2.user_id
//                 LEFT JOIN (
//                     SELECT user_id, COUNT(*) AS total_payins
//                     FROM payins 
//                     WHERE status = 2 AND created_at LIKE ? 
//                     GROUP BY user_id
//                 ) AS payin_summary1 ON users.id = payin_summary1.user_id
//                 LEFT JOIN (
//                     SELECT userid, COUNT(DISTINCT userid) AS total_bettors
//                     FROM bets 
//                     WHERE created_at LIKE ? 
//                     GROUP BY userid
//                 ) AS bettor_count ON users.id = bettor_count.userid
//                 $levelCondition
//                 GROUP BY 
//                     users.id, 
//                     users.u_id, 
//                     payin_summary1.total_payins,
//                     bettor_count.total_bettors,
//                     bet_summary.total_bet_amount,
//                     payin_summary2.total_payin_cash,
//                     sub.level
//             ", ($tier == 0)
//                 ? [$user_id, 10, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%']
//                 : [$user_id, $tier, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier]
//             );

//         } else {
//             // If no u_id is searched
//             $subordinates_deposit = \DB::select("
//     $subQuery
//     SELECT 
//         users.id, 
//         users.u_id, 
//         COALESCE(SUM(bets.amount), 0) AS bet_amount, 
//         COALESCE(SUM(payins.cash), 0) AS total_cash, 
//         users.commission AS commission, 
//         DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date,
//         sub.level
//     FROM users
//     JOIN subordinates sub ON users.id = sub.id
//     LEFT JOIN bets ON users.id = bets.userid AND bets.created_at LIKE ?
//     LEFT JOIN payins ON users.id = payins.user_id AND payins.created_at LIKE ?
//     WHERE users.u_id LIKE ?
//     $levelCondition
//     GROUP BY users.id, users.u_id, users.commission, sub.level
// ", ($tier == 0)
//     ? [$user_id, 10, $currentDate . '%', $currentDate . '%', $search_uid . '%']
//     : [$user_id, $tier, $currentDate . '%', $currentDate . '%', $search_uid . '%', $tier]
// );


//           $subordinates_deposit = \DB::select("
//     $subQuery
//     SELECT 
//         users.id, 
//         users.u_id, 
//         COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount, 
//         COALESCE(payin_summary.total_cash, 0) AS total_cash,  
//         users.commission AS commission, 
//         DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date,
//         sub.level
//     FROM users
//     JOIN subordinates sub ON users.id = sub.id
//     LEFT JOIN (
//         SELECT userid, SUM(amount) AS total_bet_amount 
//         FROM bets 
//         WHERE created_at LIKE ? 
//         GROUP BY userid
//     ) AS bet_summary ON users.id = bet_summary.userid 
//     LEFT JOIN (
//         SELECT user_id, SUM(cash) AS total_cash 
//         FROM payins 
//         WHERE status = 2 AND created_at LIKE ? 
//         GROUP BY user_id
//     ) AS payin_summary ON users.id = payin_summary.user_id
//     $levelCondition
//     WHERE users.u_id LIKE ?
//     GROUP BY users.id, users.u_id, users.commission, bet_summary.total_bet_amount, payin_summary.total_cash, sub.level
// ", ($tier == 0)
//     ? [$user_id, 10, $currentDate . '%', $currentDate . '%', $search_uid . '%']
//     : [$user_id, $tier, $currentDate . '%', $currentDate . '%', $search_uid . '%', $tier]
// );

//         }
        
//         $result = [
//             'number_of_deposit' => $subordinates_data[0]->payin_count ?? 0,
//             'payin_amount' => $subordinates_data[0]->payin_amount ?? 0,
//             'number_of_bettor' => $subordinates_data[0]->bettor_count ?? 0,
//             'bet_amount' => $subordinates_data[0]->bet_amount ?? 0,
//             'first_deposit' => $subordinates_data[0]->total_first_recharge ?? 0,
//             'first_deposit_amount' => $subordinates_data[0]->total_first_deposit_amount ?? 0,
//             'subordinates_data' => $subordinates_deposit ?? []
//         ];

//         return response()->json($result, 200);

//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }

// public function subordinate_data(Request $request) {
//     try {
//         $validator = Validator::make($request->all(), [
//             'id' => 'required',
//             'tier' => 'required|integer|min:0',
//         ]);

//         $validator->stopOnFirstFailure();

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => 400,
//                 'message' => $validator->errors()->first()
//             ], 400);
//         }

//         $user_id = $request->id;
//         $tier = $request->tier;
//         $search_uid = $request->u_id;
//         $currentDate = Carbon::now()->subDay()->format('Y-m-d');

//         // RECURSIVE subquery part is reused for both cases
//         $subQuery = "
//             WITH RECURSIVE subordinates AS (
//                 SELECT id, referral_user_id, 1 AS level
//                 FROM users
//                 WHERE referral_user_id = ?
//                 UNION ALL
//                 SELECT u.id, u.referral_user_id, s.level + 1
//                 FROM users u
//                 INNER JOIN subordinates s ON s.id = u.referral_user_id
//                 WHERE s.level + 1 <= ?
//             )
//         ";

//         $levelCondition = ($tier == 0) ? '' : 'WHERE sub.level = ?';

//         if (!empty($search_uid)) {
//             // If u_id is searched
//             $subordinates_deposit = \DB::select("
//                 SELECT 
//                     users.id, 
//                     users.u_id, 
//                     COALESCE(SUM(bets.amount), 0) AS bet_amount, 
//                     COALESCE(SUM(payins.cash), 0) AS total_cash, 
//                     users.commission AS commission, 
//                     DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date,
//                     1 AS level
//                 FROM users
//                 LEFT JOIN bets ON users.id = bets.userid AND bets.created_at LIKE ?
//                 LEFT JOIN payins ON users.id = payins.user_id AND payins.created_at LIKE ?
//                 WHERE users.u_id LIKE ?
//                 GROUP BY users.id, users.u_id, users.commission
//             ", [$currentDate . ' %', $currentDate . ' %', $search_uid . '%']);

//             $subordinates_data = \DB::select("
//                 $subQuery
//                 SELECT 
//                     users.id, 
//                     users.u_id, 
//                     COALESCE(payin_summary1.total_payins, 0) AS payin_count,
//                     COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
//                     COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
//                     COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount,
//                     sub.level
//                 FROM users
//                 JOIN subordinates sub ON users.id = sub.id
//                 LEFT JOIN (
//                     SELECT userid, SUM(amount) AS total_bet_amount 
//                     FROM bets 
//                     WHERE created_at LIKE ? 
//                     GROUP BY userid
//                 ) AS bet_summary ON users.id = bet_summary.userid
//                 LEFT JOIN (
//                     SELECT user_id, SUM(cash) AS total_payin_cash
//                     FROM payins 
//                     WHERE status = 2 AND created_at LIKE ? 
//                     GROUP BY user_id
//                 ) AS payin_summary2 ON users.id = payin_summary2.user_id
//                 LEFT JOIN (
//                     SELECT user_id, COUNT(*) AS total_payins
//                     FROM payins 
//                     WHERE status = 2 AND created_at LIKE ? 
//                     GROUP BY user_id
//                 ) AS payin_summary1 ON users.id = payin_summary1.user_id
//                 LEFT JOIN (
//                     SELECT userid, COUNT(DISTINCT userid) AS total_bettors
//                     FROM bets 
//                     WHERE created_at LIKE ? 
//                     GROUP BY userid
//                 ) AS bettor_count ON users.id = bettor_count.userid
//                 $levelCondition
//                 GROUP BY 
//                     users.id, 
//                     users.u_id, 
//                     payin_summary1.total_payins,
//                     bettor_count.total_bettors,
//                     bet_summary.total_bet_amount,
//                     payin_summary2.total_payin_cash,
//                     sub.level
//             ", ($tier == 0)
//                 ? [$user_id, 10, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%']
//                 : [$user_id, $tier, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier]
//             );

//         } else {
//             // If no u_id is searched
//             $subordinates_deposit = \DB::select("
//                 $subQuery
//                 SELECT 
//                     users.id, 
//                     users.u_id, 
//                     COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount, 
//                     COALESCE(payin_summary.total_cash, 0) AS total_cash,  
//                     users.commission AS commission, 
//                     DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS yesterday_date,
//                     sub.level
//                 FROM users
//                 JOIN subordinates sub ON users.id = sub.id
//                 LEFT JOIN (
//                     SELECT userid, SUM(amount) AS total_bet_amount 
//                     FROM bets 
//                     WHERE created_at LIKE ? 
//                     GROUP BY userid
//                 ) AS bet_summary ON users.id = bet_summary.userid 
//                 LEFT JOIN (
//                     SELECT user_id, SUM(cash) AS total_cash 
//                     FROM payins 
//                     WHERE status = 2 AND created_at LIKE ? 
//                     GROUP BY user_id
//                 ) AS payin_summary ON users.id = payin_summary.user_id
//                 $levelCondition
//                 GROUP BY users.id, users.u_id, users.commission, bet_summary.total_bet_amount, payin_summary.total_cash, sub.level
//             ", ($tier == 0)
//                 ? [$user_id, 10, $currentDate . '%', $currentDate . '%']
//                 : [$user_id, $tier, $currentDate . '%', $currentDate . '%', $tier]
//             );

//             $subordinates_data = \DB::select("
//                 $subQuery
//                 SELECT 
//                     users.id, 
//                     users.u_id, 
//                     COALESCE(payin_summary1.total_payins, 0) AS payin_count,
//                     COALESCE(bettor_count.total_bettors, 0) AS bettor_count,
//                     COALESCE(bet_summary.total_bet_amount, 0) AS bet_amount,
//                     COALESCE(payin_summary2.total_payin_cash, 0) AS payin_amount,
//                     sub.level
//                 FROM users
//                 JOIN subordinates sub ON users.id = sub.id
//                 LEFT JOIN (
//                     SELECT userid, SUM(amount) AS total_bet_amount 
//                     FROM bets 
//                     WHERE created_at LIKE ? 
//                     GROUP BY userid
//                 ) AS bet_summary ON users.id = bet_summary.userid
//                 LEFT JOIN (
//                     SELECT user_id, SUM(cash) AS total_payin_cash
//                     FROM payins 
//                     WHERE status = 2 AND created_at LIKE ? 
//                     GROUP BY user_id
//                 ) AS payin_summary2 ON users.id = payin_summary2.user_id
//                 LEFT JOIN (
//                     SELECT user_id, COUNT(*) AS total_payins
//                     FROM payins 
//                     WHERE status = 2 AND created_at LIKE ? 
//                     GROUP BY user_id
//                 ) AS payin_summary1 ON users.id = payin_summary1.user_id
//                 LEFT JOIN (
//                     SELECT userid, COUNT(DISTINCT userid) AS total_bettors
//                     FROM bets 
//                     WHERE created_at LIKE ? 
//                     GROUP BY userid
//                 ) AS bettor_count ON users.id = bettor_count.userid
//                 $levelCondition
//                 GROUP BY 
//                     users.id, 
//                     users.u_id, 
//                     payin_summary1.total_payins,
//                     bettor_count.total_bettors,
//                     bet_summary.total_bet_amount,
//                     payin_summary2.total_payin_cash,
//                     sub.level
//             ", ($tier == 0)
//                 ? [$user_id, 10, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%']
//                 : [$user_id, $tier, $currentDate . '%', $currentDate . '%', $currentDate . '%', $currentDate . '%', $tier]
//             );
//         }
//         $result = [
//             'number_of_deposit' => $subordinates_data[0]->payin_count ?? 0,
//             'payin_amount' => $subordinates_data[0]->payin_amount ?? 0,
//             'number_of_bettor' => $subordinates_data[0]->bettor_count ?? 0,
//             'bet_amount' => $subordinates_data[0]->bet_amount ?? 0,
//             'first_deposit' => $subordinates_data[0]->total_first_recharge ?? 0,
//             'first_deposit_amount' => $subordinates_data[0]->total_first_deposit_amount ?? 0,
//             'subordinates_data' => $subordinates_deposit ?? []
//         ];

//         return response()->json($result, 200);

//     } catch (\Exception $e) {
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// }



public function turnover_new_deposit_amount_condition()
{
        $datetime = Carbon::now();
    $currentDate = Carbon::now()->subDay()->format('Y-m-d');
    
    DB::table('users')->update(['yesterday_total_commission' => 0]);

    $referralUsers = DB::table('users')->whereNotNull('referral_user_id')->get();
    //dd($referralUsers);
    $referralUsersCount = $referralUsers->count();

    if ($referralUsersCount > 0) {

        foreach ($referralUsers as $referralUser) {
            $user_id = $referralUser->id;
            $maxTier = 10;

           $subordinatesData = \DB::select("
    WITH RECURSIVE subordinates AS (
        -- Base case: Start from users directly referred by the current user
        SELECT id, referral_user_id, 1 AS level
        FROM users
        WHERE referral_user_id = ?
        UNION ALL
        -- Recursive case: Get users referred by users in the previous level
        SELECT u.id, u.referral_user_id, s.level + 1
        FROM users u
        INNER JOIN subordinates s ON s.id = u.referral_user_id
        WHERE s.level + 1 <= ?
    )
    SELECT 
        users.id, 
        subordinates.level,
        COALESCE(SUM(payins.total_payin_amount), 0) AS total_payin_amount,  -- Changed from bet_summary to payins
        COALESCE(SUM(payins.total_payin_amount), 0) * COALESCE(level_commissions.commission, 0) / 100 AS commission
    FROM users
    LEFT JOIN (
        -- Sum payin amounts for each user for the previous day
        SELECT user_id, SUM(cash) AS total_payin_amount  -- Using the payins table
        FROM payins
        WHERE status = 2
        AND created_at LIKE ?  -- Added AND here to fix the multiple WHERE issue
        GROUP BY user_id
    ) AS payins ON users.id = payins.user_id
    LEFT JOIN subordinates ON users.id = subordinates.id  -- Join with subordinates
    LEFT JOIN (
        -- Commission rates for each level
        SELECT id, commission
        FROM mlm_levels
    ) AS level_commissions ON subordinates.level = level_commissions.id
    WHERE subordinates.level <= ?  -- Filter by max tier level
    GROUP BY users.id, subordinates.level, level_commissions.commission;
", [$user_id, $maxTier, $currentDate . '%', $maxTier]);

           //return $subordinatesData;
            $totalCommission = 0;
            //$totalPayinAmount = 0;
            foreach ($subordinatesData as $data) {
                $totalCommission += $data->commission;
                //$totalPayinAmount += $data->total_payin_amount;
                //dd($totalPayinAmount);
            }
            
            DB::table('users')->where('id', $user_id)->update([
    'wallet' => DB::raw('wallet + ' . $totalCommission),  // Preserve the current wallet amount and add the commission
    'recharge' => DB::raw('recharge + ' . $totalCommission),  // Add commission to recharge
    'commission' => DB::raw('commission + ' . $totalCommission),  // Add commission to commission field
    'yesterday_total_commission' => $totalCommission,  // Set yesterday's total commission
    'updated_at' => $datetime,  // Update the timestamp
]);


            // DB::table('users')->where('id', $user_id)->update([
            //     'wallet' => DB::raw('wallet + ' . $totalCommission),
            //      'wallet' => DB::raw('recharge + ' . $totalCommission),
            //     'commission' => DB::raw('commission + ' . $totalCommission),
            //     'yesterday_total_commission' => $totalCommission,
            //     'updated_at' => $datetime,
            // ]);

            DB::table('wallet_history')->insert([
                'userid' => $user_id,
                'amount' => $totalCommission,
                'subtypeid' => 23,
                //'description' => $totalPayinAmount,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
            $this->gameSerialNo();
        }

    } else {
        return response()->json(['message' => 'No referral users found.'], 400);
    }
}

public function turnover_new()
{
    $datetime = Carbon::now('Asia/Kolkata');
    $currentDate = Carbon::now('Asia/Kolkata')->subDay()->format('Y-m-d');

    // Reset all users' yesterday commissions
    DB::table('users')->update(['yesterday_total_commission' => 0]);

    // Get users who have referred others
    $referralUsers = DB::table('users')
        ->select('id')
        ->whereIn('id', function ($query) {
            $query->select('referral_user_id')->from('users')->whereNotNull('referral_user_id');
        })
        ->get();

    foreach ($referralUsers as $user) {
        $user_id = $user->id;

        // Count direct referrals
        $directReferralCount = DB::table('users')->where('referral_user_id', $user_id)->count();
        if ($directReferralCount < 1) continue;

        $maxLevel = min($directReferralCount, 10); // Max level limited by direct referrals

        // Recursive and bet aggregation query
        $subordinatesData = DB::select("
            WITH RECURSIVE subordinates AS (
                SELECT id, referral_user_id, 1 AS level
                FROM users
                WHERE referral_user_id = ?
                UNION ALL
                SELECT u.id, u.referral_user_id, s.level + 1
                FROM users u
                INNER JOIN subordinates s ON s.id = u.referral_user_id
                WHERE s.level + 1 <= ?
            ),
            combined_bets AS (
                SELECT userid, SUM(amount) AS total_bet_amount
                FROM bets
                WHERE DATE(created_at) = ?
                GROUP BY userid
            )
            SELECT 
                s.level,
                cb.total_bet_amount,
                COALESCE(cb.total_bet_amount, 0) * COALESCE(mlm_levels.commission, 0) / 100 AS commission
            FROM subordinates s
            LEFT JOIN combined_bets cb ON s.id = cb.userid
            LEFT JOIN mlm_levels ON s.level = mlm_levels.id
        ", [
            $user_id,
            $maxLevel,
            $currentDate,
        ]);
        //return $subordinatesData;

        // Process commission
        $totalCommission = 0;
        $totalBetAmount = 0;
        $totalBetterCount = 0;

        foreach ($subordinatesData as $row) {
            $betAmount = $row->total_bet_amount ?? 0;
            $commission = $row->commission ?? 0;

            if ($commission > 0) {
                if ($betAmount > 0) {
                    $totalBetterCount++;
                    $totalBetAmount += $betAmount;
                }
                $totalCommission += $commission;
            }
        }

        if ($totalCommission > 0) {
            DB::table('users')->where('id', $user_id)->update([
                'wallet' => DB::raw('wallet + ' . $totalCommission),
                'commission' => DB::raw('commission + ' . $totalCommission),
                'yesterday_total_commission' => $totalCommission,
                'updated_at' => $datetime,
            ]);

            DB::table('wallet_history')->insert([
                'userid' => $user_id,
                'amount' => $totalCommission,
                'subtypeid' => 23, // turnover commission
                'description_2' => $totalBetterCount, // number of subordinates with bets
                'description' => $totalBetAmount, // total bet amount
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ]);
        }
    }

    return response()->json(['message' => 'Turnover and commissions calculated successfully.']);
}


  private function gameSerialNo()
    {
        $date = now()->format('Ymd');
            // wingo
            $gamesNo1 = $date . "01" . "0001";
    		$gamesNo2 = $date . "02" . "0001";
    		$gamesNo3 = $date . "03" . "0001";
    		$gamesNo4 = $date . "04" . "0001";
    		// trx
    		$gamesNo6 = $date . "06" . "0001";
    		$gamesNo7 = $date . "07" . "0001";
    		$gamesNo8 = $date . "08" . "0001";
    		$gamesNo9 = $date . "09" . "0001";
    		// D & T
    		$gamesNo10 = $date . "10" . "0001";
		 	$gamesNo11 = $date . "11" . "0001";
		 	$gamesNo12 = $date . "12" . "0001";
		 	$gamesNo13 = $date . "13" . "0001";
    		
       	    DB::table('betlogs')->where('game_id', 1)
                          ->update(['games_no' => $gamesNo1]);
    		
    		DB::table('betlogs')->where('game_id', 2)
                          ->update(['games_no' => $gamesNo2]);
    		
    		DB::table('betlogs')->where('game_id', 3)
                          ->update(['games_no' => $gamesNo3]);
    		
    		DB::table('betlogs')->where('game_id', 4)
                          ->update(['games_no' => $gamesNo4]);
                          
            DB::table('betlogs')->where('game_id', 6)
                          ->update(['games_no' => $gamesNo6]);
    		
    		DB::table('betlogs')->where('game_id', 7)
                          ->update(['games_no' => $gamesNo7]);
    		
    		DB::table('betlogs')->where('game_id', 8)
                          ->update(['games_no' => $gamesNo8]);
    		
    		DB::table('betlogs')->where('game_id', 9)
                          ->update(['games_no' => $gamesNo9]);
    
            DB::table('betlogs')->where('game_id', 10)
                          ->update(['games_no' => $gamesNo10]);
		 
		 	DB::table('betlogs')->where('game_id', 11)
                          ->update(['games_no' => $gamesNo11]);
		 
		 	DB::table('betlogs')->where('game_id', 12)
                          ->update(['games_no' => $gamesNo12]);
		 
		 	DB::table('betlogs')->where('game_id', 13)
                          ->update(['games_no' => $gamesNo13]);
		 
    }



	

	  
	
}