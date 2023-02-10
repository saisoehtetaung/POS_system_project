@extends('admin.layouts.master')

@section('title', 'Pizza List')

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
                                <h2 class="title-1">Product List</h2>

                            </div>
                        </div>
                        <div class="table-data__tool-right">
                            <a href="{{ route('product#createPage') }}">
                                <button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                    <i class="zmdi zmdi-plus"></i>add pizza
                                </button>
                            </a>
                            <button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                CSV download
                            </button>
                        </div>
                    </div>


                    @if (session('deleteSuccess'))
                        <div class="col-4 offset-4">
                            <div class="alert alert-warning alert-dismissible fade show text-center" role="alert">
                                <i class="fas fa-times-circle me-2"></i>{{ session('deleteSuccess') }}
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
                            <form action="{{ route('product#list') }}" method="get" class="d-flex">
                                <input type="text" name="searchkey" class="form-control" placeholder="Search..."
                                    value="{{ old('searchkey') }}" />
                                <button type="submit" class="btn btn-dark text-white">
                                    <i class="fas
                                fa-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-1 text-center bg-white shadow-sm py-1">
                            <h4> <i class="fas fa-database me-2"></i>~{{ $pizzas->total() }}</h4>
                        </div>
                    </div>

                    @if (count($pizzas) != 0)
                        <div class="table-responsive table-responsive-data2">
                            <table class="table table-data2 text-center">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Category</th>
                                        <th>View Count</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($pizzas as $pizza)
                                        <tr class="tr-shadow my-1">
                                            <td class="col-2"><img src="{{ asset('storage/' . $pizza->image) }}"
                                                    class="img-thumbnail shadow-sm" /></td>
                                            <td class="col-4">{{ $pizza->name }}</td>
                                            <td class="col-2">{{ $pizza->price }}</td>
                                            <td class="col-2">{{ $pizza->category_name }}</td>
                                            <td class="col-2"><i class="fas fa-eye"></i>{{ $pizza->view_count }}</td>
                                            <td>
                                                <div class="table-data-feature">

                                                    <a href="{{ route('product#edit', $pizza->id) }}">
                                                        <button class="item me-2" data-toggle="tooltip" data-placement="top"
                                                            title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </a>
                                                    <a href="{{ route('product#updatePage', $pizza->id) }}">
                                                        <button class="item me-2" data-toggle="tooltip" data-placement="top"
                                                            title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </a>
                                                    <a href="{{ route('product#delete', $pizza->id) }}">
                                                        <button class="item me-2" data-toggle="tooltip" data-placement="top"
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
                                {{ $pizzas->links() }}

                                {{-- {{ $pizzas->appends(request()->query())->links() }} --}}
                            </div>

                            <!-- END DATA TABLE -->
                        </div>
                    @else
                        <h3 class="text-secondary text-center mt-5">There is no Pizza here</h3>
                    @endif
                </div>
            </div>
        </div>
        <!-- END MAIN CONTENT-->
    @endsection
