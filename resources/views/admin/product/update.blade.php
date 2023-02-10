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
                            <div class="card-title">
                                <h3 class="text-center title-2">Update Pizza detail</h3>
                            </div>

                            <hr>
                            <form action="{{ route('product#update') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-4 offset-1">

                                        <img src="{{ asset('storage/' . $pizza->image) }}" class="shadow" alt="Default" />

                                        <div class="mt-3">
                                            <input type="file" name="pizzaImage"
                                                class="form-control @error('pizzaImage') is-invalid @enderror"
                                                id="" />
                                            @error('pizzaImage')
                                                {{ $message }}
                                            @enderror
                                        </div>

                                        <div class="mt-5">
                                            <button class="btn bg-dark text-white col-12" type="submit"><i
                                                    class="fas fa-arrow-circle-right me-1"></i>Update</button>
                                        </div>
                                    </div>
                                    <input name="pizzaId" type="hidden" value="{{ $pizza->id }}" />
                                    <div class="row col-6">
                                        <div class="form-group">
                                            <label class="control-label mb-1">Pizza Name</label>
                                            <input name="pizzaName" type="text"
                                                class="form-control @error('pizzaName') is-invalid @enderror"
                                                placeholder="Enter Pizza Name..."
                                                value="{{ old('pizzaName', $pizza->name) }}">
                                            @error('pizzaName')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Description</label>
                                            <textarea name="pizzaDescription" class="form-control @error('pizzaDescription') is-invalid @enderror" rows="3"
                                                placeholder="Enter Description...">{{ old('pizzaDescription', $pizza->description) }}</textarea>
                                            @error('pizzaDescription')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Category</label>
                                            <select name="pizzaCategory"
                                                class="form-control @error('pizzaCategory') is-invalid @enderror">
                                                <option disabled selected>Choose pizza Category...</option>
                                                @foreach ($category as $c)
                                                    <option value="{{ $c->id }}"
                                                        @if ($pizza->category_id == $c->id) selected @endif>
                                                        {{ $c->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('pizzaCategory')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Price</label>
                                            <input name="pizzaPrice" type="text"
                                                class="form-control @error('pizzaPrice') is-invalid @enderror"
                                                placeholder="Enter Pizza Price..."
                                                value="{{ old('pizzaPrice', $pizza->price) }}">
                                            @error('pizzaPrice')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Waiting Time</label>
                                            <input name="pizzaWaitingTime" type="text"
                                                class="form-control @error('pizzaWaitingTime') is-invalid @enderror"
                                                placeholder="Enter Pizza waitingTime..."
                                                value="{{ old('pizzaWaitingTime', $pizza->waiting_time) }}">
                                            @error('pizzaWaitingTime')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">View Count</label>
                                            <input name="viewCount" type="text" class="form-control"
                                                value="{{ $pizza->view_count }}" disabled>

                                        </div>

                                        <div class="form-group">
                                            <label class="control-label mb-1">Created Date</label>
                                            <input name="createdAt" type="text" class="form-control"
                                                value="{{ old('createdAt', $pizza->created_at->format('j-F-Y')) }}"
                                                disabled>
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
