<div class="full_container">
         <div class="inner_container">
            <!-- Sidebar  -->
            <nav id="sidebar">
               <div class="sidebar_blog_1">
                  <div class="sidebar-header">
                     <div class="logo_section">
                        <a href="index.html"><img class="logo_icon img-responsive" src="images/logo/logo_icon.png" alt="#" /></a>
                     </div>
                  </div>
                  <div class="sidebar_user_info">
                     <div class="icon_setting"></div>
                     <div class="user_profle_side">
                        <div class="user_img"><img class="img-responsive" src="https://root.globalbet24.live/public/images/layout_img/user_img.jpg" alt="#" /></div>
                        <div class="user_info">
                           <h6>Admin</h6>
                           <p><span class="online_animation"></span> Online</p>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="sidebar_blog_2">
                  <h4>General</h4>
                  <ul class="list-unstyled components">
                     
                     <li><a href="<?php echo e(route('dashboard')); ?>"><i class="fa fa-dashboard yellow_color"></i> <span>Dashboard</span></a></li>
                     <li><a href="<?php echo e(route('attendance.index')); ?>"><i class="fa fa-clock-o purple_color2"></i> <span>Attendance</span></a></li>
                     <li><a href="<?php echo e(route('users')); ?>"><i class="fa fa-user orange_color"></i> <span>Players</span></a></li>
                     
                     
                     <li><a href="<?php echo e(route('mlmlevel')); ?>"><i class="fa fa-list red_color"></i> <span>MlM Levels</span></a></li>
                     <li><a href="<?php echo e(route('bankdetails')); ?>"><i class="fa fa-file blue1_color"></i> <span>Bank Details</span></a></li>
                     
                     
                     <!--<?php-->
                     <!--    $colourpredictions = DB::select("SELECT * FROM `game_settings` LIMIT 9;");-->
                     <!--?>-->
                     
                     <?php
    // First 4 records (1,2,3,4)
    $firstPart = DB::select("SELECT * FROM `game_settings` LIMIT 4");

    // Skip 5th and get next 4 records (6,7,8,9)
    $secondPart = DB::select("SELECT * FROM `game_settings` LIMIT 4 OFFSET 5");

    // Merge both arrays
    $colourpredictions = array_merge($firstPart, $secondPart);
