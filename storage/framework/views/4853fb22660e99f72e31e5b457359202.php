<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

	include __DIR__ . '/db_info.php';
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <!-- basic -->
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!-- mobile metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="initial-scale=1, maximum-scale=1">
      <!-- site metas -->
      <title><?php echo data($conn,3)?> - Login</title>
      <meta name="keywords" content="">
      <meta name="description" content="">
      <meta name="author" content="">
      <!-- site icon -->
      <link rel="icon" href="images/fevicon.png" type="image/png" />
      <!-- bootstrap css -->
      <link rel="stylesheet" href="<?php echo e(asset('css/bootstrap.min.css')); ?>" />
      <!-- site css -->
      <link rel="stylesheet" href="<?php echo e(asset('style.css')); ?>" />
      <!-- responsive css -->
      <link rel="stylesheet" href="<?php echo e(asset('css/responsive.css')); ?>" />
      <!-- color css -->
      <link rel="stylesheet" href="<?php echo e(asset('css/colors.css')); ?>" />
      <!-- select bootstrap -->
      <link rel="stylesheet" href="<?php echo e(asset('css/bootstrap-select.css')); ?>" />
      <!-- scrollbar css -->
      <link rel="stylesheet" href="<?php echo e(asset('css/perfect-scrollbar.css')); ?>" />
      <!-- custom css -->
      <link rel="stylesheet" href="<?php echo e(asset('css/custom.css')); ?>" />
      <!-- calendar file css -->
      <link rel="stylesheet" href="<?php echo e(asset('js/semantic.min.css')); ?>" />
     
   </head>
   <body class="inner_page login">
      <div class="full_container">
         <div class="container">
            <div class="center verticle_center full_height">
               <div class="login_section">
                  <div class="logo_login">
                     <div class="center">
                        <h3 class="text-white"><?php echo data($conn,3)?>-Login</h3>
                        
                     </div>
                  </div>
                  <div class="login_form">
                       <?php if(session()->has('msg')): ?>
                     <div class="alert alert-<?php echo e(session('msg_class')); ?>">
                     <?php echo e(session('msg')); ?>

                     </div>
                     <?php endif; ?>
                     <form  action="<?php echo e(route('auth.login')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
     
                          <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                             <div class="alert alert-danger"><?php echo e($message); ?></div>
                          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <fieldset>
                           <div class="field">
                              <label class="label_field">Email Address</label>
                              <input type="email" name="email" placeholder="E-mail" required/>
                           </div>
							<div class="field">
                              <label class="label_field">Password</label>
                              <input type="password" name="password" placeholder="Password" required/>
                           </div>
						<!--	<div class="field">
								<label class="label_field">OTP</label>
								<input type="text" name="otp" placeholder="OTP" required />
								<a class="btn btn-success forgot" id="sendOTP">Send OTP</a>
							</div> -->
                           
						

                           <div class="field margin_0">
                           
                              <center><button class="main_bt">Login</button></center>
                           </div>
                        </fieldset>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- jQuery -->
	   
	   
	   
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


	   <script>
    $(document).ready(function() {
        $('#sendOTP').click(function() {
            $.ajax({
                url: 'https://root.winzy.app/api/sendSMS',
                type: 'GET',
                data: {
                    mobile: '9167027770'
                },
                success: function(response) {
                    // Handle the response
                    alert(response.message); // For demonstration, you can replace this with displaying the response in your desired format
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
     <!-- <script src="<?php echo e(asset('js/jquery.min.js')); ?>"></script> !-->
      <script src="<?php echo e(asset('js/popper.min.js')); ?>"></script>
      <script src="<?php echo e(asset('js/bootstrap.min.js')); ?>"></script>
      <!-- wow animation -->
      <script src="<?php echo e(asset('js/animate.js')); ?>"></script>
      <!-- select country -->
      <script src="<?php echo e(asset('js/bootstrap-select.js')); ?>"></script>
      <!-- nice scrollbar -->
      <script src="<?php echo e(asset('js/perfect-scrollbar.min.js')); ?>"></script>
      <script>
         var ps = new PerfectScrollbar('#sidebar');
      </script>
      <!-- custom js -->
      <script src="<?php echo e(asset('js/custom.js')); ?>"></script>
   </body>
</html><?php /**PATH /home/u483840386/domains/globalbet24.club/public_html/root/resources/views/admin/login.blade.php ENDPATH**/ ?>