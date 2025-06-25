<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PublicApiController;
use App\Http\Controllers\Api\PayinController;
use App\Http\Controllers\Api\GameApiController;
use App\Http\Controllers\Api\AgencyPromotionController;
use App\Http\Controllers\Api\VipController;
use App\Http\Controllers\Api\ZiliApiController;
use App\Http\Controllers\Api\{TrxApiController,IfscApiController};

use App\Http\Controllers\Api\AviatorApiController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/getUrlIp',[PublicApiController::class,'getUrlIp']);

// PublicApiController
//Route::get('/gameSerialNo',[GameApiController::class,'gameSerialNo']);

Route::get('get-ifsc-details', [IfscApiController::class, 'getIfscDetails']);

Route::get('/getAllNotices',[PublicApiController::class,'getAllNotices']);

Route::post('/login',[PublicApiController::class,'login']);
Route::post('/register',[PublicApiController::class,'register']);
Route::post('/country',[PublicApiController::class,'country']);
Route::post('/forget_pass',[PublicApiController::class,'forget_pass']);
Route::get('/profile',[PublicApiController::class,'profile']);
Route::post('/update_profile',[PublicApiController::class,'update_profile']);
Route::get('/slider_image_view',[PublicApiController::class,'slider_image_view']);
Route::get('/deposit_history',[PublicApiController::class,'deposit_history']);
Route::get('/withdraw_history',[PublicApiController::class,'withdraw_history']);
Route::get('/privacy_policy',[PublicApiController::class,'Privacy_Policy']);
Route::get('/about_us',[PublicApiController::class,'about_us']);
Route::get('/Terms_Condition',[PublicApiController::class,'Terms_Condition']);
Route::get('/contact_us',[PublicApiController::class,'contact_us']);
Route::get('/support',[PublicApiController::class,'support']);
Route::get('/attendance_List',[PublicApiController::class,'attendance_List']);
Route::get('/image_all',[PublicApiController::class,'image_all']);
Route::get('/transaction_history_list',[PublicApiController::class,'transaction_history_list']);
Route::get('/transaction_history',[PublicApiController::class,'transaction_history']);
Route::get('/Status_list',[PublicApiController::class,'Status_list']);
Route::get('/pay_modes',[PublicApiController::class,'pay_modes']);
Route::post('/add_account',[PublicApiController::class,'add_account']);
Route::get('/Account_view',[PublicApiController::class,'Account_view']);
Route::post('/withdraw',[PublicApiController::class,'withdraw']);
Route::get('/notification',[PublicApiController::class,'notification']);
Route::post('/feedback',[PublicApiController::class,'feedback']);
Route::post('/gift_cart_apply',[PublicApiController::class,'giftCartApply']);
Route::get('/gift_redeem_list',[PublicApiController::class,'claim_list']);
Route::get('/customer_service',[PublicApiController::class,'customer_service']);
Route::post('/update_avatar',[PublicApiController::class,'update_profile']);
Route::post('/changePassword',[PublicApiController::class,'changePassword']);
Route::post('/main_wallet_transfer',[PublicApiController::class,'main_wallet_transfer']);
Route::get('/activity_rewards',[PublicApiController::class,'activity_rewards']);
Route::Post('/activity_rewards_claim',[PublicApiController::class,'activity_rewards_claim']);

Route::get('/activity_rewards_history',[PublicApiController::class,'activity_rewards_history']);
Route::get('/invitation_bonus_list',[PublicApiController::class,'invitation_bonus_list']);
Route::get('/Invitation_reward_rule',[PublicApiController::class,'Invitation_reward_rule']);
Route::get('/Invitation_records',[PublicApiController::class,'Invitation_records']);
Route::get('/extra_first_deposit_bonus',[PublicApiController::class,'extra_first_deposit_bonus']);
Route::post('/extra_first_deposit',[PublicApiController::class,'extra_first_deposit']);
Route::get('/attendance_List',[PublicApiController::class,'attendance_List']);
Route::get('/attendance_history',[PublicApiController::class,'attendance_history']);
Route::post('/attendance_claim',[PublicApiController::class,'attendance_claim']);
Route::get('/level_getuserbyrefid',[PublicApiController::class,'level_getuserbyrefid']);
Route::get('/commission_details',[PublicApiController::class,'commission_details']);
Route::get('/all_rules',[PublicApiController::class,'all_rules']);
Route::get('/subordinate_userlist',[PublicApiController::class,'subordinate_userlist']);
Route::get('/betting_rebate',[PublicApiController::class,'betting_rebate']);
Route::get('/betting_rebate_history',[PublicApiController::class,'betting_rebate_history']);
Route::get('/version_apk_link', [PublicApiController::class, 'versionApkLink']);
Route::post('/extra_first_payin',[PublicApiController::class,'extra_first_payin']);
Route::get('/checkPayment1',[PublicApiController::class,'checkPayment1']);
Route::post('/invitation_bonus_claim',[PublicApiController::class,'invitation_bonus_claim']);
Route::get('/invitation_bonus_list_old',[PublicApiController::class,'invitation_bonus_list_old']);
Route::post('/usdtwithdraw',[PublicApiController::class,'usdtwithdraw']);
Route::get('/total_bet_details',[PublicApiController::class,'total_bet_details']);
Route::get('/getPaymentLimits',[PublicApiController::class,'getPaymentLimits']);
	
	


Route::get('/commission_distribution',[PublicApiController::class,'commission_distribution']);

//// VIP Routes////
Route::get('/vip_level',[VipController::class,'vip_level']);
Route::get('/vip_level_history',[VipController::class,'vip_level_history']);
Route::post('/add_money',[VipController::class,'receive_money']);

//// usdt Payin by sudhir ///

