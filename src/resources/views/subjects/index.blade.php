@extends('app')
@section('title') {{ $pageTitle }} @endsection
@section('content')

    @include('partials.flash')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-md-4">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5><i class="fa fa-shopping-bag"></i> {{ trans('common.create')}}</h5>
                        <div class="ibox-tools">
                            <a style="margin-top: -8px;" href="{{ route( strtolower($pageTitle) . '.index') }}" class="btn btn-primary"><i
                                    class="fa fa-list"></i> {{ trans('common.list')}}</a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <!---FORM--->
                        <form role="form" method="post" action="{{route( strtolower($pageTitle) . '.store')}}">
                        @csrf
                        <!---{{ $pageTitle }} Name--->
                            <div class="form-group">
                                <label for="name" class="font-bold">{{ trans('customer.name')}}</label>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ trans('customer.name')}}" maxlength="255" class="form-control" required>
                                <span class="form-text m-b-none text-danger"> @error('name') {{ $message }} @enderror </span>
                            </div>
                        <!---{{ $pageTitle }} Mobile--->
                            <div class="form-group">
                                <label for="mobile" class="font-bold">{{ trans('customer.mobile')}}</label>
                                <input type="text" name="mobile" value="{{ old('mobile') }}" maxlength="11" placeholder="{{ trans('customer.mobile')}}" class="form-control" required>
                                <span class="form-text m-b-none text-danger"> @error('mobile') {{ $message }} @enderror </span>
                            </div>
                        <!---{{ $pageTitle }} Balance--->
                            <div class="form-group">
                                <label for="balance" class="font-bold">{{ trans('customer.balance')}}</label>
                                <input type="text" name="balance" value="{{ old('balance') }}" placeholder="{{ trans('customer.balance')}}" class="form-control" required>
                                <span class="form-text m-b-none text-danger"> @error('balance') {{ $message }} @enderror </span>
                            </div>
                        <!---{{ $pageTitle }} Address--->
                            <div class="form-group">
                                <label for="address" class="font-bold">{{ trans('customer.address')}}</label>
                                <textarea name="address" class="form-control"></textarea>
                                <span class="form-text m-b-none text-danger"> @error('address') {{ $message }} @enderror </span>
                            </div>
                        <!---{{ $pageTitle }} CONTROL BUTTON--->
                            <div class="form-group">
                                <button class="btn btn-success" type="submit"><i class="fa fa-fw fa-lg fa-check-circle"></i>{{ trans('common.submit')}}</button>
                                <a class="btn btn-danger" href="{{route( strtolower($pageTitle) . '.index')}}"><i class="fa fa-fw fa-lg fa-arrow-left"></i>{{ trans('common.go_back')}}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="ibox ">
                    <div class="ibox-title">
                        <h5><i class="fa fa-list"></i> {{ trans('common.list')}}</h5>
                        <div class="ibox-tools">
                            <a style="margin-top: -8px;" href="{{ route( strtolower($pageTitle) . '.create') }}" class="btn btn-primary"><i
                                    class="fa fa-plus"></i> {{ trans('common.create')}}</a>

                            <a style="margin-top: -8px;" href="{{ route( strtolower($pageTitle) . '.restore') }}" class="btn btn-primary"><i
                                    class="fa fa-window-restore"></i> {{ trans('common.restore')}}</a>
                        </div>
                    </div>
                    <div class="ibox-content">

                        <div class="table-responsive">
                            @include('partials.datatable')
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
