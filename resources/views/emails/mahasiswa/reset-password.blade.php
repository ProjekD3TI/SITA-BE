<!DOCTYPE html>
<html>
<head>
    <title>Request Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning { color: #dc3545; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <p>Sistem Kami menerima permintaan untuk mereset password akun Anda.</p>
        <p>Klik tombol di bawah ini untuk melakukan reset password:</p>
        
        <a href="{{ $resetLink }}" class="button">Reset Password</a>
        
        <p>Link ini akan kadaluarsa dalam 60 menit.</p>
        <p class="warning">Jika Anda tidak meminta reset password, abaikan email ini.</p>
    </div>
</body>
</html>