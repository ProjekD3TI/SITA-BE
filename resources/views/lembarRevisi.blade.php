<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <style>
        @page {
            margin: 10mm 10mm 10mm 10mm;
            /* atas, kanan, bawah, kiri */
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            /* margin body di-nolkan agar tidak double dengan @page */
            padding: 0;
        }

        .logo {
            width: auto;
            height: 100px;
            max-width: 100%;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 20px;
        }

        .judul {
            display: inline-block;
            text-align: center;
            padding: 10px;
            vertical-align: middle;
        }

        .subtittle {
            font-size: 16px;
            font-weight: bold;
        }

        .alamat-text {
            font-size: 14px;
        }

        .konten {
            padding: 20px;
        }

        .konten-title {
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            width: 100%;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table-data th {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            width: 20%;
        }

        .table-data td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .catatan-revisi {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .catatan-dosen {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .catatan-revisi h4,
        .catatan-revisi div {
            margin: 0px;
        }

        .catatan {
            height: auto;
            padding: 5px;
            border: 1px solid black;
            border-radius: 5px;
        }

        .tanda-tangan {
            display: flex;
            margin-top: 20px;
            width: 100%;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .tanda-tangan .ttd {
            text-align: center;
            border: 1px solid black;
            width: 33.33%;
        }

        .tanggal {
            border-bottom: 1px solid black;
            margin: 0;
            padding: 10px;
        }


        .paraf {
            display: flex;
            flex-direction: column;
            gap: 50px;
            padding: 0 10px;
        }

        .penguji {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="data:asset/webp; base64, {{ base64_encode(file_get_contents(public_path('asset/logo.webp'))) }}"
            class="logo">
        <div class="judul">
            <span class="judul-text">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN,
                RISET, DAN TEKNOLOGI
                UNIVERSITAS SEBELAS MARET SEKOLAH VOKASI</span>
            <div class="subtittle">PROGRAM STUDI D3 TEKNIK INFORMATIKA</div>
            <div class="alamat"></div>
            <span class="alamat-text">Jalan Insinyur Sutami Nomor 36A Kentingan Surakarta 57126 Telp/Fax.
                (0271)663450</span>
            <div class="alamat-text">e-mail : kontak@d3ti.vokasi.uns.ac.id,Website : <a
                    href="http://d3ti.vokasi.uns.ac.id">http://d3ti.vokasi.uns.ac.id</a>
            </div>
        </div>
    </div>
    <hr style="border: 2px solid black;margin: 0 20px;">
    </div>
    <section class="konten">
        <h3 class="konten-title">LEMBAR REVISI UJIAN AKHIR</h3>
        <table class="table-data">
            <tr>
    <th>Tanggal Ujian</th>
    <td> {{$revisi->tanggal_ujian}}</td>
</tr>
            <tr>
                <th>Nama</th>
                <td>{{$revisi->nama_mahasiswa}}</td>
            </tr>
            <tr>
                <th>NIM</th>
                <td>{{$revisi->nim_mahasiswa}}</td>
            </tr>
            <tr>
                <th>Judul TA</th>
                <td>{{$revisi->judul_tugas_akhir}}</td>
            </tr>
        </table>
       <div class="catatan-revisi">
    <div>
        <h4>{{ $revisi->nama_ketua_penguji }}</h4>
        <div class="catatan">{!! $revisi->catatan_ketua_penguji !!}</div>
    </div>
    <div class="catatan-dosen">
        <h4>{{ $revisi->nama_sekretaris_penguji }}</h4>
        <div class="catatan">{!! $revisi->catatan_sekretaris_penguji !!}</div>
    </div>
    <div class="catatan-dosen">
        <h4>{{ $revisi->nama_penguji }}</h4>
        <div class="catatan">{!! $revisi->catatan_penguji !!}</div>
    </div>
</div>
<!-- ... -->
<div class="tanda-tangan">
    <div class="ttd">
        <div class="tanggal">Tanggal Selesai ......................</div>
        <div class="paraf">
            <p>Ketua</p>
            <p class="penguji">({{$revisi->nama_ketua_penguji}})</p>
        </div>
    </div>
    <div class="ttd">
        <div class="tanggal">Tanggal Selesai ......................</div>
        <div class="paraf">
            <p>Sekretaris</p>
            <p class="penguji">({{$revisi->nama_sekretaris_penguji}})</p>
        </div>
    </div>
    <div class="ttd">
        <div class="tanggal">Tanggal Selesai ......................</div>
        <div class="paraf">
            <p>Penguji</p>
            <p class="penguji">({{$revisi->nama_penguji}})</p>
        </div>
    </div>
</div>

    </section>
</body>

</html>
