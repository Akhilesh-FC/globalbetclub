

<?php $__env->startSection('admin'); ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="white_shd full margin_bottom_30">
                <div class="full graph_head">
                    <div class="heading1 margin_0 d-flex">
                        <h2>USDT Deposit List</h2>
                        
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
                                    <th>INR Amount</th>
                                    <th>USDT Amount</th>
                                    <th>Screenshot</th>
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
                                    <td><?php echo e($item->usdt_amount); ?></td>
                                    <td><a href="<?php echo e(url(env('APP_URL') . $item->typeimage)); ?>">view</a></td>
                                    <td>
                                        <?php if($item->status == 1): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Pending
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" href="<?php echo e(route('usdt_success', $item->id)); ?>">Success</a>
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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u483840386/domains/globalbet24.club/public_html/root/resources/views/usdt_deposit/deposit.blade.php ENDPATH**/ ?>