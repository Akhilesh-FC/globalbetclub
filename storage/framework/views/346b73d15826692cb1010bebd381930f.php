 <?php if(Session::has('id')){

}else{
	
	header("Location: https://root.globalbet24.live/");
            die; 

} 
      
?>
<div id="content">
    <!-- topbar -->
    <div class="topbar">
       <nav class="navbar navbar-expand-lg navbar-light">
          <div class="full">
             <button type="button" id="sidebarCollapse" class="sidebar_toggle"><i class="fa fa-bars"></i></button>
             <div class="logo_section">
                <h3 class="img-responsive text-white mt-2 ml-2" ><?php echo data($conn,4)?></h3>
                
             </div>
             <div class="right_topbar">
                <div class="icon_info">
                   
                   <ul class="user_profile_dd">
                      <li>
                         <a class="dropdown-toggle" data-toggle="dropdown"><img class="img-responsive rounded-circle" src="https://root.globalbet24.live/public/images/layout_img/user_img.jpg" alt="#" /><span class="name_user">Admin</span></a>
                         <div class="dropdown-menu">
                            <!--<a class="dropdown-item" href="#">My Profile</a>-->
                            
                            <a class="dropdown-item" href="<?php echo e(route('auth.logout')); ?>"><span>Log Out</span> <i class="fa fa-sign-out"></i></a>
                         </div>
                      </li>
                   </ul>
                </div>
             </div>
          </div>
       </nav>
    </div>
    <!-- end topbar -->


 <?php /**PATH /home/u483840386/domains/globalbet24.club/public_html/root/resources/views/admin/body/header.blade.php ENDPATH**/ ?>