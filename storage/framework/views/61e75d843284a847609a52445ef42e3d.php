<?php $__env->startSection('admin'); ?>

 <!-- dashboard inner -->
 <div class="midde_cont">
    <div class="container-fluid">
       <div class="row column_title">
          <div class="col-md-12">
             <div class="page_title">
                <h2>Dashboard</h2>
             </div>
          </div>
       </div>
		
       <div class="row">
	
    <div class="col-md-3 form-group">
    
    <form action="<?php echo e(route('dashboard')); ?>" method="get">
		<?php echo csrf_field(); ?>
      <div class="form-group">
        <label for="start_date">Start Date:</label>
        <input type="date" class="form-control" id="start_date" name="start_date">
      </div>
	</div>	
		   <div class="col-md-3 form-group">
      <div class="form-group">
        <label for="end_date">End Date:</label>
        <input type="date" class="form-control" id="end_date" name="end_date">
      </div>
			   </div>
		   <div class="col-md-2 form-group mt-4">
      <button type="submit" class="btn btn-success">Search</button>
			   <a href="https://root.winzy.app/dashboard" class=" btn btn-secondary">Reset</a>
			   </div>
    </form>
  
         </div>
       </div>
       <div class="row column1">
          <div class="col-md-6 col-lg-3">
             <div class="full counter_section margin_bottom_">
                <div class="couter_icon">
                   <div> 
                       <i class="fa fa-user yellow_color"></i>
                   </div>
                </div>
                <div class="counter_no">
                   <div>
                      
					   <p class="total_no"><?php echo e($users[0]->totaluser); ?></p>
                      <p class="head_couter">Total Player</p>
                   </div>
                </div>
             </div>
          </div>
		   
		    <div class="col-md-6 col-lg-3">
             <div class="full counter_section margin_bottom_">
                <div class="couter_icon">
                   <div> 
                      <i class="fa fa-user yellow_color"></i>
                   </div>
                </div>
                <div class="counter_no">
                   <div>
                      
					   <p class="total_no"><?php echo e($users[0]->activeuser); ?></p>
                      <p class="head_couter">Active Player</p>
                   </div>
                </div>
             </div>
          </div>
		   
         
          <div class="col-md-6 col-lg-3">
             <div class="full counter_section margin_bottom_30">
                <div class="couter_icon">
                   <div> 
					    <i class="fa fa-user yellow_color"></i>
                   </div>
                </div>
                <div class="counter_no">
                   <div>
                      <p class="total_no"><?php echo e($users[0]->todayuser); ?></p>
                      <p class="head_couter">Today User</p>
                   </div>
                </div>
             </div>
          </div>
          <div class="col-md-6 col-lg-3">
             <div class="full counter_section margin_bottom_30">
                <div class="couter_icon">
                   <div> 
                      <i class="fa fa-comments-o red_color"></i>
                   </div>
                </div>
                <div class="counter_no">
                   <div>
					   <p class="total_no"><?php echo e($users[0]->todayturnover); ?></p>
                     <p class="head_couter">Today Turnover</p>
                   </div>
                </div>
             </div>
          </div>
       </div>
       <div class="row column1">
         <div class="col-md-6 col-lg-3">
             <div class="full counter_section margin_bottom_30">
                <div class="couter_icon">
                   <div> 
                      <i class="fa fa-cloud-download green_color"></i>
                   </div>
                </div>
                <div class="counter_no">
                   <div>
                      <p class="total_no"><?php echo e($users[0]->total_turnover); ?></p>
                      <p class="head_couter">Total Turnover</p>
                   </div>
                </div>
             </div>
          </div>
		   
		   
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div> 
                     <i class="fa fa-clock-o blue1_color"></i>
                  </div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->totaldeposit); ?></p>
                      <p class="head_couter">Total Deposit</p>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div> 
                     <i class="fa fa-cloud-download green_color"></i>
                  </div>
               </div>
               <div class="counter_no">
                  <div>
					   <p class="total_no"><?php echo e($users[0]->tdeposit); ?></p>
                     <p class="head_couter">Today Deposit</p>
                     
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div> 
                     <i class="fa fa-comments-o red_color"></i>
                  </div>
               </div>
               <div class="counter_no">
                  <div>
					    <p class="total_no"><?php echo e($users[0]->totalwithdraw); ?></p>
                     <p class="head_couter">Total Withdrawl</p>
                   
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="row column1">
		  <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div> 
                      <i class="fa fa-clock-o blue1_color"></i>
                  </div>
               </div>
               <div class="counter_no">
                  <div>
                       <p class="total_no"><?php echo e($users[0]->tamount); ?></p>
                     <p class="head_couter">Today Withdrawl</p>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div> 
                     <i class="fa fa-comments-o red_color"></i>
                  </div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->totalfeedback); ?></p>
                     <p class="head_couter">FeedBack</p>
                  </div>
               </div>
            </div>
         </div>
		  
		   <div class="col-md-6 col-lg-3">
             <div class="full counter_section margin_bottom_30">
                <div class="couter_icon">
                   <div> 
                     <i class="fa fa-clock-o blue1_color"></i>
                   </div>
                </div>
                <div class="counter_no">
                   <div>
                      <p class="total_no"><?php echo e($users[0]->totalgames); ?></p>
                      <p class="head_couter">Total Games</p>
                   </div>
                </div>
             </div>
          </div>
		   <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div> 
                    <i class="fa fa-clock-o blue1_color"></i>
                  </div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->commissions); ?></p>
                     <p class="head_couter">Total Commission</p>
                  </div>
               </div>
            </div>
         </div>
         
      </div>
	 
	  <div class="row column1">
		  
		   <div class="col-md-6 col-lg-3">
             <div class="full counter_section margin_bottom_30">
                <div class="couter_icon">
                   <div> 
                     <i class="fa fa-clock-o blue1_color"></i>
                   </div>
                </div>
                <div class="counter_no">
                   <div>
                      <p class="total_no">00</p>
                      <p class="head_couter">P/L</p>
                   </div>
                </div>
             </div>
          </div>
		  
	  </div>
	 
       
       <!-- graph -->
       
       <!-- end graph -->
       
       
    </div>
    </div>
  <!-- end dashboard inner -->
</div>
</div>
</div>
    <?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u483840386/domains/globalbet24.club/public_html/root/resources/views/admin/index.blade.php ENDPATH**/ ?>