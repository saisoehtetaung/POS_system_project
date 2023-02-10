@extends('admin.layouts.master')

@section('title', 'Category List')

@section('content')


    <!-- MAIN CONTENT-->
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="col-lg-10 offset-1">
                    <div class="card">
                        <div class="card-body">
                            <div class=" btn btn-outline-dark ms-5" onclick="history.back() ">
                                <i class="fas fa-arrow-left"></i>
                            </div>
                            <div class="card-title ">
                                <h3 class="text-center title-2">Pizza details</h3>
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-3 offset-1">

                                    <img src="{{ asset('storage/' . $pizza->image) }}" class="shadow" alt="profile" />
                                    {{-- <div class="row">
                                        <div class="col-5 offset-2 mt-5">
                                            <a href="{{ route('admin#edit') }}">
                                                <button class="btn bg-dark text-white"><i class="fas fa-edit"></i>Edit
                                                    Profile</button>
                                            </a>
                                        </div>

                                    </div> --}}
                                </div>
                                <div class="col">
                                    <h3 class="my-3 d-block "><i
                                            class="fas fa-pizza-slice me-2 fs-5"></i>{{ $pizza->name }}
                                    </h3>

                                    <span class="my-3 btn btn-dark"><i
                                            class="fas fa-money-bill-wave me-2 fs-5"></i>{{ $pizza->price }} Ks
                                    </span>
                                    <span class="my-3 btn btn-dark"><i
                                            class="fas fa-stopwatch me-2 fs-5"></i>{{ $pizza->waiting_time }}
                                        minutes
                                    </span>
                                    <span class="my-3 btn btn-dark"><i
                                            class="fas fa-eye me-2 fs-5"></i>{{ $pizza->view_count }}
                                    </span>
                                    <span class="my-3 btn btn-dark"><i
                                            class="fas fa-clipboard-list me-2 fs-5"></i>{{ $pizza->category_name }}
                                    </span>
                                    <span class="my-4 btn btn-dark"><i
                                            class="fas fa-user-clock me-2 fs-5"></i>{{ $pizza->created_at->format('j-F-Y') }}
                                    </span>
                                    <div class="text-decoration-underline"><i class="fas fa-file-alt me-2 fs-4"></i>
                                        Description </div>
                                    <div>{{ $pizza->description }}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @if (session('updateSuccess'))
                <div class="col-4 offset-4">
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('updateSuccess') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <!-- END MAIN CONTENT-->
@endsection
