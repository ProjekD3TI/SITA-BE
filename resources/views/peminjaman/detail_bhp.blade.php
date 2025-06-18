<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Peminjaman BHP - {{ $peminjaman->id_peminjaman_bhp }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #000;
            margin: 40px;
            line-height: 1.5;
        }

        h2 {
            text-align: center;
            font-size: 20pt;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .subtitle {
            text-align: center;
            font-size: 12px;
            margin-bottom: 30px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .info-table td {
            padding: 6px 4px;
            vertical-align: top;
        }

        .label {
            width: 160px;
            font-weight: bold;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
        }

        .qr-code {
            position: absolute;
            bottom: 40px;
            left: 40px;
            width: 100px;
        }

        @media print {
            body {
                margin: 0;
            }
            .qr-code {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <h2>Invoice Peminjaman BHP</h2>
    <div class="subtitle">{{ $tanggalPinjam }}</div>

    <table class="info-table">
        <tr>
            <td class="label">Nama Peminjam</td>
            <td>:
                {{
                    collect(explode(' ', $peminjaman->nama_peminjam))
                        ->map(function($word, $i) {
                            if ($i < 2) return $word;
                            return mb_substr($word, 0, 1) . '.';
                        })
                        ->implode(' ')
                }}
            </td>
        </tr>
        <tr>
            <td class="label">Nomor Induk</td>
            <td>: {{ $peminjaman->ni_peminjam }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Telepon</td>
            <td>: {{ $peminjaman->no_telp ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Keperluan</td>
            <td>: {{ $peminjaman->kegunaan }}</td>
        </tr>
    </table>

    <div class="section-title">Detail Barang Habis Pakai</div>

    <table class="info-table">
    <tr>
        <td class="label">Nama Barang</td>
        <td>: {{ $bhp->nama_bhp }}</td>
    </tr>
    <tr>
        <td class="label">Jumlah Peminjaman</td>
        <td>: {{ $peminjaman->jumlah_penggunaan }} unit</td>
    </tr>
</table>


    <div class="signature">
        <p>Surakarta, {{ now()->locale('id')->translatedFormat('d F Y') }}</p>
        <br><br><br>
        <p><strong>{{ $peminjaman->nama_peminjam }}</strong></p>
    </div>

<div class="qr-code">
    @if(!empty($peminjaman->qr_code_path) && file_exists(public_path('storage/bhp/peminjaman/qrcode/' . $peminjaman->qr_code_path)) && is_file(public_path('storage/bhp/peminjaman/qrcode/' . $peminjaman->qr_code_path)))
        <img src="{{ public_path('storage/bhp/peminjaman/qrcode/' . $peminjaman->qr_code_path) }}" width="120">
    @else
        <p>QR Code tidak ditemukan</p>
    @endif
</div>

</body>
</html>
