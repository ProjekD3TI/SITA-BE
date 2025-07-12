<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman - {{ $peminjaman->referensi }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #000;
            margin: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 16pt;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            padding-top: 20px;
        }

        .info-table td {
            padding: 4px 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #fff;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 30px;
        }

        .notes {
            margin-bottom: 15px;
        }

        .notes-label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .terms {
            border-top: 1px solid #000;
            padding-top: 10px;
            margin-top: 20px;
        }

        .signature-container {
            display: table;
            width: 100%;
            margin-top: 5px;
            border-collapse: separate;
            border-spacing: 10px;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            /* border: 1px solid #000; */
            padding: 15px;
            height: 120px;
            text-align: center;
            vertical-align: top;
            box-sizing: border-box;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .signature-date {
            margin-bottom: 40px;
        }

        .signature-name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
            margin-top: 50px;
        }

        .footer hr {
            margin: 20px 0;
        }

        .qr-code {
            position: absolute;
            top: 0px;
            left: 20px;
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
    <div class="qr-code">
            @if(!empty($peminjaman->qr_code_path) && file_exists(public_path('storage/inventaris/peminjaman/qrcode/' . $peminjaman->qr_code_path)) && is_file(public_path('storage/inventaris/peminjaman/qrcode/' . $peminjaman->qr_code_path)))
                <img src="{{ public_path('storage/inventaris/peminjaman/qrcode/' . $peminjaman->qr_code_path) }}" width="120">
            @else
                <p>QR Code tidak ditemukan</p>
            @endif
        </div>

    <h2>Invoice Peminjaman Inventaris</h2>

    <table class="info-table">
        <tr>
            <td><strong>No. Referensi</strong></td>
            <td>: {{ $peminjaman->referensi }}</td>
            <td><strong>Tanggal Peminjaman</strong></td>
            <td>: {{ $tanggalPinjam }}</td>
        </tr>
        <tr>
            <td><strong>Nama Peminjam</strong></td>
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
            <td><strong>Nomor Induk Peminjam</strong></td>
            <td>: {{ $peminjaman->ni_peminjam }}</td>
            <!-- <td><strong>Status</strong></td> -->
            <!-- <td>: {{ $peminjaman->status == 1 ? 'Selesai' : 'Dipinjam' }}</td> -->
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">No</th>
                <th>Nama Inventaris</th>
                <th>No. Inventaris</th>

            </tr>
        </thead>
        <tbody>
            @foreach($detail as $item)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $item->inventaris->jenisInventaris->nama_inventaris ?? '-' }}</td>
                <td>{{ $item->inventaris->no_inventaris ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="notes">
            <div class="notes-label">Kegunaan:</div>
            <div>{{ $peminjaman->kegunaan ?? '-' }}</div>
        </div>

        <div class="terms">
            <div><strong>Catatan:</strong></div>
            <div>1. Peminjam bertanggung jawab penuh atas barang yang dipinjam .</div>
            <div>2. Peminjam wajib menyerahkan invoice ini pada saat pengembalian barang.</div>
            <div>3. Segala bentuk kerusakan atau kehilangan wajib dilaporkan.</div>
            <div>4. Peminjam tidak diperkenankan memindahtangankan barang tanpa izin.</div>
        </div>

        <!-- <div class="signature">
            <p>Surakarta, {{ now()->locale('id')->translatedFormat('d F Y') }}</p>
            <br><br><br>
            <p><strong>{{
                    collect(explode(' ', $peminjaman->nama_peminjam))
                        ->map(function($word, $i) {
                            if ($i < 2) return $word;
                            return mb_substr($word, 0, 1) . '.';
                        })
                        ->implode(' ')
                }}</strong></p>
        </div> -->
        <p style="text-align: right; margin-right: 30px;">Surakarta, {{ now()->locale('id')->translatedFormat('d F Y') }}</p>
        <p style="text-align: center;">Mengetahui</p>
        <div class="signature-container">
            <div class="signature-box">
                <div class="signature-title">Admin</div>
                <div class="signature-name">
                    {{
                        collect(explode(' ', $peminjaman->created_by))
                            ->take(2)
                            ->implode(' ')
                    }}
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-date">Peminjam</div>
                <div class="signature-name">
                    {{
                        collect(explode(' ', $peminjaman->nama_peminjam))
                            ->take(2)
                            ->implode(' ')
                    }}
                </div>
            </div>
        </div>
        
        
    </div>

</body>
</html>
