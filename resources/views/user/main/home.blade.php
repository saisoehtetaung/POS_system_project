@extends('user.layouts.master')

@section('content')
    <!-- Shop Start -->
    <div class="container-fluid">
        <div class="row px-xl-5">
            <!-- Shop Sidebar Start -->
            <div class="col-lg-3 col-md-4">
                <!-- Price Start -->
                <h5 class="section-title position-relative text-uppercase mb-3"><span class=" pr-3">Filter
                        by Categories</span></h5>
                <div class="bg-light p-4 mb-30">
                    <form>
                        <div class=" d-flex align-items-center justify-content-between mb-3 bg-dark text-white px-3 py-1">
                            {{-- <input type="checkbox" class="custom-control-input" checked id="price-all"> --}}
                            <label class="mt-2" for="price-all">Categories</label>
                            <span class="badge border font-weight-normal">{{ count($categories) }}</span>
                        </div>

                        <div class=" d-flex align-items-center justify-content-between mb-3">

                            <a class="text-dark" href="{{ route('user#home') }}"><label class=""
                                    for="price-1">All</label></a>

                        </div>

                        @foreach ($categories as $category)
                            <div class=" d-flex align-items-center justify-content-between mb-3">

                                <a class="text-dark" href="{{ route('user#filter', $category->id) }}"><label class=""
                                        for="price-1">{{ $category->name }}</label></a>

                            </div>
                        @endforeach


                    </form>
                </div>
                <!-- Price End -->


                <div class="">
                    <button class="btn btn btn-warning w-100">Order</button>
                </div>
            </div>
            <!-- Shop Sidebar End -->


            <!-- Shop Product Start -->
            <div class="col-lg-9 col-md-8">
                <div class="row pb-3">
                    <div class="col-12 pb-1">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <a href="{{ route('cart#list') }}">
                                    <button type="button" class="btn btn-dark position-relative">
                                        <i class="fas fa-shopping-cart"></i>
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ count($carts) }}

                                        </span>
                                    </button>
                                </a>

                                <a href="{{ route('user#history') }}" class="ms-3">
                                    <button type="button" class="btn btn-dark position-relative">
                                        <i class="fas fa-history"></i>History
                                        <span
                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ count($orders) }}

                                        </span>
                                    </button>
                                </a>
                            </div>

                            <div class="ml-2">
                                <div class="btn-group">

                                    <select name="sorting" id="sortingOption" class="form-control">
                                        <option disabled selected> Choose Option... </option>
                                        <option value="asc">Ascending</option>
                                        <option value="desc">Descending</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    @if (session('messageSent'))
                        <div class="col-4 offset-4">
                            <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                <i class="fas fa-times-circle me-2"></i>{{ session('messageSent') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        </div>
                    @endif


                    <div class="row" id="myform">
                        @if (count($pizzas) != 0)
                            @foreach ($pizzas as $pizza)
                                <div class="col-lg-4 col-md-6 col-sm-6 pb-1">
                                    <div class="product-item bg-light mb-4">
                                        <div class="product-img position-relative overflow-hidden">
                                            <img class="img-fluid w-100" style="height:200px"
                                                src="{{ asset('storage/' . $pizza->image) }}" alt="">
                                            <input type="hidden" value="{{ Auth::user()->id }}" id="userId" />
                                            <input type="hidden" value="{{ $pizza->id }}" id="pizzaId" />
                                            <div class="product-action">
                                                <a class="btn btn-outline-dark btn-square" href="" id="addCartBtn"><i
                                                        class="fa fa-shopping-cart"></i></a>
                                                <a class="btn btn-outline-dark btn-square"
                                                    href="{{ route('user#pizzaDetails', $pizza->id) }}"><i
                                                        class="fas fa-info-circle"></i></a>

                                            </div>
                                        </div>
                                        <div class="text-center py-4">
                                            <a class="h6 text-decoration-none text-truncate"
                                                href="">{{ $pizza->name }}</a>
                                            <div class="d-flex align-items-center justify-content-center mt-2">
                                                <h5>{{ $pizza->price }} kyats</h5>
                                                {{-- <h6 class="text-muted ml-2"><del>25000</del></h6> --}}
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="text-center shadow fs-2 col-6 offset-3 py-5">There is no pizza.<i
                                    class="fas fa-pizza-slice"></i></p>
                        @endif
                    </div>



                </div>
            </div>
            <!-- Shop Product End -->
        </div>
    </div>
    <!-- Shop End -->
@endsection

@section('scriptSource')
    <script>
        $(document).ready(function() {
            //     $.ajax({
            //         type: 'get',
            //         url: 'http://localhost:8000/user/ajax/pizza/list',
            //         dataType: 'json',
            //         success: function(data) {
            //             console.log(data);
            //         },
            //         error: function() {
            //             alert('error');
            //         }
            //     });

            $('#sortingOption').change(function() {
                $eventOption = $('#sortingOption').val();
                // console.log($eventOption);

                if ($eventOption == 'asc') {
                    $.ajax({
                        method: 'get',
                        url: 'http://127.0.0.1:8000/user/ajax/pizza/list',
                        data: {
                            'status': 'asc'
                        },
                        dataType: 'json',
                        success: function(response) {
                            $list = ``;
                            for ($i = 0; $i < response.length; $i++) {
                                $list += `
                                <div class="col-lg-4 col-md-6 col-sm-6 pb-1">
                                <div class="product-item bg-light mb-4">
                                 <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100" style="height:200px"
                                    src="{{ asset('storage/${response[$i] . image}') }}" alt="">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href=""><i
                                            class="fa fa-shopping-cart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href=""><i
                                            class="fas fa-info-circle"></i></a>

                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none text-truncate" href="">${response[$i] . name}</a>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <h5>${response[$i] . price} kyats</h5>
                                </div>

                            </div>
                            </div>
                            </div>
                            `;

                            }
                            $('#myform').html($list);
                        },
                    });
                } else if ($eventOption == 'desc') {
                    $.ajax({
                        method: 'get',
                        url: 'http://127.0.0.1:8000/user/ajax/pizza/list',
                        data: {
                            'status': 'desc'
                        },
                        dataType: 'json',
                        success: function(response) {
                            $list = ``;
                            for ($i = 0; $i < response.length; $i++) {
                                $list += `
                                <div class="col-lg-4 col-md-6 col-sm-6 pb-1">
                                <div class="product-item bg-light mb-4">
                                 <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100" style="height:200px"
                                    src="{{ asset('storage/${response[$i] . image}') }}" alt="">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href=""><i
                                            class="fa fa-shopping-cart"></i></a>
                                    <a class="btn btn-outline-dark btn-square" href=""><i
                                            class="fas fa-info-circle"></i></a>

                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none text-truncate" href="">${response[$i] . name}</a>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <h5>${response[$i] . price} kyats</h5>
                                </div>

                            </div>
                            </div>
                            </div>
                            `;

                            }
                            $('#myform').html($list);
                        },
                    });
                }
            })

            // click add to cart btn
            $('#addCartBtn').click(function() {

                $source = {
                    'userId': $('#userId').val(),
                    'pizzaId': $('#pizzaId').val(),
                    'count': '1',
                };

                $.ajax({
                    method: 'get',
                    url: 'http://127.0.0.1:8000/user/ajax/addToCart',
                    data: $source,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location.href = 'http://127.0.0.1:8000/user/homePage';
                        }
                    },
                });
            });

        });
    </script>
@endsection
