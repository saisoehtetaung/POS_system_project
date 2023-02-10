@extends('user.layouts.master')

@section('content')
    <!-- Cart Start -->
    <div class="container-fluid" style="min-height: 500px;">
        <div class="row px-xl-5">
            <div class="col-lg-8 offset-2 table-responsive mb-5">
                <table class="table table-light table-borderless table-hover text-center mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Date</th>
                            <th>Order</th>
                            <th>Total Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle" id="dataTable">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="align-middle">{{ $order->created_at->format('F-j-Y') }}</td>
                                <td class="align-middle">{{ $order->order_code }}</td>
                                <td class="align-middle">{{ $order->total_price }}</td>
                                <td class="align-middle">
                                    @if ($order->status == 0)
                                        <span class="text-warning shadow-sm fs-4"><i
                                                class="fas fa-hourglass-half"></i>Pending...</span>
                                    @elseif($order->status == 1)
                                        <span class="text-success shadow-sm fs-4"><i class="fas fa-check"></i>Success</span>
                                    @elseif($order->status == 2)
                                        <span class="text-danger shadow-sm fs-4"><i
                                                class="far fa-times-circle"></i>Reject</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <span>
                    {{ $orders->appends(request()->query())->links() }}
                </span>
            </div>

        </div>
    </div>
    <!-- Cart End -->
@endsection
