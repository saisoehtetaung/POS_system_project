@extends('admin.layouts.master')

@section('title', 'Pizza List')

@section('content')


    <!-- MAIN CONTENT-->
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="col-md-12">
                    <!-- DATA TABLE -->
                    <div class="table-data__tool">
                        <div class="table-data__tool-left">
                            <div class="overview-wrap">
                                <h2 class="title-1">Order List</h2>

                            </div>
                        </div>

                    </div>


                    @if (session('deleteSuccess'))
                        <div class="col-4 offset-4">
                            <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                                <i class="fas fa-times-circle me-2"></i>{{ session('deleteSuccess') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-3">
                            <h4 class="text-secondary">Search Key: <span
                                    class="text-danger">{{ request('searchkey') }}</span></h4>
                        </div>
                        <div class="col-3 offset-6">
                            <form action="{{ route('admin#orderList') }}" method="get" class="d-flex">
                                <input type="text" name="searchkey" class="form-control" placeholder="Search..."
                                    value="{{ old('searchkey') }}" />
                                <button type="submit" class="btn btn-dark text-white">
                                    <i class="fas
                                fa-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-1 text-center bg-white shadow-sm py-1">
                            <h4> <i class="fas fa-database me-2"></i>~{{ $orders->total() }}</h4>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-outline-dark btn-sm me-2" id="all">All</button>
                        <button class="btn btn-outline-warning btn-sm me-2" id='pending'>Pending</button>
                        <button class="btn btn-outline-success btn-sm me-2" id='sending'>Sending</button>
                        <button class="btn btn-outline-danger btn-sm me-2" id='rejected'>Rejected</button>
                    </div>

                    @if (count($orders) != 0)
                        <div class="table-responsive table-responsive-data2">
                            <table class="table table-data2 text-center">
                                <thead>
                                    <tr>
                                        <th>User ID</th>
                                        <th>User Name</th>
                                        <th>Order Date</th>
                                        <th>Order Code</th>
                                        <th>Amount</th>
                                        <th>status</th>

                                    </tr>
                                </thead>
                                <tbody id="dataList">

                                    @foreach ($orders as $order)
                                        <tr class="tr-shadow my-1">
                                            <input type="hidden" name="" id="orderid" value="{{ $order->id }}">

                                            <td class="">{{ $order->user_id }}</td>
                                            <td class="">{{ $order->user_name }}</td>
                                            <td class="">{{ $order->created_at->format('F-j-Y') }}</td>
                                            <td class="ordercode">
                                                <a href=" {{ route('admin#listInfo', $order->order_code) }}"
                                                    class="text-decoration-underline">{{ $order->order_code }}</a>
                                            </td>
                                            <td class="">{{ $order->total_price }} kyats</td>
                                            <td class="">
                                                <select name="status" class="form-control text-center statuschange">
                                                    <option value="0"
                                                        @if ($order->status == 0) selected @endif>
                                                        Pending
                                                    </option>
                                                    <option value="1"
                                                        @if ($order->status == 1) selected @endif>
                                                        Sending</option>
                                                    <option value="2"
                                                        @if ($order->status == 2) selected @endif>
                                                        Rejected</option>
                                                </select>
                                            </td>

                                        </tr>
                                    @endforeach


                                </tbody>
                            </table>
                            <div class="m-5">
                                {{-- {{ $pizzas->links() }} --}}

                                {{ $orders->appends(request()->query())->links() }}
                            </div>

                            <!-- END DATA TABLE -->
                        </div>
                    @else
                        <h3 class="text-secondary text-center mt-5">There is no Order here</h3>
                    @endif
                </div>
            </div>
        </div>
        <!-- END MAIN CONTENT-->
    @endsection

    @section('scriptSection')
        <script>
            $(document).ready(function() {
                function choosestatus(val) {
                    $.ajax({
                        method: 'get',
                        url: 'http://127.0.0.1:8000/order/ajax/status',
                        data: {
                            'status': val,
                        },
                        dataType: 'json',
                        success: function(response) {
                            // console.log(response.data[0].user_id);


                            $list = ``;
                            $month = ['January', 'February', 'March', 'April', 'May', 'June', 'Jule',
                                'Augest', 'September', 'October', 'November', 'December'
                            ];
                            for ($i = 0; $i < response.data.length; $i++) {

                                $dbDate = new Date(response.data[$i].created_at);
                                $getmonth = $month[$dbDate.getMonth()];
                                $getday = $dbDate.getDate();
                                $getyear = $dbDate.getFullYear();

                                $list += `<tr class="tr-shadow my-1">
                                    <input type="hidden" name="" id="orderid" value="${response.data[$i].id}">
                                    <td class="">${response.data[$i].user_id}</td>
                                    <td class="">${response.data[$i].user_name}</td>
                                    <td class="">${$getmonth}-${$getday}-${$getyear}</td>
                                    <td class="ordercode">${response.data[$i].order_code}</td>
                                    <td class="">${response.data[$i].total_price} kyats</td>
                                    <td class="">
                                        `;

                                if (response.data[$i].status == 0) {
                                    $list += `<select name="status" class="form-control text-center statuschange">
                                            <option value="0" selected >Pending</option>
                                            <option value="1" >Sending</option>
                                            <option value="2" >Rejected</option>
                                        </select>
                                    </td>

                                </tr>`;
                                } else if (response.data[$i].status == 1) {
                                    $list += `<select name="status" class="form-control text-center statuschange">
                                            <option value="0" >Pending</option>
                                            <option value="1" selected>Sending</option>
                                            <option value="2" >Rejected</option>
                                        </select>
                                    </td>

                                </tr>`;
                                } else {
                                    $list += `<select name="status" class="form-control text-center statuschange">
                                            <option value="0" >Pending</option>
                                            <option value="1" >Sending</option>
                                            <option value="2" selected>Rejected</option>
                                        </select>
                                    </td>

                                </tr>`;
                                }
                            }
                            $('#dataList').html($list);


                        },

                    })
                }
                $('#all').click(function() {
                    choosestatus('')
                });
                $('#pending').click(function() {
                    choosestatus('0')
                });
                $('#sending').click(function() {
                    choosestatus('1')
                });
                $('#rejected').click(function() {
                    choosestatus('2')
                });

                //change status
                $('.statuschange').change(function() {
                    console.log('hello');
                    $parentNode = $(this).parents('tr');
                    $currentStatus = $(this).val();
                    $orderId = $parentNode.find('#orderid').val();
                    $.ajax({
                        method: 'get',
                        url: 'http://127.0.0.1:8000/order/ajax/change/status',
                        data: {
                            'orderId': $orderId,
                            'status': $currentStatus,
                        },
                        dataType: 'json',
                    });
                });
            })
        </script>
    @endsection
