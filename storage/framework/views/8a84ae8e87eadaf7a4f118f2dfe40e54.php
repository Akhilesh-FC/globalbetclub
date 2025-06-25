<?php $__env->startSection('admin'); ?>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>


<style>
#imageModal {
    position: fixed;
    top: 100px;
    left: 30;
    width: 50%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    display: none; /* Start hidden */
    justify-content: center;
    align-items: center;
}

#imageModal img {
    max-width: 90%;
    max-height: 80%;
    object-fit: contain;
}

#closeButton {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 30px;
    color: black;
    cursor: pointer;
}
</style>

<script>
function openModal(imageSrc) {
    // Set the image source for the modal dynamically
    document.getElementById("modalImage").src = imageSrc;
    document.getElementById("imageModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("imageModal").style.display = "none";
}
</script>







<div class="container-fluid">
    <div class="row">
<div class="col-md-12">
    <div class="white_shd full margin_bottom_30">
       <div class="full graph_head">
          <div class="heading1 margin_0 d-flex">
             <h2>Deposit List</h2>
             
          </div>
       </div>
       <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
             <table id="example" class="table table-striped" style="width:100%">
                <thead class="thead-dark">
                   <tr>
                      <th>Id</th>
					   <th>User Id</th>
                      <th>User Name</th>
					  <th>Mobile</th>
                      <th>Order Id</th>
                      <th>Amount</th>
					  <th>Proof</th>
                      <th>Status</th>
                      <th>Date</th>
                   </tr>
                </thead>
                <tbody>
                  <?php $__currentLoopData = $deposits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                   <tr>
                      <td><?php echo e($item->id); ?></td>
					  <td><?php echo e($item->userid); ?></td>
                      <td><?php echo e($item->uname); ?></td>
					  <td><?php echo e($item->mobile); ?></td>
                      <td><?php echo e($item->order_id); ?></td>
                      <td><?php echo e($item->cash); ?></td>
					   <td>
                                        <!-- Pass the image source dynamically to the modal -->
                                        <img src="<?php echo e(URL::asset($item->typeimage)); ?>" width="50px" height="50px" onclick="openModal('<?php echo e(URL::asset($item->typeimage)); ?>')">
                                    </td>
					   <td>
                                        <?php if($item->status == 1): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Pending
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" href="<?php echo e(route('payin_success', $item->id)); ?>">Success</a>
                                                <a class="dropdown-item" href="<?php echo e(route('usdt_reject', $item->id)); ?>">Reject</a>
                                            </div>
                                            
                                        </div>
                                        <?php elseif($item->status == 2): ?>
                                        <button class="btn btn-success">Success</button>
                                        <?php elseif($item->status == 3): ?>
                                        <button class="btn btn-danger">Reject</button>
                                        <?php else: ?>
                                        <span class="badge badge-secondary">Unknown Status</span>
                                        <?php endif; ?>
                                    </td>
                      <td><?php echo e($item->created_at); ?></td>
                     
                      
                      
                    
                   </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
             </table>
          </div>
       </div>
    </div>
 </div>
</div>
</div> 

<!-- Modal -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


<!-- Modal -->
<div id="imageModal">
    <span id="closeButton" onclick="closeModal()">×</span>
    <img id="modalImage" src="" alt="Enlarged image">
</div>

 <?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u483840386/domains/globalbet24.club/public_html/root/resources/views/ManualPayment/index.blade.php ENDPATH**/ ?>