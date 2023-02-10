@extends('user.layouts.master')

@section('content')

    <!-- MAIN CONTENT-->
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="col-lg-10 offset-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">
                                <h3 class="text-center title-2">Account Profile</h3>
                            </div>

                            <hr>
                            @if (session('updateSuccess'))
                                <div class="col-4 offset-4">
                                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                                        <i class="fas fa-check-circle me-2"></i>{{ session('updateSuccess') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                            aria-label="Close"></button>
                                    </div>
                                </div>
                            @endif
                            <form action="{{ route('user#accountChange', Auth::user()->id) }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-4 offset-1">
                                        @if (Auth::user()->image == null)
                                            @if (Auth::user()->gender == 'male')
                                                <img src="{{ asset('image/default.png') }}" class="img-thumbnail shadow-sm"
                                                    alt="Default" />
                                            @else
                                                <img src="{{ asset('image/female_default.png') }}"
                                                    class="img-thumbnail shadow-sm" alt="Default" />
                                            @endif
                                        @else
                                            <img src="{{ asset('storage/' . Auth::user()->image) }}" class="shadow "
                                                style="width: 450px;" alt="profile" />
                                        @endif
                                        <div class="mt-3">
                                            <input type="file" name="image"
                                                class="form-control @error('image') is-invalid @enderror" id="" />
                                            @error('image')
                                                {{ $message }}
                                            @enderror
                                        </div>

                                        <div class="mt-5">
                                            <button class="btn bg-dark text-white col-12" type="submit"><i
                                                    class="fas fa-arrow-circle-right me-1"></i>Update</button>
                                        </div>
                                    </div>
                                    <div class="row col-6">
                                        <div class="form-group">
                                            <label class="control-label mb-1">Name</label>
                                            <input name="name" type="text"
                                                class="form-control @error('name') is-invalid @enderror"
                                                placeholder="Enter Admin Name..."
                                                value="{{ old('name', Auth::user()->name) }}">
                                            @error('name')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Email</label>
                                            <input name="email" type="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                placeholder="Enter Admin Email..."
                                                value="{{ old('email', Auth::user()->email) }}">
                                            @error('email')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Gender</label>
                                            <select name="gender"
                                                class="form-control @error('gender') is-invalid @enderror">
                                                <option disabled>Choose gender...</option>
                                                <option value="male" @if (Auth::user()->gender == 'male') selected @endif>
                                                    Male</option>
                                                <option value="female" @if (Auth::user()->gender == 'female') selected @endif>
                                                    Female</option>
                                            </select>
                                            @error('gender')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Phone</label>
                                            <input name="phone" type="text"
                                                class="form-control @error('phone') is-invalid @enderror"
                                                placeholder="Enter Admin Phone no..."
                                                value="{{ old('phone', Auth::user()->phone) }}">
                                            @error('phone')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Address</label>
                                            <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3"
                                                placeholder="Enter Admin Address...">{{ old('address', Auth::user()->address) }}</textarea>
                                            @error('address')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Role</label>
                                            <input name="role" type="text" class="form-control"
                                                value="{{ old('role', Auth::user()->role) }}" disabled>
                                        </div>

                                    </div>
                                </div>


                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END MAIN CONTENT-->

@endsection
