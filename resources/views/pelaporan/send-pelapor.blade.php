<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[NOTIFIKASI] Pelaporan Kejadian {{ $nomor_laporan }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #3498db;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .notification-tag {
            display: inline-block;
            background-color: #3498db;
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .detail-box {
            background-color: #f9f9f9;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 20px;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: bold;
            display: inline-block;
            min-width: 150px;
        }
        .action-button {
            display: inline-block;
            background-color: #3498db;
            color: #ffffff;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 15px;
            font-size: 14px;
            color: #666666;
            border-top: 1px solid #eeeeee;
        }
        .info-box {
            padding: 15px;
            background-color: #ebf8ff;
            border-left: 4px solid #3498db;
            margin: 20px 0;
        }
        .info-title {
            font-weight: bold;
            color: #2c5282;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>[NOTIFIKASI] Pelaporan Kejadian</h1>
        </div>
        
        <div class="content">
            <p>Yth. Kepala Laboratorium,</p>
            
            <div class="notification-tag">Laporan Baru</div>
            
            <p>Ini adalah notifikasi otomatis untuk menginformasikan bahwa terdapat laporan kejadian baru yang telah masuk ke dalam sistem dan memerlukan peninjauan Anda.</p>
            
            <div class="detail-box">
                <div class="detail-item">
                    <span class="detail-label">Nomor Laporan:</span> {{ $nomor_laporan }}
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal Kejadian:</span> {{ \Carbon\Carbon::parse($tanggal_kejadian)->setTimezone('Asia/Jakarta')->isoFormat('DD MMMM YYYY, HH:mm') }} WIB
                </div>
                <div class="detail-item">
                    <span class="detail-label">Diajukan Oleh:</span> {{ $nama_pelapor }}
                </div>
                <div class="detail-item">
                    <span class="detail-label">Waktu Pelaporan:</span> {{ \Carbon\Carbon::now()->setTimezone('Asia/Jakarta')->isoFormat('DD MMMM YYYY, HH:mm') }} WIB
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span> 
                    <span style="color: #f39c12; font-weight: bold;">Terkirim dan Tersimpan dalam Sistem</span>
                </div>
            </div>
            
            <p>Laporan ini sekarang tersedia untuk ditinjau dalam sistem pelaporan. Silakan masuk ke akun Anda untuk mengakses laporan lengkap dan memberikan tanggapan atau persetujuan yang diperlukan.</p>
            
            <div class="info-box">
                <div class="info-title">Informasi:</div>
                <p>Mohon untuk meninjau laporan ini sesuai dengan prosedur standar yang berlaku. Setiap laporan harus ditangani dengan tepat untuk menjamin lingkungan kerja dan pembelajaran yang baik.</p>
            </div>
            
            <p>Jika Anda memiliki pertanyaan mengenai laporan ini atau memerlukan informasi tambahan, silakan hubungi pelapor secara langsung melalui informasi kontak yang tersedia di sistem.</p>
            
            <p>Terima kasih atas perhatian dan tindak lanjut Anda.</p>
            
            <p>Salam,</p>
            <p><strong>Sistem Informasi Pelaporan D3TI UNS</strong></p>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} D3TI UNS - Sistem Informasi Pelaporan</p>
        </div>
    </div>
</body>
</html>
