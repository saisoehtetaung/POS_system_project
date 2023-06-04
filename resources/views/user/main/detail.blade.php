@extends('user.layouts.master')

@section('content')
    <div class=" btn btn-outline-dark my-3 ms-5" onclick="history.back() ">
        <i class="fas fa-arrow-left"></i>
    </div>
    <!-- Shop Detail Start -->
    <div class="container-fluid pb-5">
        <div class="row px-xl-5">
            <div class="col-lg-5 mb-30">
                <div id="product-carousel" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner bg-light">
                        <div class="carousel-item active">
                            <img class=" h-100" style="width: 450px;" src="{{ asset('storage/' . $pizzaInfo->image) }}"
                                alt="Image">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 h-auto mb-30">
                <div class="h-100 bg-light p-30">
                    <h3>{{ $pizzaInfo->name }}</h3>
                    <div class="d-flex mb-3">
                        {{-- <div class="text-primary mr-2">
                            <small class="fas fa-star"></small>
                            <small class="fas fa-star"></small>
                            <small class="fas fa-star"></small>
                            <small class="fas fa-star-half-alt"></small>
                            <small class="far fa-star"></small>
                        </div> --}}
                        <small class="pt-1"><i class="fas fa-eye"></i> {{ $pizzaInfo->view_count }} Views</small>
                    </div>
                    <h3 class="font-weight-semi-bold mb-4">{{ $pizzaInfo->price }} Kyats</h3>
                    <p class="mb-4">{{ $pizzaInfo->description }}</p>
                    <div class="d-flex align-items-center mb-4 pt-2">
                        <div class="input-group quantity mr-3" style="width: 200px;">
                            <div class="input-group-btn">
                                <button class="btn btn-warning btn-minus">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                            <input type="text" class="form-control bg-secondary border-0 text-center" value="1"
                                id="orderCount">

                            <div class="input-group-btn">
                                <button class="btn btn-warning btn-plus">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" value="{{ Auth::user()->id }}" id="userId" />
                        <input type="hidden" value="{{ $pizzaInfo->id }}" id="pizzaId" />
                        <button class="btn btn-warning px-3" type="button" id="addCartBtn"><i
                                class="fa fa-shopping-cart mr-1"></i> Add To
                            Cart</button>
                    </div>
                    <div class="d-flex pt-2">
                        <strong class="text-dark mr-2">Share on:</strong>
                        <div class="d-inline-flex">
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a class="text-dark px-2" href="">
                                <i class="fab fa-pinterest"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Shop Detail End -->


    <!-- Products Start -->
    <div class="container-fluid py-5">
        <h2 class="section-title position-relative text-uppercase mx-xl-5 mb-4"><span class="bg-secondary pr-3">You
                May Also Like</span></h2>
        <div class="row px-xl-5">
            <div class="col">
                <div class="owl-carousel related-carousel">
                    @foreach ($pizzas as $pizza)
                        <div class="product-item bg-light">
                            <div class="product-img position-relative overflow-hidden">
                                <img class="img-fluid w-100" style="height: 200px;"
                                    src="{{ asset('storage/' . $pizza->image) }}" alt="">
                                <div class="product-action">
                                    <a class="btn btn-outline-dark btn-square" href=""><i
                                            class="fa fa-shopping-cart"></i></a>
                                    <a class="btn btn-outline-dark btn-square"
                                        href="{{ route('user#pizzaDetails', $pizza->id) }}"><i
                                            class="fas fa-info-circle"></i></a>

                                </div>
                            </div>
                            <div class="text-center py-4">
                                <a class="h6 text-decoration-none text-truncate" href="">$pizza->name</a>
                                <div class="d-flex align-items-center justify-content-center mt-2">
                                    <h5>{{ $pizza->price }} kyats</h5>

                                </div>
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small class="fa fa-star text-primary mr-1"></small>
                                    <small>(99)</small>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
    <!-- Products End -->
@endsection


@section('scriptSource')
    <script>
        $(document).ready(function() {

            //increase view count
            $.ajax({
                method: 'get',
                url: 'http://127.0.0.1:8000/user/ajax/increase/viewCount',
                data: {
                    'productId': $('#pizzaId').val(),
                },
                dataType: 'json'
            })


            // click add to cart btn
            $('#addCartBtn').click(function() {

                $source = {
                    'userId': $('#userId').val(),
                    'pizzaId': $('#pizzaId').val(),
                    'count': $('#orderCount').val(),
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
