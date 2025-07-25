<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\CreateorderController;
// use App\Http\Controllers\WorkassignController;
use App\Http\Controllers\ProjectmaintenanceController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\RevenueController;  
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TrxAdminController;

use App\Http\Controllers\GiftController;
use App\Http\Controllers\PlinkoController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\WidthdrawlController;

use App\Http\Controllers\AdminPayinController;

use App\Http\Controllers\MlmlevelController;
use App\Http\Controllers\ColourPredictionController;
use App\Http\Controllers\AdminSettingController; 
use App\Http\Controllers\BannerController;
use App\Http\Controllers\AllBetHistoryController;
use App\Http\Controllers\BankDetailController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\UsdtDepositController;
use App\Http\Controllers\UsdtController;
use App\Http\Controllers\UsdtWidthdrawController;


//Aviator
use App\Http\Controllers\AviatorAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
//Admin Setting




//Aviator
//Route::get('/bet-history',[AviatorAdminController::class, 'bet_history'])->name('bet_history');

// Route::get('/result',[AviatorAdminController::class, 'result_index'])->name('result');
// Route::post('/result-update', [AviatorAdminController::class, 'result_update'])->name('result.update');
// Route::post('/number-update', [AviatorAdminController::class, 'result_number_update'])->name('number.update');

Route::get('/offer', [NoticeController::class, 'offer'])->name('offer');
Route::get('/offer/edit/{id}', [NoticeController::class, 'edit'])->name('offer.edit');
Route::put('/offer/update/{id}', [NoticeController::class, 'update'])->name('offer.update');




 Route::get('/aviator/{game_id}',[AviatorAdminController::class, 'aviator_prediction_create'])->name('result');
	 Route::get('/aviator_fetchs/{game_id}', [AviatorAdminController::class, 'aviator_fetchDatacolor'])->name('aviator_fetch_data');

     Route::post('/aviator_store',[AviatorAdminController::class, 'aviator_store'])->name('aviator.store');
     Route::post('/aviator_percentage_update', [AviatorAdminController::class, 'aviator_update'])->name('aviator_percentage.update');
 Route::get('/bet-history/{game_id}', [AviatorAdminController::class, 'aviator_bet_history'])->name('bet_history');



     Route::get('/andar_bahar/{gameid}',[AndarbaharController::class, 'andarbahar_create'])->name('andarbahar');
	 Route::get('/andarbahar_fetch/{gameid}', [AndarbaharController::class, 'fetchDatas'])->name('fetch_data_ab');

     Route::post('/andarbahar-store',[AndarbaharController::class, 'andarbahar_store'])->name('andarbahar.store');
     Route::post('/percentage-update', [AndarbaharController::class, 'andarbahar_update'])->name('percentage.update');


//End Aviator
Route::get('/clear', function() {

   Artisan::call('cache:clear');
   Artisan::call('config:clear');
   Artisan::call('config:cache');
   Artisan::call('view:clear');

   return "Cleared!";

});


 
Route::get('/',[LoginController::class,'login'])->name('login');
Route::post('/login',[LoginController::class,'auth_login'])->name('auth.login');

Route::get('/dashboard',[LoginController::class,'dashboard'])->name('dashboard');
 
        
 

// register
    Route::get('/register',[LoginController::class,'register_create'])->name('register');
    Route::post('/register',[LoginController::class,'register_store'])->name('register.store');
    
