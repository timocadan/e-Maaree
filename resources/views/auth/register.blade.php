@extends('layouts.login_master')

@section('content')
    <div class="page-content login-cover">

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Content area -->
            <div class="content d-flex justify-content-center align-items-center">

                <!-- Register card -->
                <form class="login-form" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <i class="icon-user-plus icon-2x text-warning-400 border-warning-400 border-3 rounded-round p-3 mb-3 mt-1"></i>
                                <h5 class="mb-0">Create landlord account</h5>
                                <span class="d-block text-muted">Register for e-maaree admin</span>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger alert-styled-left alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                    <span class="font-weight-semibold">Oops!</span> {{ implode('<br>', $errors->all()) }}
                                </div>
                            @endif

                            <div class="form-group">
                                <input type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" placeholder="Name" required autofocus>
                                @if ($errors->has('name'))
                                    <span class="invalid-feedback"><strong>{{ $errors->first('name') }}</strong></span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" placeholder="E-Mail Address" required>
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" placeholder="Password" required>
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback"><strong>{{ $errors->first('password') }}</strong></span>
                                @endif
                            </div>

                            <div class="form-group">
                                <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password" required>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">Register <i class="icon-circle-right2 ml-2"></i></button>
                            </div>

                            <div class="form-group text-center">
                                <a href="{{ route('login') }}" class="text-muted">Already have an account? Sign in</a>
                            </div>
                        </div>
                    </div>
                </form>

            </div>

        </div>

    </div>
@endsection
