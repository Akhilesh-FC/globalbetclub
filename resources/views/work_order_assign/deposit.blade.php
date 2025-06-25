@extends('admin.body.adminmaster')

@section('admin')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="container-fluid">
    <div class="row">
         @if ($errors->has('pin'))
                <span class="text-danger error-message">{{ $errors->first('pin') }}</span>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
<div class="col-md-12">
    <div class="white_shd full margin_bottom_30">
       <div class="full graph_head">
          <div class="heading1 margin_0 d-flex">
             <h2>Deposit List</h2>
             <!--<a href="{{route('deposit.delete_all')}}" class="btn btn-danger" style="margin-left:620px;" role="button">All Delete</a>-->

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
                      <th>Status</th>
                      <th>Date</th>
                      <!--<th>Action</th>-->
                   </tr>
                </thead>
                <tbody>
                  @foreach($deposits as $item)
                   <tr>
                      <td>{{$item->id}}</td>
					  <td>{{$item->userid}}</td>
                      <td>{{$item->uname}}</td>
					  <td>{{$item->mobile}}</td>
                      <td>{{$item->order_id}}</td>
                      <td>{{$item->cash}}</td>
					   <td>
                                        @if($item->status == 1)
                                        <div class="dropdown">
                                            <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Pending
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                 <a class="dropdown-item" data-toggle="modal" data-target="#exampleModalCenter1{{$item->id}}" style="background-color: green; color: white;">
                                                                Success
                                                            </a>
                                                <!--<a class="dropdown-item" href="{{ route('payin_success', $item->id) }}">Success</a>-->
                                            </div>
                                        </div>
                                        @elseif($item->status == 2)
                                        <button class="btn btn-success">Success</button>
                                        @elseif($item->status == 3)
                                        <button class="btn btn-danger">Reject</button>
                                        @else
                                        <span class="badge badge-secondary">Unknown Status</span>
                                        @endif
                                    </td>
                                     <!-- Approved Modal -->
                                                <div class="modal fade" id="exampleModalCenter1{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLongTitle">Success Deposit</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form action="{{ route('payin_success', ['id' => $item->id]) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="container-fluid">
                                                                        <div class="row">
                                                                            <div class="form-group col-md-12">
                                                                                <label for="pin">Please Enter Pin</label>
                                                                                <input type="number" class="form-control" id="pin" name="pin" placeholder="Enter your PIN" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                      <td>{{$item->created_at}}</td>
                      <!-- <td>-->
                      <!--  <a href="{{route('deposit.delete',$item->id)}}"><i class="fa fa-trash mt-1 ml-1" style="font-size:30px;color:red;" ></i></a>-->
                      <!--</td>-->
                 </tr>    
                @endforeach      
       </div>
    </div>
 </div>
</div>
</div> 


<script>
    $('#myModal').on('shown.bs.modal', function () {
  $('#myInputs').trigger('focus')
})
</script> 
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>



 @endsection