// Route::middleware(['auth'])->group(function () {


    Route::get('/auth-logout',[LoginController::class,'logout'])->name('auth.logout');
     Route::get('/change_password',[LoginController::class,'password_index'])->name('change_password');
     Route::post('/change_password',[LoginController::class,'password_change'])->name('change_pass.update');
     
     
     
     ///Admin Payin Route ///
     Route::any('/admin_payin-{id}',[AdminPayinController::class,'admin_payin'])->name('admin_payin.store');
  

    Route::get('/bank_details',[BankDetailController::class, 'bankdetails'])->name('bankdetails');
    Route::post('/edit_bank_details',[BankDetailController::class, 'edit_bank_details'])->name('edit_bank_details');


    // Route::post('/city-data', function () {
    //     return view('city');
    // })->name('city-data');
    
    Route::get('/gift-index',[GiftController::class, 'index'])->name('gift');
    Route::post('/gift-store',[GiftController::class, 'gift_store'])->name('gift.store');
    Route::get('/giftredeemed',[GiftController::class, 'giftredeemed'])->name('giftredeemed');

   //Banner
    Route::get('/banner-index',[BannerController::class, 'index'])->name('banner');
 Route::post('/banner-store',[BannerController::class, 'banner_store'])->name('banner.store');
 Route::get('/banner-delete-{id}',[BannerController::class, 'banner_delete'])->name('banner.delete');
  Route::post('/banner-update-{id}', [BannerController::class, 'banner_update'])->name('banner.update');  
    
    Route::get('/attendance',[AttendanceController::class, 'attendance'])->name('attendance.index');
    Route::post('/attendance',[AttendanceController::class, 'attendance_store'])->name('attendance.store');
    Route::get('/attendance-delete-{id}',[AttendanceController::class, 'attendance_delete'])->name('attendance.delete');
    Route::post('/attendance-update-{id}', [AttendanceController::class, 'attendance_update'])->name('attendance.update');
    
    
    Route::get('/users',[UserController::class, 'user_create'])->name('users');
    Route::get('/user_detail-{id}',[UserController::class,'user_details'])->name('userdetail');
    
    Route::post('/password-update-{id}', [UserController::class, 'password_update'])->name('password.update');
    
    //  Route::post('/users',[UserController::class, 'user_store'])->name('users.store');
    Route::get('/users-delete-{id}',[UserController::class, 'delete'])->name('users.destroy');
    Route::get('/users-active-{id}', [UserController::class,'user_active'])->name('user.active');
    Route::get('/users-inactive-{id}',[UserController::class, 'user_inactive'])->name('user.inactive');
    Route::post('/wallet-store-{id}',[UserController::class, 'wallet_store'])->name('wallet.store');
     Route::post('/refer-store-{id}',[UserController::class, 'refer_id_store'])->name('refer_id.store');
    Route::post('/wallet/subtract/{id}', [UserController::class, 'wallet_subtract'])->name('wallet.subtract');

	Route::get('/users-mlm-{id}',[UserController::class, 'user_mlm'])->name('user.mlm');
	
	Route::get('/registerwithref/{id}',[UserController::class,'registerwithref'])->name('registerwithref');
	Route::post('/register_store-{referral_code}',[UserController::class,'register_store'])->name('user_register');
	
	

    
    
     Route::get('/colour_prediction/{gameid}',[ColourPredictionController::class, 'colour_prediction_create'])->name('colour_prediction');
	 Route::get('/fetch/{gameid}', [ColourPredictionController::class, 'fetchData'])->name('fetch_data');

     Route::post('/colour_prediction-store',[ColourPredictionController::class, 'store'])->name('colour_prediction.store');
     Route::post('/percentage-update', [ColourPredictionController::class, 'color_update'])->name('percentage_color.update');

    Route::post('/category',[CategoryController::class, 'category_store'])->name('category.store');
    Route::get('/category-active-{id}', [CategoryController::class,'category_active'])->name('category.active');
    Route::get('/category-inactive-{id}',[CategoryController::class, 'category_inactive'])->name('category.inactive');
    Route::get('/category-delete-{id}',[CategoryController::class, 'category_delete'])->name('category.delete');
    Route::post('/category-update-{id}', [CategoryController::class, 'category_update'])->name('category.update');
    
       
    Route::get('/mlm_level',[MlmlevelController::class, 'mlmlevel_create'])->name('mlmlevel');
    Route::post('/mlm_level',[MlmlevelController::class, 'mlmlevel_store'])->name('mlmlevel.store');
    
    Route::get('/mlm_level-active-{id}', [MlmlevelController::class,'mlmlevel_active'])->name('mlmlevel.active');
    Route::get('/mlm_level-inactive-{id}',[MlmlevelController::class, 'mlmlevel_inactive'])->name('mlmlevel.inactive');
    Route::get('/mlm_level-delete-{id}',[MlmlevelController::class, 'mlmlevel_delete'])->name('mlmlevel.delete');
    Route::post('/mlm_level-update-{id}', [MlmlevelController::class, 'mlmlevel_update'])->name('mlmlevel.update');
    
    
    Route::get('/orderlist',[OrderController::class,'order_create'])->name('order.list');
    Route::post('/orderlist-store',[OrderController::class,'order_store'])->name('order.store');
    Route::get('/orderlist-active-{id}', [OrderController::class,'order_active'])->name('order.active');
    Route::get('/orderlist-inactive-{id}',[OrderController::class, 'order_inactive'])->name('order.inactive');
    Route::get('/orderlist-delete-{id}',[OrderController::class, 'order_delete'])->name('order.delete');
    Route::post('/orderlist-update-{id}', [OrderController::class, 'order_update'])->name('order.update');
    
    
    Route::get('/Create-orderlist',[CreateorderController::class,'createorder_index'])->name('create_orderlist');
    Route::post('/Create-orderlist',[CreateorderController::class,'createorder_store'])->name('create_orderlist.store');
    Route::get('/Create-orderlist-active-{id}', [CreateorderController::class,'create_order_active'])->name('create_order.active');
    Route::get('/Create-orderlist-inactive-{id}',[CreateorderController::class, 'create_order_inactive'])->name('create_order.inactive');
    Route::get('/Create-orderlist-delete-{id}',[CreateorderController::class, 'createorder_delete'])->name('create_order.delete');
    Route::post('/Create-orderlist-update-{id}', [CreateorderController::class, 'createorder_update'])->name('create_order.update');
    
    
    
    
    Route::get('/setting',[SettingController::class,'setting_index'])->name('setting');

     Route::get('/view-{id}',[SettingController::class,'view'])->name('view');
    Route::post('/setting',[SettingController::class,'setting_store'])->name('setting.store');
    Route::get('/setting-active-{id}', [SettingController::class,'setting_active'])->name('setting.active');
    Route::get('/setting-inactive-{id}',[SettingController::class, 'setting_inactive'])->name('setting.inactive');
    Route::get('/setting-delete-{id}',[SettingController::class, 'setting_delete'])->name('setting.delete');
    Route::post('/setting-update-{id}', [SettingController::class, 'setting_update'])->name('setting.update');
    
    Route::post('/update-status/{id}', [SettingController::class, 'updateStatus'])->name('update.status');

    
     Route::get('/support_setting',[SettingController::class,'support_setting'])->name('support_setting');
    Route::post('/supportsetting-update-{id}', [SettingController::class, 'supportsetting_update'])->name('supportsetting.update');
    
    Route::get('/notification',[SettingController::class,'notification'])->name('notification');
    Route::get('/view_notification-{id}',[SettingController::class,'view_notification'])->name('view_notification');
