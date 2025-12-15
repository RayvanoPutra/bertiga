<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Bank Mini</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Masuk ke Akun</h2>

            <!-- Menampilkan error jika login gagal -->
            @if ($errors->any())
                <div style="color: red;">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                <div>
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required />
                </div>
                <div>
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required />
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
