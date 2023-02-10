@extends('admin.layouts.master')

@section('title', 'Category List')

@section('content')


    <!-- MAIN CONTENT-->
    <div class="main-content">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="col-md-12">
                    <!-- DATA TABLE -->
                    <div class="table-data__tool">
                        <div class="table-data__tool-left">
                            <div class="overview-wrap">
                                <h2 class="title-1">Admin List</h2>

                            </div>
                        </div>
                    </div>


                    @if (session('deleted'))
                        <div class="col-4 offset-4">
                            <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                                <i class="fas fa-times-circle me-2"></i>{{ session('deleted') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-3">
                            <h4 class="text-secondary">Search Key: <span
                                    class="text-danger">{{ request('searchkey') }}</span></h4>
                        </div>
                        <div class="col-3 offset-6">
                            <form action="{{ route('admin#list') }}" method="get" class="d-flex">
                                <input type="text" name="searchkey" class="form-control" placeholder="Search..."
                                    value="{{ request('searchkey') }}" />
                                <button type="submit" class="btn btn-dark text-white">
                                    <i class="fas
                                fa-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-1 text-center bg-white shadow-sm py-1">
                            <h4> <i class="fas fa-user me-2"></i>~{{ $admins->total() }}</h4>
                        </div>
                    </div>

                    <div class="table-responsive table-responsive-data2">
                        <table class="table table-data2 text-center">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Gender</th>
                                    <th>Phone-NO</th>
                                    <th>Address</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($admins as $admin)
                                    <tr class="tr-shadow my-1">
                                        <td class='col-2'>
                                            @if ($admin->image == null)
                                                @if ($admin->gender == 'male')
                                                    <img src="{{ asset('image/default.png') }}"
                                                        class="img-thumbnail shadow-sm" width="100px" alt="Default" />
                                                @else
                                                    <img src="{{ asset('image/female_default.png') }}"
                                                        class="img-thumbnail shadow-sm" width="100px" alt="Default" />
                                                @endif
                                            @else
                                                <img src="{{ asset('storage/' . $admin->image) }}"
                                                    class="img-thumbnail shadow-sm" width="100px" alt="Admin" />
                                            @endif

                                        </td>
                                        <td>{{ $admin->name }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>{{ $admin->gender }}</td>
                                        <td>{{ $admin->phone }}</td>
                                        <td>{{ $admin->address }}</td>
                                        <td>
                                            <div class="table-data-feature">
                                                <a href="{{ route('admin#changeRole', $admin->id) }}">
                                                    <button class="item fs-4 me-2" data-toggle="tooltip"
                                                        data-placement="top"
                                                        @if (Auth::user()->id == $admin->id) style="display:none" @endif
                                                        title="Edit">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                </a>
                                                <a href="{{ route('admin#delete', $admin->id) }}">
                                                    <button class="item fs-4" data-toggle="tooltip" data-placement="top"
                                                        @if (Auth::user()->id == $admin->id) style="display:none" @endif
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </a>
                                            </div>

                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                        <div class="m-5">
                            {{ $admins->links() }}

                            {{-- {{ $categories->appends(request()->query())->links() }} --}}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- END MAIN CONTENT-->
@endsection
