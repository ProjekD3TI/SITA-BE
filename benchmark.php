<?php

function testJumlah($jumlah) {
    try {
        $start = microtime(true);

        $data = [
            'id_jenis_inventaris' => 1,
            'no_inventaris' => '3.05.02.01.001',
            'kondisi' => 'baik',
            'id_ruang' => 1,
            'tahun_pengadaan' => 2023,
            'jumlah' => $jumlah
        ];

        $ch = curl_init("http://localhost:8000/api/admin_pengelola/inventaris/create");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Jika pakai Bearer Token, aktifkan baris ini
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer 1UBRxRnA2rgI1hyLj9jlf78GvF4JEu3Kp0ynaS5L44ad6c06', // ganti dengan token asli
        ]);

        $response = curl_exec($ch);
        $end = microtime(true);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $elapsed = round($end - $start, 3);

        return [
            'jumlah' => $jumlah,
            'time' => $elapsed,
            'success' => $httpCode === 200,
            'http_code' => $httpCode,
            'raw' => $response
        ];
    } catch (\Throwable $e) {
        return [
            'jumlah' => $jumlah,
            'time' => 0,
            'success' => false,
            'http_code' => 500,
            'raw' => $e->getMessage()
        ];
    }
}

// Ambil input dari user
echo "Masukkan jumlah inventaris yang ingin diuji: ";
$input = trim(fgets(STDIN));
$jumlah = is_numeric($input) ? (int)$input : 0;

if ($jumlah <= 0) {
    echo "❌ Jumlah tidak valid. Harus berupa angka lebih dari 0.\n";
    exit;
}

// Jalankan pengujian
$result = testJumlah($jumlah);
echo "Jumlah: {$result['jumlah']} | Time: {$result['time']}s | Status: {$result['http_code']}\n";

if (!$result['success']) {
    echo "❌ Gagal menyimpan inventaris. Status: {$result['http_code']}\n";
    echo "Respon: {$result['raw']}\n";
} else {
    echo "✅ Berhasil menyimpan {$result['jumlah']} inventaris.\n";
}
