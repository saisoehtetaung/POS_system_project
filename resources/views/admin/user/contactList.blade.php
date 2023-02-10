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
                                <h2 class="title-1">User Messages</h2>

                            </div>
                        </div>

                    </div>





                    <div class="row my-2">
                        <div class="col-1 text-center bg-white shadow-sm py-1">
                            <h4> <i class="fas fa-database me-2"></i>~{{ $contacts->total() }}</h4>
                        </div>
                    </div>

                    @if (count($contacts) != 0)
                        <div class="table-responsive table-responsive-data2">
                            <table class="table table-data2 text-center">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Message</th>
                                        <th>Sent Time</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($contacts as $contact)
                                        <tr class="tr-shadow my-1">
                                            <td>{{ $contact->id }}</td>
                                            <td>{{ $contact->name }}</td>
                                            <td>{{ $contact->email }}</td>
                                            <td>{{ Str::substr($contact->message, 0, 20) }}...</td>
                                            <td>{{ $contact->created_at->format('F-j-Y') }}</td>
                                            <td>
                                                <div class="table-data-feature">
                                                    <a href="{{ route('admin#userContactInfo', $contact->id) }}">
                                                        <button class="item me-2" data-toggle="tooltip" data-placement="top"
                                                            title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </a>


                                                    <a href="{{ route('admin#deleteContact', $contact->id) }}">
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

                                {{ $contacts->appends(request()->query())->links() }}
                            </div>

                            <!-- END DATA TABLE -->
                        </div>
                    @else
                        <h3 class="text-secondary text-center mt-5">There is no Contact here</h3>
                    @endif
                </div>
            </div>
        </div>
        <!-- END MAIN CONTENT-->
    @endsection
