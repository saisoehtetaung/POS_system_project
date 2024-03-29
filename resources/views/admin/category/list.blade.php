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
                                <h2 class="title-1">Category List</h2>

                            </div>
                        </div>
                        <div class="table-data__tool-right">
                            <a href="{{ route('category#createPage') }}">
                                <button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                    <i class="zmdi zmdi-plus"></i>add category
                                </button>
                            </a>

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
                            <form action="{{ route('category#list') }}" method="get" class="d-flex">
                                <input type="text" name="searchkey" class="form-control" placeholder="Search..." />
                                <button type="submit" class="btn btn-dark text-white">
                                    <i class="fas
                                fa-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="row my-2">
                        <div class="col-1 text-center bg-white shadow-sm py-1">
                            <h4> <i class="fas fa-database me-2"></i>~{{ $categories->total() }}</h4>
                        </div>
                    </div>

                    @if (count($categories) != 0)
                        <div class="table-responsive table-responsive-data2">
                            <table class="table table-data2 text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category Name</th>
                                        <th>Category Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($categories as $category)
                                        <tr class="tr-shadow my-1">
                                            <td>{{ $category->id }}</td>
                                            <td class='col-6'>{{ $category->name }}</td>
                                            <td>{{ $category->created_at->format('j-F-Y') }}</td>
                                            <td>
                                                <div class="table-data-feature">
                                                    {{-- <button class="item" data-toggle="tooltip" data-placement="top"
                                                        title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button> --}}
                                                    <a href="{{ route('category#edit', $category->id) }}">
                                                        <button class="item me-2" data-toggle="tooltip" data-placement="top"
                                                            title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </a>
                                                    <a href="{{ route('category#delete', $category->id) }}">
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
                                {{ $categories->links() }}

                                {{-- {{ $categories->appends(request()->query())->links() }} --}}
                            </div>
                        </div>
                    @else
                        <h3 class="text-secondary text-center mt-5">There is no Category here</h3>
                    @endif
                    <!-- END DATA TABLE -->
                </div>
            </div>
        </div>
    </div>
    <!-- END MAIN CONTENT-->
@endsection
