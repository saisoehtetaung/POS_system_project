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
                            <h3 class="text-center title-2"><i
                                    class="fas fa-comment-dots fs-3 me-2"></i>{{ $contact->name }}'s
                                Message</h3>

                            <hr>
                            <div class="row">

                                <div class="col-10 offset-1">
                                    <h3 class="my-3"><i class="fas fa-user me-2"></i>Name: {{ $contact->name }}</h3>
                                    <h3 class="my-3"><i class="fas fa-envelope me-2"></i>email: {{ $contact->email }}
                                    </h3>
                                    <h3 class="my-3"><i class="fas fa-comment-alt me-2"></i></i>Message: </h3>
                                    <p class="fs-4 ms-5">{{ $contact->message }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-5 offset-6 mt-2">
                                    <a href="{{ route('admin#deleteContact', $contact->id) }}">
                                        <button class="btn bg-danger text-white"><i class="fas fa-trash me-2"></i>Delete
                                            Message</button>
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
