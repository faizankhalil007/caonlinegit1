@extends('layouts.app')
@section('container_fluid')
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> Over All Comment
            </a>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> Weak Topics Input
            </a>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-download fa-sm text-white-50"></i> Instructor Guide
            </a>
        </div>
        <!-- Content Row -->
        <div class="row">
            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Test</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $test_count }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Test Taken</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $taken_count }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pass Test</div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $total_pass }}</div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Pending Requests Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Fail Test</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_fail }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-comments fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Test Schedule -->
        <!-- Content Row -->
        <div class="row">
            <h1 class="h3 mb-0 text-gray-800">Test Schedule</h1>
            <hr>
            <!-- Content Column -->
            <div class="col-lg-12 mb-12">
                <!-- Collapsable Card Example -->
                @if (count($schedule_list) > 0)
                    @foreach ($schedule_list as $list)
                        <div class="card shadow mb-12 m-3">
                            <!-- Card Header - Accordion -->
                            <a href="#collapseCardExample{{ $list->id }}" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseCardExample{{ $list->id }}">
                                <h6 class="m-0 font-weight-bold text-primary pull-left d-inline">{{ $list->title }}</h6>
                                <h6 class="m-0 font-weight-bold text-primary d-inline float-right">{{ $list->test_date }}</h6>
                            </a>
                            <!-- Card Content - Collapse -->
                            <div class="collapse " id="collapseCardExample{{ $list->id }}">
                                <div class="card-body">
                                    {{ $list->message }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        <!-- Test Schedule -->
        <!-- Content Row -->
    </div>
@endsection