Route::post('/notification-update-{id}', [SettingController::class, 'notification_update'])->name('notification.update');
Route::post('/notification_store', [SettingController::class, 'notification_store'])->name('notification_store');
Route::get('/add_notification',[SettingController::class,'add_notification'])->name('add_notification');

    Route::get('/deposit-{id}',[DepositController::class,'deposit_index'])->name('deposit');
    Route::post('/deposit',[DepositController::class,'deposit_store'])->name('deposit.store');
    Route::get('/deposit-active-{id}', [DepositController::class,'deposit_active'])->name('deposit.active');
    Route::get('/deposit-inactive-{id}',[DepositController::class, 'deposit_inactive'])->name('deposit.inactive');
    Route::get('/deposit-delete/{id}',[DepositController::class, 'deposit_delete'])->name('deposit.delete');
    Route::post('/deposit-update-{id}', [DepositController::class, 'deposit_update'])->name('deposit.update');
    Route::post('/update-setting',[SettingController::class,'update_setting'])->name('update_setting');
     Route::get('/deposit-delete-all',[DepositController::class, 'deposit_delete_all'])->name('deposit.delete_all'); 
     Route::get('/deposit_reject/{id}', [DepositController::class, 'deposit_reject'])->name('deposit_reject');
    
     Route::get('/deposit_success/{id}',[DepositController::class,'payin_success'])->name('payin_success');
    //  Route::get('/deposit-{id}',[DepositController::class,'deposit_index'])->name('deposit');
    //  Route::get('/deposit_success/{id}',[DepositController::class,'payin_success'])->name('payin_success');
    
    
    
    Route::get('/feedback',[FeedbackController::class,'feedback_index'])->name('feedback');
    Route::post('/feedback',[FeedbackController::class,'feedback_store'])->name('feedback.store');
    Route::get('/feedback-delete-{id}',[FeedbackController::class, 'feedback_delete'])->name('feedback.delete');
    Route::post('/feedback-update-{id}', [FeedbackController::class, 'feedback_update'])->name('feedback.update');
    
    Route::get('/widthdrawl/{id}',[WidthdrawlController::class,'widthdrawl_index'])->name('widthdrawl');
    Route::post('/widthdrawl',[WidthdrawlController::class,'widthdrawl_store'])->name('widthdrawl.store');
    Route::get('/widthdrawl-delete-{id}',[WidthdrawlController::class, 'widthdrawl_delete'])->name('widthdrawl.delete');
    Route::post('/widthdrawl-update-{id}', [WidthdrawlController::class, 'widthdrawl_update'])->name('widthdrawl.update');
    Route::post('/widthdrawl-active-{id}', [WidthdrawlController::class,'success'])->name('widthdrawl.success');
    Route::post('/only-success-{id}', [WidthdrawlController::class, 'only_success'])->name('widthdrawl.only_success_id');
    Route::post('/widthdrawl/reject/{id}', [WidthdrawlController::class, 'reject'])->name('widthdrawl.reject');
     Route::post('/widthdrawl-upi-{id}', [WidthdrawlController::class,'success_by_upi'])->name('widthdrawl.upi');
     //////////////////////////////
     
     //Route::post('/payout/success/{id}', [WidthdrawlController::class, 'successbycamlenio'])->name('widthdrawl.success');
     
    //////////////////////
    //Route::get('/withdraw_response', [WidthdrawlController::class,'withdraw_response']);
    
    Route::get('/widthdraw/success/payout/{id}',[WidthdrawlController::class,'sendEncryptedPayoutRequest'])->name('withdraw.success');
    Route::get('/indiaonlin_payout-{id}', [WidthdrawlController::class,'indiaonlin_payout'])->name('indiaonlin_payout.success');
    Route::post('/widthdrawl-all-success',[WidthdrawlController::class, 'all_success'])->name('widthdrawl.all_success');


    Route::get('/complaint',[ComplaintController::class,'complaint_index'])->name('complaint');
    Route::post('/complaint',[ComplaintController::class,'complaint_store'])->name('complaint.store');
    Route::get('/complaint-delete-{id}',[ComplaintController::class, 'complaint_delete'])->name('complaint.delete');
    Route::post('/complaint-update-{id}', [ComplaintController::class, 'complaint_update'])->name('complaint.update');
    
    Route::get('/revenue',[RevenueController::class,'revenue_create'])->name('revenue');
    Route::post('/revenue',[RevenueController::class,'revenue_store'])->name('revenue.store');
    Route::get('/revenue-delete-{id}',[RevenueController::class, 'revenue_delete'])->name('revenue.delete');
    Route::post('/revenue-update-{id}', [RevenueController::class, 'revenue_update'])->name('revenue.update');
    
    //plinko
   Route::get('/plinko-index',[PlinkoController::class, 'index'])->name('plinko');
   Route::get('/all_bet_history/{id}',[AllBetHistoryController::class, 'all_bet_history'])->name('all_bet_history');
   // routes/web.php

  
 Route::post('/referral/update/{id}', [UserController::class, 'updatereferral'])->name('referral.update');
 
 
 
   ///// USDT Deposit ///////
    Route::get('/usdt_deposit/{id}',[UsdtDepositController::class,'usdt_deposit_index'])->name('usdt_deposit');
    Route::post('/usdt_deposit',[UsdtDepositController::class,'usdt_deposit_store'])->name('usdt_deposit.store');

    Route::get('/usdt_success/{id}',[UsdtDepositController::class,'usdt_success'])->name('usdt_success');
    Route::get('/usdt_reject/{id}',[UsdtDepositController::class,'usdt_reject'])->name('usdt_reject');

    Route::get('/usdt_deposit-active-{id}', [UsdtDepositController::class,'usdt_deposit_active'])->name('usdt_deposit.active');
    Route::get('/usdt_deposit-inactive-{id}',[UsdtDepositController::class, 'usdt_deposit_inactive'])->name('usdt_deposit.inactive');
    Route::get('/usdt_deposit-delete-{id}',[UsdtDepositController::class, 'usdt_deposit_delete'])->name('usdt_deposit.delete');
    Route::post('/usdt_deposit-update-{id}', [UsdtDepositController::class, 'usdt_deposit_update'])->name('usdt_deposit.update');


	///// USDT Withdraw ///////
    Route::get('/usdt_widthdrawl/{id}',[UsdtWidthdrawController::class,'usdt_widthdrawl_index'])->name('usdt_widthdrawl');
    Route::post('/usdt_widthdrawl',[UsdtWidthdrawController::class,'usdt_widthdrawl_store'])->name('usdt_widthdrawl.store');
    Route::get('/usdt_widthdrawl-delete-{id}',[UsdtWidthdrawController::class, 'usdt_widthdrawl_delete'])->name('usdt_widthdrawl.delete');
    Route::post('/usdt_widthdrawl-update-{id}', [UsdtWidthdrawController::class, 'usdt_widthdrawl_update'])->name('usdt_widthdrawl.update');
    //Route::get('/usdt_withdraw/{id}', [UsdtWidthdrawController::class,'usdt_success'])->name('usdt_widthdrawl.success');
    Route::post('/usdt_withdraw/{id}', [UsdtWidthdrawController::class, 'usdt_success'])->name('usdt_widthdrawl.success');
    Route::post('/usdt_widthdrawl-inactive-{id}',[UsdtWidthdrawController::class, 'usdt_reject'])->name('usdt_widthdrawl.reject');
    Route::post('/usdt_widthdrawl-all-success',[UsdtWidthdrawController::class, 'usdt_all_success'])->name('usdt_widthdrawl.all_success');




	Route::get('/usdt_qr',[UsdtController::class, 'usdt_view'])->name('usdtqr');
   // Route::post('/update_usdtqr', [UsdtController::class, 'update_usdtqr'])->name('usdtqr.update');
   Route::post('/update_usdtqr/{id}', [UsdtController::class, 'update_usdtqr'])->name('usdtqr.update');
 
 
 


    

// });      avitor game view





