<x-guest-layout>


    <div class="container">
        <div class="row justify-content-center">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card overflow-hidden">
                    <div class="" style="background-color: #c655e640 !important">
                        <div class="row">
                            <div class="col-7">
                                <div class="text-primary p-4">
                                    <h5 class="" style="color: #64336F">Welcome Back !</h5>
                                    <p style="color: #64336F">Sign up to continue to Audita.</p>
                                </div>
                            </div>
                            <div class="col-5 align-self-end">
                                <img src="assets/images/profile-img.png" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">

                        <div class="p-2">
                            <form class="form-horizontal" action="{{ route('register.post') }}" method="POST">
                                @csrf

                                <div class="mb-3 mt-5 ">
                                    <label for="fullname" class="form-label">Fullname</label>
                                    <input type="text" name="fullname"
                                        class="form-control @error('fullname') is-invalid @enderror" id="fullname"
                                        placeholder="Enter fullname" value="{{ old('username') }}">

                                    @error('fullname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 ">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" name="email"
                                        class="form-control @error('email') is-invalid @enderror" id="email"
                                        placeholder="Enter email" value="{{ old('email') }}">

                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="input-group auth-pass-inputgroup">
                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Enter password" aria-label="Password"
                                            aria-describedby="password-addon">
                                        <button class="btn btn-light" type="button" id="password-addon"><i
                                                class="mdi mdi-eye-outline"></i></button>

                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>


                                <div class= "d-grid">
                                    <button type="submit" class="mt-3 btn btn-primary waves-effect waves-light">Sign
                                        Up</button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>


            </div>
        </div>
    </div>

</x-guest-layout>
