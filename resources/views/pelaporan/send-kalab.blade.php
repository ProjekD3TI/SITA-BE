<h2>
    [TANGGAPAN] Pelaporan Kejadian {{ $nomor_laporan }}
</h2>

<p>
    Kepada Yth. {{ $nama_pelapor }},
</p>
<p>
    Ini adalah notifikasi otomatis untuk menginformasikan bahwa kepala laboratorium telah memberikan tanggapan atas laporan yang Anda ajukan.
</p>
<p>
    Detail Laporan:<br>
    Nomor Laporan: {{ $nomor_laporan }}<br>
    Tanggal Kejadian: {{ $tanggal_kejadian }}<br> 
    Status: <b>{{ $status }}</b> 
</p>

<p>
    Ringkasan Tanggapan:<br>
    {!! $tanggapan !!}
</p>

<p> 
    Jika Anda memiliki pertanyaan lebih lanjut mengenai tanggapan yang diberikan, silakan hubungi kepala laboratorium melalui sistem atau secara langsung.
</p>
<p>
    Terima kasih atas pelaporan Anda.
</p>
<p>
    Salam,
</p>
<p>
    Sistem Informasi Pelaporan
</p>