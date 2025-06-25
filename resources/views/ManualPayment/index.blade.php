@extends('admin.body.adminmaster')

@section('admin')

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif


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
             {{-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModalCenter" style="margin-left:620px;">Add Work Name</button> --}}
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
                  @foreach($deposits as $item)
                   <tr>
                      <td>{{$item->id}}</td>
					  <td>{{$item->userid}}</td>
                      <td>{{$item->uname}}</td>
					  <td>{{$item->mobile}}</td>
                      <td>{{$item->order_id}}</td>
                      <td>{{$item->cash}}</td>
					   <td>
                                        <!-- Pass the image source dynamically to the modal -->
                                        <img src="{{URL::asset($item->typeimage)}}" width="50px" height="50px" onclick="openModal('{{URL::asset($item->typeimage)}}')">
                                    </td>
					   <td>
                                        @if($item->status == 1)
                                        <div class="dropdown">
                                            <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Pending
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" href="{{ route('payin_success', $item->id) }}">Success</a>
                                                <a class="dropdown-item" href="{{ route('usdt_reject', $item->id) }}">Reject</a>
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
                      <td>{{$item->created_at}}</td>
                     
                      {{-- <td>
                        <i class="fa fa-edit mt-1" data-toggle="modal" data-target="#exampleModalCenterupdate{{$item->id}}" style="font-size:30px"></i>
                        <a href="{{route('work_name.delete',$item->id)}}"><i class="fa fa-trash mt-1 ml-1" style="font-size:30px;color:red;" ></i></a>
            
                      </td> --}}
                      {{-- <div class="modal fade" id="exampleModalCenterupdate{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="exampleModalLongTitle">Edit Work Name</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <form action="{{route('work_name.update',$item->id)}}" method="POST" enctype="multipart/form-data">
                              @csrf
                            <div class="modal-body">
                              <div class="container-fluid">
                                <div class="row">
                                  <div class="form-group col-md-12">
                                    <label for="fieldname">Field Name</label>
                                    <input type="text" class="form-control" id="fieldname" value="{{$item->fieldname}}" name="fieldname" placeholder="Enter fieldname">
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
                      </div> --}}
                    
                   </tr>
                  @endforeach
                </tbody>
             </table>
          </div>
       </div>
    </div>
 </div>
</div>
</div> 
{{-- popup modal form --}}
<!-- Modal -->
{{-- <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Add Work Name</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{route('work_name.store')}}" method="POST" enctype="multipart/form-data">
          @csrf
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="form-group col-md-12">
                <label for="fieldname">Field Name</label>
                <input type="text" class="form-control" id="fieldname" name="fieldname" placeholder="Enter fieldname">
              </div>
              
            </div>
           
        </div>
      
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </div>
  </div>


<script>
    $('#myModal').on('shown.bs.modal', function () {
  $('#myInputs').trigger('focus')
})
</script> --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>


<!-- Modal -->
<div id="imageModal">
    <span id="closeButton" onclick="closeModal()">×</span>
    <img id="modalImage" src="" alt="Enlarged image">
</div>

 @endsection