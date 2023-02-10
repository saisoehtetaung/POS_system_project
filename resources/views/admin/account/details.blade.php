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
                            <div class="card-title">
                                <h3 class="text-center title-2">Account Info</h3>
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-3 offset-2">
                                    @if (Auth::user()->image == null)
                                        @if (Auth::user()->gender == 'male')
                                            <img src="{{ asset('image/default.png') }}" class="img-thumbnail shadow-sm"
                                                alt="Default" />
                                        @else
                                            <img src="{{ asset('image/female_default.png') }}"
                                                class="img-thumbnail shadow-sm" alt="Default" />
                                        @endif
                                    @else
                                        <img src="{{ asset('storage/' . Auth::user()->image) }}" class="shadow"
                                            style="width: 450px;" alt="profile" />
                                    @endif
                                </div>
                                <div class="col-5 offset-1">
                                    <h4 class="my-3"><i class="fas fa-user-edit me-2"></i>{{ Auth::user()->name }}</h4>
                                    <h4 class="my-3"><i class="fas fa-envelope me-2"></i>{{ Auth::user()->email }}</h4>
                                    <h4 class="my-3"><i class="fas fa-phone-alt me-2"></i>{{ Auth::user()->phone }}</h4>
                                    <h4 class="my-3"><i class="fas fa-map-marker-alt me-2"></i>{{ Auth::user()->address }}
                                    </h4>
                                    <h4 class="my-3"><i class="fas fa-venus-mars me-2"></i>{{ Auth::user()->gender }}
                                    </h4>
                                    <h4 class="my-4"><i
                                            class="fas fa-user-clock me-2"></i>{{ Auth::user()->created_at->format('j-F-Y') }}
                                    </h4>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-5 offset-6 mt-2">
                                    <a href="{{ route('admin#edit') }}">
                                        <button class="btn bg-dark text-white"><i class="fas fa-edit"></i>Edit
                                            Profile</button>
                                    </a>
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
