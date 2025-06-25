@extends('admin.body.adminmaster')

@section('admin')

    <style>
        @import url("https://fonts.googleapis.com/css?family=Montserrat:400,400i,700");

     
th{
    white-space: nowrap; 
    text-overflow: ellipsis;
}
td{
    white-space: nowrap; 
    text-overflow: ellipsis;
}  
    </style>
 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <div class="container-fluid mt-5">
        <div class="row">
            @if ($errors->has('pin'))
                <span class="text-danger error-message">{{ $errors->first('pin') }}</span>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('Success'))
                <div class="alert alert-success">
                    {{ session('Success') }}
                </div>
            @endif

            <div class="col-md-12">
                <div class="white_shd full margin_bottom_30">
                    <div class="full graph_head">
                        <div class="heading1 margin_0 d-flex">
                            <h2>Withdrawal List</h2>
                        </div>
                    </div>
                    <div class="table_section padding_infor_info">
                        <div class="table-responsive-sm">
                            <table id="example" class="table table-striped" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Id</th>
                                        <th>User name</th>
                                        <th>Bank name</th>
                                        <th>Ac. No.</th>
                                        <th>IFSC</th>
                                        <th>UPI ID</th>
                                        <th>Amount</th>
                                        <th>Actual_amount</th>
                                        <th>Mobile</th>
                                        <th>Status</th>
                                        <th>Msg</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($widthdrawls as $item)
                                        <tr>
                                            <td>{{$item->id}}</td>
                                            <td>{{$item->uname}}</td>
                                            <td>{{$item->bname}}</td>
                                            <td>{{$item->acno}}</td>
                                            <td>{{$item->ifsc}}</td>
                                            <td>{{$item->upi_id}}</td>
                                            <td>{{$item->amount}}</td>
                                            <td>{{$item->actual_amount}}</td>
                                            <td>{{$item->mobile}}</td>
                                            @if($item->status==1)  
                                                <td>
                                                    <div class="dropdown" style="display: inline-block;">
                                                        <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown" style="font-size: 13px;">
                                                            Pending
                                                        </button>
                                                        <div class="dropdown-menu" style="min-width: 160px; font-size: 12px;">
                                                            <!--<a class="dropdown-item" data-toggle="modal" data-target="#exampleModalCenter1{{$item->id}}" style="background-color: green; color: white;">-->
                                                            <!--    Approved-->
                                                            <!--</a>-->
                                                            <a class="dropdown-item" data-toggle="modal" data-target="#exampleModalCenter{{$item->id}}" style="color: red;">
                                                                Reject
                                                            </a>
                                                            <a class="dropdown-item" data-toggle="modal" data-target="#exampleModalCenter2{{$item->id}}" style="background-color: green; color: white;">
                                                                Success
                                                            </a>
                                                            <!--<a class="dropdown-item" data-toggle="modal" data-target="#exampleModalCenter3{{$item->id}}" style="background-color:white ; color: green;font-size:bold">-->
                                                            <!--    By UPI-->
                                                            <!--</a>-->
                                                            
                                                        </div>
                                                    </div>
                                                </td>
                                                
                                                <!-- upi success Modal -->
                                                <!--<div class="modal fade" id="exampleModalCenter3{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">-->
                                                <!--    <div class="modal-dialog modal-dialog-centered" role="document">-->
                                                <!--        <div class="modal-content">-->
                                                <!--            <div class="modal-header">-->
                                                <!--                <h5 class="modal-title" id="exampleModalLongTitle">Approve Withdraw</h5>-->
                                                <!--                <button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
                                                <!--                    <span aria-hidden="true">&times;</span>-->
                                                <!--                </button>-->
                                                <!--            </div>-->
                                                <!--            <form action="{{ route('widthdrawl.upi',['id' => $item->id]) }}" method="POST">-->
                                                <!--                @csrf-->
                                                <!--                <div class="modal-body">-->
                                                <!--                    <div class="container-fluid">-->
                                                <!--                        <div class="row">-->
                                                <!--                            <div class="form-group col-md-12">-->
                                                <!--                                <label for="pin">Please Enter Pin</label>-->
                                                <!--                                <input type="number" class="form-control" id="pin" name="pin" placeholder="Enter your PIN" required>-->
                                                <!--                            </div>-->
                                                <!--                        </div>-->
                                                <!--                    </div>-->
                                                <!--                </div>-->
                                                <!--                <div class="modal-footer">-->
                                                <!--                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>-->
                                                <!--                    <button type="submit" class="btn btn-primary">Submit</button>-->
                                                <!--                </div>-->
                                                <!--            </form>-->
                                                <!--        </div>-->
                                                <!--    </div>-->
                                                <!--</div>-->
                                                
                                                <!-- upi success Modal -->
                                                <div class="modal fade" id="exampleModalCenter3{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLongTitle">Approve Withdraw</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form action="{{ route('widthdrawl.upi',['id' => $item->id]) }}" method="POST" id="upiForm{{$item->id}}">
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
                                                
                                                                    <!-- Loader (initially hidden) -->
                                                                    <div id="loader{{$item->id}}" style="display: none; text-align: center;">
                                                                        <img src="path_to_loader.gif" alt="Loading...">
                                                                    </div>
                                                                    
                                                                    <!-- Success Message (initially hidden) -->
                                                                    <div id="successMessage{{$item->id}}" style="display: none; text-align: center;">
                                                                        Payment Successful!
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-primary" id="submitBtn{{$item->id}}">Submit</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>


                                                
                                                
                                                   <!-- only success Modal -->
                                                <div class="modal fade" id="exampleModalCenter2{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLongTitle">Approve Withdraw</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form action="{{ route('widthdrawl.only_success_id', ['id' => $item->id]) }}" method="POST">
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
                                                
                                                
                                                
                                                <!-- Approved Modal -->
                                                <div class="modal fade" id="exampleModalCenter1{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLongTitle">Approve Withdraw</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form action="{{ route('widthdrawl.success', ['id' => $item->id]) }}" method="POST">
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
                                                
                                                <!-- Reject Modal -->
                                                <div class="modal fade" id="exampleModalCenter{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="exampleModalLongTitle">Reject Withdrawal</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form action="{{ route('widthdrawl.reject', ['id' => $item->id]) }}" method="POST">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="container-fluid">
                                                                        <div class="row">
                                                                            <div class="form-group col-md-12">
                                                                                <label for="msg">Reason for rejection</label>
                                                                                <textarea class="form-control" id="msg" name="msg" rows="3" placeholder="Enter reason for rejection" required></textarea>
                                                                                @error('msg')
                                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                                @enderror
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
                                            @elseif($item->status==2)
                                                <td><button class="btn btn-success">Approved</button></td>
                                            @elseif($item->status==3)
                                                <td><button class="btn btn-danger">Reject</button></td>
                                            @elseif($item->status==4)
                                                <td><button class="btn btn-success">Successful</button></td>
                                            @elseif($item->status==5)
                                                <td><button class="btn btn-danger">Failed</button></td>
                                            @else
                                                <td></td>
                                            @endif
                                            <td><?php if($item->status==3){echo $item->rejectmsg;}elseif($item->status==1){echo "Pending";}elseif($item->status==2){echo "Success";}; ?></td>
                                            <td>{{$item->created_at}}</td>
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

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

<!--/////////////////////////////for loader start////////////////////-->
<!-- JavaScript (to handle loader and submit button disable) -->



<!--////////////////loader end JS-->

@endsection
