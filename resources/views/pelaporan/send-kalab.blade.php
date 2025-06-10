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
    * Nomor Laporan: {{ $nomor_laporan }}<br>
    * Tanggal Kejadian: {{ $tanggal_kejadian }}<br> 
    * Status: <b>{{ $status }}</b> 
</p>

<p>
    Ringkasan Tanggapan:<br>
    @if(is_string($tanggapan))
        {{ $tanggapan }}
    @elseif(is_array($tanggapan) || is_object($tanggapan))
        @foreach(json_decode($tanggapan, true) as $item)
            <div>
                <b>{{ $item['user'] }} ({{ date('d-m-Y H:i', strtotime($item['timestamp'])) }}):</b><br>
                {{ $item['text'] }}
            </div>
            @if(!$loop->last)<hr>@endif
        @endforeach
    @endif
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