<?php $__env->startSection('admin'); ?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
      <div class="white_shd full margin_bottom_30">
        <div class="full graph_head">
          <div class="heading1 margin_0 d-flex">
            <h2>Banner List</h2>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModalCenter" style="margin-left:600px;">Add Banner</button>
          </div>
        </div>
        <div class="table_section padding_infor_info">
          <div class="table-responsive-sm">
            <table id="example" class="table table-striped" style="width:100%">
              <thead class="thead-dark">
                <tr>
                  <th>Sr.No</th>
                  <th>Name</th>
                  <th>Image</th>
					<th>Activity Image</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php $__currentLoopData = $banner; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                  <td><?php echo e($item->id); ?></td>
                  <td><?php echo e($item->title); ?></td>
                  <td><img src="<?php echo e(URL::asset($item->image)); ?>" width="50 px" height="50 px"></td>
					<td><img src="<?php echo e(URL::asset($item->activity_image)); ?>" width="50 px" height="50 px"></td>
                  <td>
                    <i class="fa fa-edit mt-1" data-toggle="modal" data-target="#exampleModalCenterupdate1<?php echo e($item->id); ?>" style="font-size:30px"></i>
                    <a href="<?php echo e(route('banner.delete',$item->id)); ?>"><i class="fa fa-trash mt-1 ml-1" style="font-size:30px;color:red;"></i></a> 
                  </td>
                </tr>
                <div class="modal fade" id="exampleModalCenterupdate1<?php echo e($item->id); ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit Banner & Activity</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <form action="<?php echo e(route('banner.update',$item->id)); ?>" method="POST" enctype="multipart/form-data">
						<?php echo csrf_field(); ?>
						<div class="modal-body">
						  <div class="container-fluid">
							<div class="row">
							  <div class="form-group col-md-6">
								<label for="image">Name</label>
								<input type="text" class="form-control" id="title" name="title" placeholder=" " value="<?php echo e($item->title); ?>">
							  </div>
							  <div class="form-group col-md-6">
								<label for="image">Image</label>
								<input type="file" class="form-control" id="image" name="image" placeholder=" " value="<?php echo e($item->image); ?>" >
							  </div>
							</div>
							     <div class="row">
              <div class="form-group col-md-6">
            <label for="image">Activity image</label>
                <input type="file" class="form-control" id="activity_image" name="activity_image" placeholder=" " value="<?php echo e($item->activity_image); ?>" >
              </div>
            
            </div>
						  </div>
						</div>
						<div class="modal-footer">
						  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
						  <button type="submit" class="btn btn-primary">Update</button>
						</div>
					  </form>
                    </div>
                  </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> 

<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Add Banner & Activity</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?php echo e(route('banner.store')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="form-group col-md-6">
                <label for="image">Name</label>
                <input type="text" class="form-control" id="title" name="title" placeholder=" " required>
              </div>
              <div class="form-group col-md-6">
                <label for="image">Image</label>
                <input type="file" class="form-control" id="image" name="image" placeholder=" " required>
              </div>
            </div>
			   <div class="row">
              <div class="form-group col-md-6">
            <label for="image">Activity image</label>
                <input type="file" class="form-control" id="activity_image" name="activity_image" placeholder=" " required>
              </div>
            
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u483840386/domains/globalbet24.club/public_html/root/resources/views/banner/index.blade.php ENDPATH**/ ?>