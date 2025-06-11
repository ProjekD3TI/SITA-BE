<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>[NOTIFIKASI] Pelaporan Kekerasan {{ $nomor_laporan_kekerasan }}</title>
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
            background-color: #dc3545;
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
        .urgent-tag {
            display: inline-block;
            background-color: #dc3545;
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .detail-box {
            background-color: #f9f9f9;
            border-left: 4px solid #dc3545;
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
            background-color: #dc3545;
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
        .note {
            padding: 15px;
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
        .note-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>[NOTIFIKASI] Pelaporan Kekerasan</h1>
        </div>
        
        <div class="content">
            <p>Yth. Petugas PPKS D3TI dan {{ $nama_pelapor }},</p>
            
            <div class="urgent-tag">Memerlukan Perhatian Segera</div>
            
            <p>Ini adalah notifikasi otomatis untuk menginformasikan bahwa terdapat laporan kekerasan yang baru masuk ke dalam sistem dan memerlukan tindakan dari Anda.</p>
            
            <div class="detail-box">
                <div class="detail-item">
                    <span class="detail-label">Nomor Laporan:</span> {{ $nomor_laporan_kekerasan }}
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
                    <span style="color: #dc3545; font-weight: bold;">Terkirim dan Tersimpan dalam Sistem</span>
                </div>
            </div>
            
            <p>Laporan ini sekarang tersedia untuk ditinjau dalam sistem pelaporan. Mohon segera ditindaklanjuti sesuai dengan prosedur yang berlaku.</p>
            
            <div class="note">
                <div class="note-title">Penting:</div>
                <p>Laporan kekerasan harus ditangani sesuai dengan kebijakan institusi dan protokol keamanan yang berlaku. Pastikan untuk merahasiakan identitas pelapor jika diperlukan untuk keamanan.</p>
            </div>
            
            <p>Jika Anda memiliki pertanyaan mengenai laporan ini atau memerlukan informasi tambahan, silakan hubungi pelapor secara langsung atau melalui sistem pelaporan.</p>
            
            <p>Terima kasih atas perhatian dan tindakan cepat Anda.</p>
            
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