?>

                     
                     <li>
                        <a href="#apps" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-object-group blue2_color"></i> <span>Colour_prediction</span></a>
                        <ul class="collapse list-unstyled" id="apps">
                           <?php $__currentLoopData = $colourpredictions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                           <li><a href="<?php echo e(route('colour_prediction',$item->id)); ?>"> <span><?php echo e($item->name); ?></span></a></li>
                           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                     </li>
     <?php if ($__env->exists('admin.body.aviator_sidebar')) echo $__env->make('admin.body.aviator_sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php if ($__env->exists('admin.body.dragon_sidebar')) echo $__env->make('admin.body.dragon_sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      
       <?php if ($__env->exists('admin.body.andarbahar_sidebar')) echo $__env->make('admin.body.andarbahar_sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
       <!--<?php if ($__env->exists('admin.body.Headtail_sidebar')) echo $__env->make('admin.body.Headtail_sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>-->
					 
                     
					  <!--<li><a href="<?php echo e(route('plinko')); ?>"><i class="fa fa-gamepad purple_color2"></i> <span>Plinko</span></a></li>-->
					  
					   <?php
                         $game_id = DB::select("SELECT * FROM `game_settings` where status=0 LIMIT 11;");
                       ?>
					  
					  <li>
                        <a href="#apps-xy" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-object-group blue2_color"></i> <span>Bet History</span></a>
                        <ul class="collapse list-unstyled" id="apps-xy">
							 <?php $__currentLoopData = $game_id; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><a href="<?php echo e(route('all_bet_history',$itemm->id)); ?>"> <span><?php echo e($itemm->name); ?></span></a></li>
							 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                     </li>
					  
					   <li><a href="<?php echo e(route('offer')); ?>"><i class="fa fa-table purple_color2"></i> <span>Offer</span></a></li>
                     <li><a href="<?php echo e(route('gift')); ?>"><i class="fa fa-table purple_color2"></i> <span>Gift</span></a></li>
					  <li><a href="<?php echo e(route('giftredeemed')); ?>"><i class="fa fa-table purple_color2"></i> <span>Gift Redeemed History</span></a></li>
                    <li><a href="<?php echo e(route('banner')); ?>"><i class="fa fa-picture-o" aria-hidden="true"></i> <span> Activity & Banner</span></a></li> 
                     <li><a href="<?php echo e(route('feedback')); ?>"><i class="fa fa-file blue1_color"></i> <span>FeedBack</span></a></li>
                     
                     
                     
					   <li>
     <a href="#app13" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-tasks  green_color"></i>            <span>Deposit</span></a>
     <ul class="collapse list-unstyled" id="app13">
   <li><a href="<?php echo e(route('deposit', 1)); ?>">Pending</a></li>
<li><a href="<?php echo e(route('deposit', 2)); ?>">Success</a></li>
<li><a href="<?php echo e(route('deposit',3)); ?>">Reject</a></li>


     </ul>
  </li>
					  
					   <li>
     <a href="#app11" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-wrench purple_color2"></i>            <span>Withdrawal</span></a>
     <ul class="collapse list-unstyled" id="app11">
   <li><a href="<?php echo e(route('widthdrawl', 1)); ?>">Pending</a></li>
<li><a href="<?php echo e(route('widthdrawl', 2)); ?>">Approved</a></li>
<li><a href="<?php echo e(route('widthdrawl',3)); ?>">Reject</a></li>
<!--<li><a href="<?php echo e(route('widthdrawl', 4)); ?>">Successfull</a></li>-->
<!--<li><a href="<?php echo e(route('widthdrawl',5)); ?>">Failed</a></li>-->


     </ul>
  </li>
  
  
   <li><a href="<?php echo e(route('usdtqr')); ?>"><i class="fa fa-table purple_color2"></i> 
<span>USDT QR Code</span></a></li>
                                          <li>
     <a href="#app20" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
<i class="fa fa-tasks  green_color"></i><span>USDT Deposit</span></a>
     <ul class="collapse list-unstyled" id="app20">
   <li><a href="<?php echo e(route('usdt_deposit', 1)); ?>">Pending</a></li>
<li><a href="<?php echo e(route('usdt_deposit', 2)); ?>">Success</a></li>
<li><a href="<?php echo e(route('usdt_deposit',3)); ?>">Reject</a></li>


     </ul>
  </li>

                                           <li>
     <a href="#app21" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
<i class="fa fa-wrench purple_color2"></i>            <span>USDT Withdrawal</span></a>
     <ul class="collapse list-unstyled" id="app21">
   <li><a href="<?php echo e(route('usdt_widthdrawl', 1)); ?>">Pending</a></li>
<li><a href="<?php echo e(route('usdt_widthdrawl', 2)); ?>">Success</a></li>
<li><a href="<?php echo e(route('usdt_widthdrawl',3)); ?>">Reject</a></li>


     </ul>
  </li>
  
				
					  <li><a href="<?php echo e(route('notification')); ?>"><i class="fa fa-bell  yellow_color"></i> <span>Notification</span></a></li>
                     <li><a href="<?php echo e(route('setting')); ?>"><i class="fa fa-info-circle  yellow_color"></i> <span>Setting</span></a></li>
					  <li><a href="<?php echo e(route('support_setting')); ?>"><i class="fa fa-info-circle  yellow_color"></i> <span>Support Setting </span></a></li> 
                      <li><a href="<?php echo e(route('change_password')); ?>"><i class="fa fa-warning red_color"></i> <span>Change Password</span></a></li>
                     <li><a href="<?php echo e(route('auth.logout')); ?>"><i class="fa fa-line-chart yellow_color"></i> <span>Logout</span></a></li>
                    
                     
                  </ul>
               </div>
            </nav>
            <!-- end sidebar --><?php /**PATH /home/u483840386/domains/globalbet24.club/public_html/root/resources/views/admin/body/sidebar.blade.php ENDPATH**/ ?>