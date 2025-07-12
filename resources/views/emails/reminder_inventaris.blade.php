<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat Peminjaman Inventaris Terlambat</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            background-color: #f44336;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin-bottom: 20px;
        }
        .warning-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .content {
            padding: 0 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .info-table td {
            padding: 8px 5px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: bold;
            width: 40%;
        }
        .info-highlight {
            background-color: #fff3cd;
            padding: 10px;
            border-left: 4px solid #ffc107;
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .action-btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="warning-icon">⚠️</span>
            <h2>PENGINGAT PEMINJAMAN INVENTARIS TERLAMBAT</h2>
        </div>
        <div class="content">
            <p>Kepada Yth,<br><strong>{{ $peminjaman->nama_peminjam }}</strong></p>
            
            <p>Berdasarkan catatan kami, terdapat peminjaman inventaris yang telah melewati batas waktu pengembalian dengan detail sebagai berikut:</p>
            
            <table class="info-table">
                <tr>
                    <td class="label">Nomor Referensi</td>
                    <td>: {{ $peminjaman->referensi }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor Induk</td>
                    <td>: {{ $peminjaman->ni_peminjam }}</td>
                </tr>
                <tr>
                    <td class="label">Jumlah Item</td>
                    <td>: {{ $barang_belum_kembali }} buah</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Kembali</td>
                    <td>: {{ $tanggal_kembali }}</td>
                </tr>
                <tr>
                    <td class="label">Keterlambatan</td>
                    <td>: <strong>{{ $hari_terlambat }} hari</strong></td>
                </tr>
            </table>
            
            <div class="info-highlight">
                <p>Mohon segera mengembalikan item inventaris yang dipinjam ke Unit Sarana dan Prasarana untuk menghindari sanksi administratif sesuai dengan peraturan yang berlaku.</p>
            </div>
            
            @if(isset($detail_barang) && count($detail_barang) > 0)
            <p><strong>Detail Item yang Belum Dikembalikan:</strong></p>
            <table class="info-table" style="border: 1px solid #ddd;">
                <tr style="background-color: #f2f2f2;">
                    <td style="border: 1px solid #ddd; padding: 8px;"><strong>No</strong></td>
                    <td style="border: 1px solid #ddd; padding: 8px;"><strong>Nama Barang</strong></td>
                    <td style="border: 1px solid #ddd; padding: 8px;"><strong>Jenis</strong></td>
                </tr>
                @foreach($detail_barang as $index => $item)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $index + 1 }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $item->inventaris->no_inventaris }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $item->inventaris->jenisInventaris->nama_inventaris }}</td>
                </tr>
                @endforeach
            </table>
            @endif
            
            <p>Jika Anda telah mengembalikan item tersebut atau memiliki pertanyaan, silakan hubungi kami melalui:</p>
            <p>
                Email: sarana.prasarana@example.ac.id<br>
                Telp: (0271) 123456<br>
                WhatsApp: 0812-3456-7890
            </p>
            
            <p>Terima kasih atas perhatian dan kerjasamanya.</p>
            
            <p>Hormat kami,<br>
            <strong>Unit Sarana dan Prasarana</strong></p>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis, mohon untuk tidak membalas email ini.</p>
            <p>&copy; {{ date('Y') }} Unit Sarana dan Prasarana</p>
        </div>
    </div>
</body>
</html>