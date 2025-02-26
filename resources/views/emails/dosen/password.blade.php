<!DOCTYPE html>
<html>
<head>
    <title>Password Berhasil Diubah</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .password-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Berhasil Diubah</h2>
        <p>Password akun Anda telah berhasil diubah.</p>
        
        <div class="password-box">
            <p><strong>Password baru Anda:</strong> {{ $password }}</p>
        </div>
        
        <p class="warning">
            Demi keamanan:
            <ul>
                <li>Simpan password ini dengan aman</li>
                <li>Jangan memberikan password kepada siapapun</li>
            </ul>
        </p>
    </div>
</body>
</html>