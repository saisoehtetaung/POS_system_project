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
                                <h2 class="title-1">User List</h2>

                            </div>
                        </div>

                    </div>

                    <div class="row my-2">
                        <div class="col-1 text-center bg-white shadow-sm py-1">
                            <h4> <i class="fas fa-database me-2"></i>~{{ $users->total() }}</h4>
                        </div>
                    </div>

                    @if (count($users) != 0)
                        <div class="table-responsive table-responsive-data2">
                            <table class="table table-data2 text-center">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Gender</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Role</th>
                                        <th></th>

                                    </tr>
                                </thead>
                                <tbody id="dataList">

                                    @foreach ($users as $user)
                                        <tr>
                                            <input type="hidden" name="" id="userId" value="{{ $user->id }}">
                                            <td>
                                                @if ($user->image == null)
                                                    @if ($user->gender == 'male')
                                                        <img src="{{ asset('image/default.png') }}"
                                                            class="img-thumbnail shadow-sm" style="width: 100px;"
                                                            alt="Default" />
                                                    @else
                                                        <img src="{{ asset('image/female_default.png') }}"
                                                            class="img-thumbnail shadow-sm" style="width: 100px;"
                                                            alt="Default" />
                                                    @endif
                                                @else
                                                    <img src="{{ asset('storage/' . $user->image) }}" class="shadow"
                                                        style="width: 100px;" alt="profile" />
                                                @endif
                                            </td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->gender }}</td>
                                            <td>{{ $user->phone }}</td>
                                            <td>{{ $user->address }}</td>
                                            <td class="w-100">
                                                <select class="form-control text-center statusChange p-1 ">
                                                    <option value="user"
                                                        @if ($user->role == 'user') selected @endif>User</option>
                                                    <option value="admin"
                                                        @if ($user->role == 'admin') selected @endif>Admin</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button class="btn deleteAccount" title="Delete Account">
                                                    <i class="fas fa-times-circle fs-3 text-danger"></i>
                                                </button>
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                            <div class="m-5">
                                {{-- {{ $pizzas->links() }} --}}

                                {{ $users->appends(request()->query())->links() }}
                            </div>

                            <!-- END DATA TABLE -->
                        </div>
                    @else
                        <h3 class="text-secondary text-center mt-5">There is no User here</h3>
                    @endif
                </div>
            </div>
        </div>
        <!-- END MAIN CONTENT-->
    @endsection

    @section('scriptSection')
        <script>
            $(document).ready(function() {
                $(".deleteAccount").click(function() {
                    $parentNode = $(this).parents('tr');
                    $userId = $parentNode.find('#userId').val();

                    $.ajax({
                        method: 'get',
                        url: '/user/delete/role',
                        data: {
                            'userId': $userId,
                        },
                        dataType: 'json'
                    });
                    location.reload();
                });

                $(".statusChange").change(function() {
                    $currentStatus = $('.statusChange').val();
                    $parentNode = $(this).parents('tr');
                    $userId = $parentNode.find('#userId').val();

                    $.ajax({
                        method: 'get',
                        url: '/user/change/role',
                        data: {
                            'userId': $userId,
                            'role': $currentStatus
                        },
                        dataType: 'json'
                    });

                    location.reload();
                })


            })
        </script>
    @endsection
