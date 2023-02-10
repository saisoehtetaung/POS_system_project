@extends('admin.layouts.master')

@section('title', 'Pizza List')

@section('content')


    <!-- MAIN CONTENT-->
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class=" btn btn-outline-dark mb-3" onclick="history.back() ">
                <i class="fas fa-arrow-left"></i>
            </div>
            <div class="container-fluid">
                <div class="col-md-12">
                    <!-- DATA TABLE -->
                    <div class="table-data__tool">
                        <div class="table-data__tool-left">
                            <div class="overview-wrap">
                                <h2 class="title-1">Ordered Products List</h2>

                            </div>
                        </div>

                    </div>



                    <div class="row my-2">
                        <div class="col-1 text-center bg-white shadow-sm py-1">
                            <h4> <i class="fas fa-database me-2"></i>~{{ $orderLists->total() }}</h4>
                        </div>
                    </div>

                    @if (count($orderLists) != 0)
                        <div class="table-responsive table-responsive-data2">

                            <div class="card col-5 mt-4">
                                <div class="card-header">
                                    <h4><i class="fas fa-clipboard-list me-3 fs-2"></i>Order Info</h4><span class="ms-4">
                                        (Include Delivery Charges)
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col"><i class="fas fa-user me-3"></i>Customer Name</div>
                                        <div class="col text-uppercase">{{ $orderLists[0]->user_name }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col"><i class="fas fa-barcode me-3"></i>Order Code</div>
                                        <div class="col">{{ $orderLists[0]->order_code }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col"><i class="fas fa-clock me-3"></i>Order Date</div>
                                        <div class="col">{{ $orderLists[0]->created_at->format('F-j-Y') }}</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col"><i class="fas fa-money-bill me-3"></i>Total</div>
                                        <div class="col">{{ $order->total_price }} kyats</div>
                                    </div>
                                </div>
                            </div>

                            <table class="table table-data2 text-center">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Order ID</th>
                                        <th>Product Image</th>
                                        <th>Product Name</th>
                                        <th>Qyantity</th>
                                        <th>Amount</th>

                                    </tr>
                                </thead>
                                <tbody id="dataList">

                                    @foreach ($orderLists as $orderList)
                                        <tr class="tr-shadow my-1">
                                            <td></td>
                                            <td class="">{{ $orderList->id }}</td>
                                            <td class="col-2"><img
                                                    src="{{ asset('storage/' . $orderList->product_image) }}"
                                                    class="img-thumbnails shadow-sm" /></td>
                                            <td class="">{{ $orderList->product_name }}</td>
                                            <td class="">{{ $orderList->qty }}</td>
                                            <td class="">{{ $orderList->total }}</td>
                                        </tr>
                                    @endforeach


                                </tbody>
                            </table>
                            <div class="m-5">
                                {{-- {{ $pizzas->links() }} --}}

                                {{ $orderLists->appends(request()->query())->links() }}
                            </div>

                            <!-- END DATA TABLE -->
                        </div>
                    @else
                        <h3 class="text-secondary text-center mt-5">There is no Ordered Product here</h3>
                    @endif
                </div>
            </div>
        </div>
        <!-- END MAIN CONTENT-->
    @endsection