Route::post('/usdt_payin',[PublicApiController::class,'payin_usdt']);
Route::post('/payin_call_back',[PublicApiController::class,'payin_call_back']);

// Payin Controller

Route::post('/payin',[PayinController::class,'payin']);
Route::get('/checkPayment',[PayinController::class,'checkPayment']);
Route::get('/finixpay',[PayinController::class,'finixpay']);

Route::get('/camlenio',[PayinController::class,'camlenio']);
Route::post('/camleniopaycallback',[PayinController::class, 'camleniopaycallback']);

Route::get('/payin-successfully',[PayinController::class,'redirect_success'])->name('payin.successfully');
//Game Controller//
Route::controller(GameApiController::class)->group(function () {
     Route::post('/bets', 'bet');
     Route::post('/bets_new', 'bet_new');
      Route::post('/dragon_bet', 'dragon_bet');
       // Route::post('/dragon_bet_new', 'dragon_bet_new');
     Route::get('/win-amount', 'win_amount');
     Route::get('/results','results');
     Route::get('/last_five_result','lastFiveResults');
     Route::get('/last_result','lastResults');
     Route::post('/bet_history','bet_history');
     Route::get('/cron/{game_id}/','cron');
     Route::get('/get_results','get_result');
     
    
   // Route::post('/trx_result',[TrxApiController::class,'trx_result']);
   
   
    Route::get('/trx/result',[TrxApiController::class, 'trx_result_new']);
    Route::get('/trx/results',[TrxApiController::class, 'trx_results']);
    Route::get('/trx/results_by_periodno',[TrxApiController::class, 'get_result_by_periodno']);
    Route::get('/trx/update_result_cron',[TrxApiController::class, 'trx_cron_result_update']);


    
    // Plinko Game Route /////
    
    Route::post('/plinko_bet','plinkoBet');
    Route::post('/plinko_bet_new','plinkoBet_new');
    Route::get('/plinko_index_list','plinko_index_list');
    Route::get('/plinko_result','plinko_result');
    Route::get('/plinko_cron','plinko_cron');
    Route::post('/plinko_multiplier','plinko_multiplier'); 
});



Route::post('/aviator_bet',[AviatorApiController::class, 'aviator_bet']);
Route::get('/aviator_bet_new',[AviatorApiController::class, 'aviator_bet_new']);
Route::post('/aviator_cashout',[AviatorApiController::class, 'aviator_cashout']);
Route::post('/aviator_history',[AviatorApiController::class, 'aviator_history']);

Route::get('/aviator_last_five_result',[AviatorApiController::class, 'last_five_result']);
Route::get('/aviator_bet_cancel',[AviatorApiController::class, 'bet_cancel']);
// india Payin
Route::post('/userpayin',[PayinController::class, 'userpayin']);
Route::post('/callbackfunc',[PayinController::class, 'callbackfunc']);
Route::post('/callbackfunc_payout',[PayinController::class, 'callbackfunc_payout']);


//mine
Route::post('/mine_bet',[GameController::class, 'mine_bet']);
Route::post('/mine_cashout',[GameController::class, 'mine_cashout']);
Route::get('/mine_result',[GameController::class,'mine_result']);
//otp
Route::get('/sendSMS',[PublicApiController::class,'sendSMS']);
Route::get('/verifyOTP',[PublicApiController::class,'verifyOTP']);
Route::post('/updatePassword',[PublicApiController::class,'updatePassword']);

Route::controller(AgencyPromotionController::class)->group(function () {
    Route::get('/agency-promotion-data-{id}', 'promotion_data');
	Route::get('/new-subordinate', 'new_subordinate');
	Route::get('/tier', 'tier');
	Route::post('/subordinate-data','subordinate_data');
	Route::get('/turnovers','turnover_new');
	
});

 //// Zili Api ///
Route::post('/user_register',[ZiliApiController::class,'user_register']);  //not in use for registration
Route::post('/all_game_list',[ZiliApiController::class,'all_game_list']);
Route::post('/all_game_list_test',[ZiliApiController::class,'all_game_list_test']);
Route::post('/get_game_url',[ZiliApiController::class,'get_game_url']);
Route::post('/get_jilli_transactons_details',[ZiliApiController::class,'get_jilli_transactons_details']);
Route::post('/jilli_deduct_from_wallet',[ZiliApiController::class,'jilli_deduct_from_wallet']);
Route::post('/jilli_get_bet_history',[ZiliApiController::class,'jilli_get_bet_history']);
Route::post('/add_in_jilli_wallet ',[ZiliApiController::class,'add_in_jilli_wallet']);
Route::post('/update_main_wallet ',[ZiliApiController::class,'update_main_wallet']);
Route::post('/get_jilli_wallet ',[ZiliApiController::class,'get_jilli_wallet']);
Route::post('/update_jilli_wallet ',[ZiliApiController::class,'update_jilli_wallet']);
Route::post('/update_jilli_to_user_wallet ',[ZiliApiController::class,'update_jilli_to_user_wallet']);


Route::get('/test_get_user_info ',[ZiliApiController::class,'test_get_user_info']);
Route::get('/get-reseller-info/{manager_key?}',[ZiliApiController::class,'get_reseller_info']);


 Route::post('/Skillpay',[PayinController::class, 'Skillpay']);
 Route::post('/skillpaycallback',[PayinController::class, 'skillpaycallback']);
 Route::post('/skillpay_payin',[PayinController::class, 'skillpay_payin']);
 Route::get('/checkSkillPayOrderId',[PayinController::class, 'checkSkillPayOrderId']);
 
 Route::post('/manual_payin',[PayinController::class,'manual_payinn']);
 Route::post('/usdt_payin',[PayinController::class,'usdt_payin']);
 Route::get('/show_qr',[PayinController::class,'qr_view']);
 
 
 
 