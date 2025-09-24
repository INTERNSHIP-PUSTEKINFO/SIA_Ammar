<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PORTAL DPR RI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .login-card {
            width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 120px;
            margin-bottom: 15px;
        }
        .title {
            font-weight: bold;
            color: #2c3e50;
        }
        .subtitle {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
        }
        .btn-login {
            background-color: #2c3e50;
            border: none;
            padding: 10px;
            font-weight: 500;
        }
        .btn-login:hover {
            background-color: #1a2530;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #7f8c8d;
        }
        .form-check-label {
            font-size: 14px;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card login-card p-4">
        <div class="logo-container">
            <!-- Logo DPR RI (placeholder) -->
            <div class="logo-placeholder" style="width: 100px; height: 100px; background-color: #2c3e50; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-weight: bold;">

            </div>
            <h4 class="title mt-3">PORTAL DPR RI</h4>
            <p class="subtitle">SINGLE SIGN ON (SSO) SYSTEM</p>
        </div>

        <!-- Error message (akan muncul hanya jika ada error) -->
        <div class="error-message" style="display: none;">
            Tidak ada hak akses ke aplikasi: PARLEMENREMAJAADMIN, atau Login dan Password salah!
        </div>

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Pengguna</label>
                <input type="text" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="showPassword">
                <label class="form-check-label" for="showPassword">Show Password</label>
            </div>

            <button type="submit" class="btn btn-login w-100 text-white">Login</button>
        </form>

        <div class="footer">
            <p>Single Idel(Tilly Network Login)</p>
            <p>6 Sekreteraci, Serviced DPR RI</p>
            <p>Pusat Training dan Informati (POSTDINFO)</p>
        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan/menyembunyikan password
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordField = document.getElementById('password');
            if (this.checked) {
                passwordField.type = 'text';
            } else {
                passwordField.type = 'password';
            }
        });

        // Simulasi error message (dalam implementasi nyata, ini akan dikontrol oleh backend)
        // Untuk keperluan demonstrasi, kita akan memunculkan error setelah 1 detik
        setTimeout(function() {
            const errorDiv = document.querySelector('.error-message');
            errorDiv.style.display = 'block';
        }, 1000);
    </script>
</body>
</html>