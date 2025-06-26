<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$templatePath = __DIR__ . '/template.pdf';
if (!file_exists($templatePath)) {
    die("File template.pdf tidak ditemukan di: $templatePath");
}

// Data yang akan diisi ke field PDF
$fields = [
    'judul_tugas_akhir-1'           => 'Sistem Informasi Akademik',
    'judul_tugas_akhir-2'           => 'Berbasis Web',
    'judul_tugas_akhir-3'           => 'Studi Kasus Universitas X',
    'nama_divisi_tugas_akhir'       => 'Ir. Bambang Sutejo',
    'nama_kepala_program_studi'     => 'Dr. Rina Kusuma',
    'nama_ketua_penguji-1'          => 'Dr. Andi Wijaya',
    'nama_ketua_penguji-3'          => 'Dr. Andi Wijaya',
    'nama_ketua_penguji-4'          => 'Dr. Andi Wijaya',
    'nama_mahasiswa-1'              => 'Raffael Aldrich Setyawan',
    'nama_mahasiswa-2'              => 'Raffael Aldrich Setyawan',
    'nama_mahasiswa-3'              => 'Raffael Aldrich Setyawan',
    'nama_mahasiswa-4'              => 'Raffael Aldrich Setyawan',
    'nama_penguji-1'                => 'Dr. Budi Santoso',
    'nama_penguji-3'                => 'Dr. Budi Santoso',
    'nama_penguji-4'                => 'Dr. Budi Santoso',
    'nama_penguji-5'                => 'Dr. Budi Santoso',
    'nama_sekretaris_penguji-1'     => 'Dr. Siti Aminah',
    'nama_sekretaris_penguji-3'     => 'Dr. Siti Aminah',
    'nama_sekretaris_penguji-4'     => 'Dr. Siti Aminah',
    'nik_ketua_penguji-1'           => '1987654321',
    'nik_ketua_penguji-3'           => '1987654321',
    'nik_ketua_penguji-4'           => '1987654321',
    'nik_penguji-1'                 => '1987654323',
    'nik_penguji-3'                 => '1987654323',
    'nik_penguji-4'                 => '1987654323',
    'nik_penguji-5'                 => '1987654323',
    'nik_sekretaris_penguji-1'      => '1987654322',
    'nik_sekretaris_penguji-3'      => '1987654322',
    'nik_sekretaris_penguji-4'      => '1987654322',
    'nilai_bimbingan_ta-1'          => '92',
    'nilai_bimbingan_ta-2'          => '93',
    'nilai_proposal_ta'             => '89',
    'nilai_rata-rata_ujian'         => '87.67',
    'nilai_ujian_ketua_penguji'     => '85',
    'nilai_ujian_penguji'           => '90',
    'nilai_ujian_sekretaris_penguji'=> '88',
    'nim_mahasiswa-1'               => '123456789',
    'nim_mahasiswa-2'               => '123456789',
    'nim_mahasiswa-3'               => '123456789',
    'nim_mahasiswa-4'               => '123456789',
    'nip_divisi_tugas_akhir'        => '1976543211',
    'nip_kepala_program_studi'      => '1976543210',
    'rata-rata_nilai_ujian_ta'      => '88.5',
    'tanggal_ujian-1'               => '2025-06-15',
    'tanggal_ujian-2'               => '2025-06-15',
    'tanggal_ujian-3'               => '2025-06-15',
    'tanggal_ujian-4'               => '2025-06-15',
    'tanggal_ujian-5'               => '2025-06-15',
    'tanggal_ujian-6'               => '2025-06-15',
    'tanggal_ujian-7'               => '2025-06-15',
    'tanggal_ujian-8'               => '2025-06-15',
    'total_nilai_tugas_akhir'       => '90',
    'waktu_ujian-1'                 => '09:00 WIB',
    'waktu_ujian-2'                 => '09:00 WIB',
    'waktu_ujian-3'                 => '09:00 WIB',
    'waktu_ujian-4'                 => '09:00 WIB',
];

$pdf = new FPDM($templatePath);
$pdf->Load($fields, true); // true = case-insensitive
$pdf->Merge();
$pdf->Output('F', __DIR__ . '/output.pdf'); // Simpan ke file output.pdf

echo "PDF berhasil diisi dan disimpan sebagai output.pdf\n";
