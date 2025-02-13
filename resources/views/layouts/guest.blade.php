<!doctype html>
<html lang="en">



@include('layouts.head')

<body>
    <div class="account-pages my-5 pt-sm-1">

        @include('sweetalert::alert')

        <center>
            <img style="margin: 0 auto 1rem auto; width: 9rem; padding: 0.2rem" src="{{ asset('bpsl_imgs/Audita-2.png') }}"
                alt="">
        </center>

        {{ $slot }}

        <div class="text-center">

            <div>
                <img style="margin: 0 auto; width: 10rem; padding: 0.5rem"
                    src="{{ asset('bpsl_imgs/purple-logo-bpsl.png') }}" alt="">
                <p>©
                    <script>
                        document.write(new Date().getFullYear())
                    </script> Powered By BPSL | IT Department - Application
                    Support
                </p>
            </div>
        </div>

    </div>
    <!-- end account-pages -->

    @include('layouts.footer')
    @stack('scripts')
</body>


</html>
