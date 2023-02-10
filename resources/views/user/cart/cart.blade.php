@extends('user.layouts.master')

@section('content')
    <!-- Cart Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <div class="col-lg-8 table-responsive mb-5">
                <table class="table table-light table-borderless table-hover text-center mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Products</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle" id="dataTable">
                        @foreach ($cartLists as $cartList)
                            <tr>
                                <td class="align-middle"><img src="{{ asset("storage/$cartList->pizza_image") }}"
                                        alt="" style="width: 50px;">
                                    {{ $cartList->pizza_name }}
                                    <input type="hidden" id="cartId" value="{{ $cartList->id }}" />
                                    <input type="hidden" id="productId" value="{{ $cartList->product_id }}" />
                                    <input type="hidden" id="userId" value="{{ $cartList->user_id }}" />
                                </td>
                                <td class="align-middle" id="pizzaPrice">{{ $cartList->pizza_price }} kyats</td>
                                <td class="align-middle">
                                    <div class="input-group quantity mx-auto" style="width: 100px;">
                                        <div class="input-group-btn">
                                            <button class="btn btn-sm btn-warning btn-minus">
                                                <i class="fa fa-minus"></i>
                                            </button>
                                        </div>
                                        <input type="text" id="qty"
                                            class="form-control form-control-sm  border-0 text-center"
                                            value="{{ $cartList->qty }}">
                                        <div class="input-group-btn">
                                            <button class="btn btn-sm btn-warning btn-plus">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle" id="total">{{ $cartList->pizza_price * $cartList->qty }} kyats
                                </td>
                                <td class="align-middle"><button class="btn btn-sm btn-danger btn-remove"><i
                                            class="fa fa-times"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-lg-4">
                <h5 class="section-title position-relative text-uppercase mb-3"><span class=" pr-3">Cart
                        Summary</span></h5>
                <div class="bg-light p-30 mb-5">
                    <div class="border-bottom pb-2">
                        <div class="d-flex justify-content-between mb-3">
                            <h6>Subtotal</h6>
                            <h6 id="subTotalPrice">{{ $totalPrice }} kyats</h6>
                        </div>
                        <div class="d-flex justify-content-between">
                            <h6 class="font-weight-medium">Shipping</h6>
                            <h6 class="font-weight-medium">3000 kyats</h6>
                        </div>
                    </div>
                    <div class="pt-2">
                        <div class="d-flex justify-content-between mt-2">
                            <h5>Total</h5>
                            <h5 id="finalPrice">{{ $totalPrice + 3000 }} kyats</h5>
                        </div>
                        <button class="btn btn-block btn-warning font-weight-bold my-3 py-3" id="orderbtn">Proceed To
                            Checkout</button>
                        <button class="btn btn-block btn-danger font-weight-bold my-3 py-3" id="clearbtn">Clear
                            Cart</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Cart End -->
@endsection

@section('scriptSource')
    <script src="{{ asset('js/cart.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#orderbtn').click(function() {
                $orderList = [];

                $random = Math.floor(Math.random() * 100000000);

                $('#dataTable tr').each(function(index, element) {
                    $orderList.push({
                        'orderCode': 'POS' + $random,
                        'userId': $(element).find('#userId').val(),
                        'productId': $(element).find('#productId').val(),
                        'qty': $(element).find('#qty').val(),
                        'total': Number($(element).find('#total').html().replace('kyats\n',
                            '')),

                    });
                });

                // console.log(Object.assign({}, $orderList));
                $.ajax({
                    method: 'get',
                    url: '/user/ajax/order',
                    data: Object.assign({}, $orderList),
                    dataType: 'json',
                    success: function(response) {
                        if (response['status'] == 'true') {
                            window.location.href = 'http://127.0.0.1:8000/user/homePage';
                        }
                    },

                })
            });

            $('#clearbtn').click(function() {
                $('#dataTable tr').remove();
                $('#subTotalPrice').html(`0 kyats`);
                $('#finalPrice').html(`3000 kyats`);

                $.ajax({
                    method: 'get',
                    url: '/user/ajax/clearCart',
                    dataType: 'json',

                })
            });


            $('.btn-remove').click(function() {
                $parentNode = $(this).parents('tr');
                $cartId = $parentNode.find('#cartId').val();

                $.ajax({
                    method: 'get',
                    url: '/user/ajax/clear/current/product',
                    data: {
                        'cartId': $cartId,
                    },
                    dataType: 'json',

                })
                $parentNode.remove();

                $subTotal = 0;
                $('#dataTable tr').each(function(index, element) {
                    $subTotal += Number($(element).find('#total').text().replace('kyats', ''));
                });

                $('#subTotalPrice').html($subTotal + " kyats");
                $('#finalPrice').html(`${$subTotal + 3000} kyats`);
            });

        });
    </script>
@endsection
