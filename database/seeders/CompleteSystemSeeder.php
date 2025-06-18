<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Dosen;
use App\Models\User;
use App\Models\Admin;
use App\Models\Jabatan;
use App\Models\JabatanDosen;
use App\Models\AdminPengelola\Bhp;
use App\Models\AdminPengelola\Gedung;
use App\Models\AdminPengelola\LantaiGedung;
use App\Models\AdminPengelola\Ruang;
use App\Models\AdminPengelola\Merk;
use App\Models\AdminPengelola\Tipe;
use App\Models\AdminPengelola\JenisInventaris;
use App\Models\AdminPengelola\Inventaris;

class CompleteSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to avoid constraint issues during seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear existing data from tables
        $this->truncateAllTables();
        
        // First seed basic tables that don't have dependencies
        $this->seedAdmin();
        $this->seedJabatan();
        $this->seedLantaiGedung();
        $this->seedMerk();
        $this->seedTipe();
        
        // Now seed tables with dependencies
        $this->seedGedung();
        $this->seedRuang();
        $this->seedJenisInventaris();
        
        // Finally seed main data tables with factory
        $this->seedDosen();
        $this->seedMahasiswa();
        $this->seedInventaris();
        $this->seedBhp();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('All data seeded successfully!');
    }
    
    /**
     * Truncate all tables before seeding
     */
    private function truncateAllTables(): void
    {
        JabatanDosen::truncate();
        Jabatan::truncate();
        Dosen::truncate();
        User::truncate();
        Admin::truncate();
        Bhp::truncate();
        Inventaris::truncate();
        JenisInventaris::truncate();
        Merk::truncate();
        Tipe::truncate();
        Ruang::truncate();
        Gedung::truncate();
        LantaiGedung::truncate();
    }
    
    /**
     * Seed admin users
     */
    private function seedAdmin(): void
    {
        Admin::factory()->count(5)->create();
        
        // // Also create a predictable super admin
        // Admin::create([
        //     'name' => 'Super Admin',
        //     'email' => 'admin@example.com',
        //     'password' => Hash::make('password123'),
        //     'role' => 'super_admin',
        // ]);
        
        $this->command->info('Admin users seeded successfully');
    }
    
    /**
     * Seed jabatan (positions) - only 4 as specified
     */
    private function seedJabatan(): void
    {
        $jabatan = [
            ['name' => 'Dosen', 'description' => 'Dosen pengajar tetap'],
            ['name' => 'Kalab', 'description' => 'Kepala Laboratorium'],
            ['name' => 'Kaprodi', 'description' => 'Kepala Program Studi'],
            ['name' => 'Petugas', 'description' => 'Petugas Khusus']
        ];

        foreach ($jabatan as $jbt) {
            Jabatan::create($jbt);
        }
        
        $this->command->info('Jabatan seeded successfully');
    }
    
    /**
     * Seed dosen (lecturers) with their positions
     */
    private function seedDosen(): void
    {
        // Get Jabatan IDs for easy reference
        $dosenJabatanId = Jabatan::where('name', 'Dosen')->first()->id;
        $kalabJabatanId = Jabatan::where('name', 'Kalab')->first()->id;
        $kaprodiJabatanId = Jabatan::where('name', 'Kaprodi')->first()->id;
        $petugasJabatanId = Jabatan::where('name', 'Petugas')->first()->id;

        // Create 50 dosen using factory
        Dosen::factory()->count(50)->create()->each(function ($dosen) use ($dosenJabatanId, $kalabJabatanId, $kaprodiJabatanId, $petugasJabatanId) {
            // Always assign Dosen role
            JabatanDosen::create([
                'dosen_id' => $dosen->id,
                'jabatan_id' => $dosenJabatanId,
                'is_active' => 1,
            ]);
            
            // Randomly assign one additional role to some lecturers
            $rand = rand(1, 100);
            
            if ($rand <= 15) {
                // 15% chance to be Kalab
                JabatanDosen::create([
                    'dosen_id' => $dosen->id,
                    'jabatan_id' => $kalabJabatanId,
                    'is_active' => 1,
                ]);
            } elseif ($rand <= 25) {
                // 10% chance to be Kaprodi
                JabatanDosen::create([
                    'dosen_id' => $dosen->id,
                    'jabatan_id' => $kaprodiJabatanId,
                    'is_active' => 1,
                ]);
            } elseif ($rand <= 45) {
                // 20% chance to be Petugas
                JabatanDosen::create([
                    'dosen_id' => $dosen->id,
                    'jabatan_id' => $petugasJabatanId,
                    'is_active' => 1,
                ]);
            }
            // Remaining 55% have only Dosen role
        });
        
        $this->command->info('Dosen and JabatanDosen seeded successfully');
    }
    
    /**
     * Seed mahasiswa (students)
     */
    private function seedMahasiswa(): void
    {
        User::factory()->count(50)->create();
        
        $this->command->info('Mahasiswa seeded successfully');
    }
    
    /**
     * Seed lantai gedung (building floors)
     */
    private function seedLantaiGedung(): void
    {
        $lantai = [
            ['nama_lantai' => 'Lantai 1'],
            ['nama_lantai' => 'Lantai 2'],
            ['nama_lantai' => 'Lantai 3'],
            ['nama_lantai' => 'Lantai 4'],
            ['nama_lantai' => 'Lantai 5'],
            ['nama_lantai' => 'Basement'],
        ];
        
        foreach ($lantai as $lt) {
            LantaiGedung::create($lt);
        }
        
        $this->command->info('Lantai Gedung seeded successfully');
    }
    
    /**
     * Seed gedung (buildings)
     */
    private function seedGedung(): void
    {
        // Create 10 buildings
        for ($i = 1; $i <= 10; $i++) {
            $letter = chr(64 + $i); // A=65, B=66, etc.
            Gedung::create([
                'nama_gedung' => "Gedung $letter",
                'lokasi' => rand(0, 1) ? 'Kampus Utama' : 'Kampus Satelit',
                'id_lantai' => rand(1, 6), // Random number of floors
            ]);
        }
        
        $this->command->info('Gedung seeded successfully');
    }
    
    /**
     * Seed ruang (rooms)
     */
    private function seedRuang(): void
    {
        // Generate room names for each building and floor
        $gedungs = Gedung::all();
        $roomCount = 0;
        
        foreach ($gedungs as $gedung) {
            $totalLantai = $gedung->id_lantai;
            $letterCode = substr($gedung->nama_gedung, -1); // Get the letter
            
            for ($lantai = 1; $lantai <= $totalLantai; $lantai++) {
                // Create 5 rooms per floor
                for ($r = 1; $r <= 5; $r++) {
                    $roomNumber = $lantai * 100 + $r;
                    Ruang::create([
                        'nama_ruang' => "$letterCode$roomNumber",
                        'kapasitas' => rand(20, 50),
                        'fasilitas' => $this->getRandomFasilitas(),
                        'dimensi' => rand(6, 12) . 'x' . rand(6, 15) . ' meter',
                        'id_gedung' => $gedung->id_gedung,
                        'status' => 1,
                    ]);
                    $roomCount++;
                    
                    // If we've reached 50 rooms, we can stop
                    if ($roomCount >= 50) {
                        break 3; // Break out of all loops
                    }
                }
            }
        }
        
        $this->command->info('Ruang seeded successfully');
    }
    
    /**
     * Get random room facilities
     */
    private function getRandomFasilitas(): string
    {
        $fasilitas = [
            'AC', 'Proyektor', 'Papan Tulis', 'Smart Board', 
            'Komputer', 'Sound System', 'CCTV', 'WiFi',
            'Meja Lab', 'Kursi Lipat', 'Podium'
        ];
        
        $selected = array_rand(array_flip($fasilitas), rand(3, 6));
        return implode(', ', $selected);
    }
    
    /**
     * Seed merk (brands)
     */
    private function seedMerk(): void
    {
        $merks = [
            'Lenovo', 'HP', 'Dell', 'Asus', 'Acer', 'Samsung', 'LG', 'Epson', 
            'Canon', 'Apple', 'Microsoft', 'Sony', 'Toshiba', 'Sharp', 'Philips',
            'Logitech', 'BenQ', 'AOC', 'ViewSonic', 'MSI', 'Gigabyte', 'Intel',
            'AMD', 'Corsair', 'Razer', 'HyperX', 'SteelSeries', 'Panasonic',
            'Xiaomi', 'Huawei', 'Oppo', 'Vivo', 'Realme', 'iQOO', 'OnePlus',
            'Sennheiser', 'Bose', 'JBL', 'Harman Kardon', 'Edifier', 'Deli',
            'Faber Castell', 'Snowman', 'Brother', 'Fujitsu', 'IBM', 'Casio',
            'Konica Minolta', 'Western Digital', 'Seagate', 'Kingston', 'SanDisk'
        ];
        
        foreach ($merks as $index => $merkName) {
            Merk::create([
                'nama_merk' => $merkName
            ]);
            
            if ($index >= 49) {
                break; // Stop after 50 records
            }
        }
        
        $this->command->info('Merk seeded successfully');
    }
    
    /**
     * Seed tipe (types)
     */
    private function seedTipe(): void
    {
        $tipes = [
            'Laptop', 'Desktop', 'Proyektor', 'Printer', 'Scanner', 'Monitor',
            'Keyboard', 'Mouse', 'Speaker', 'Headset', 'Webcam', 'Microphone',
            'UPS', 'Router', 'Switch', 'Access Point', 'Server', 'Storage', 
            'Tablet', 'Smartphone', 'Camera', 'Drone', 'Tripod', 'Drawing Tablet',
            'Sound Card', 'Audio Interface', 'External HDD', 'Flash Drive',
            'Cooling Pad', 'Gaming Chair', 'Ergonomic Chair', 'Standing Desk',
            'Whiteboard', 'Flipchart', 'Podium', 'Laser Pointer', 'HDMI Cable',
            'VGA Cable', 'Network Cable', 'USB Hub', 'Power Strip', 'Adaptor',
            'Charging Dock', 'Barcode Scanner', 'Card Reader', 'Power Bank',
            'Extension Cord', 'RFID Reader', 'Smart Lock', 'Air Purifier'
        ];
        
        foreach ($tipes as $index => $tipeName) {
            Tipe::create([
                'nama_tipe' => $tipeName
            ]);
            
            if ($index >= 49) {
                break; // Stop after 50 records
            }
        }
        
        $this->command->info('Tipe seeded successfully');
    }
    
    /**
     * Seed jenis inventaris (inventory types)
     */
    private function seedJenisInventaris(): void
    {
        $merkCount = Merk::count();
        $tipeCount = Tipe::count();
        
        // Create 50 jenis inventaris
        for ($i = 1; $i <= 50; $i++) {
            $merkId = rand(1, $merkCount);
            $tipeId = rand(1, $tipeCount);
            
            $merk = Merk::find($merkId);
            $tipe = Tipe::find($tipeId);
            
            JenisInventaris::create([
                'nama_inventaris' => "{$merk->nama_merk} {$tipe->nama_tipe} " . rand(1000, 9999),
                'id_merk' => $merkId,
                'id_tipe' => $tipeId,
                'peruntukan' => $this->getRandomPeruntukan(),
                'status_import' => $this->getRandomStatusImport(),
            ]);
        }
        
        $this->command->info('Jenis Inventaris seeded successfully');
    }
    
    /**
     * Get random peruntukan (purpose)
     */
    private function getRandomPeruntukan(): string
    {
        $peruntukan = [
            'alat_lab', 'inventaris_kantor'
        ];
        
    $selected = array_rand(array_flip($peruntukan), rand(1, 1));
    return is_array($selected) ? implode(', ', $selected) : $selected;
    }
    private function getRandomStatusImport(): string
    {
        $status_impor = [
            'produk_luar_negeri', 'produk_dalam_negeri'
        ];
        
$selected = array_rand(array_flip($status_impor), rand(1, 1));
    return is_array($selected) ? implode(', ', $selected) : $selected;
    }
    
    /**
     * Seed inventaris (inventory items)
     */
    private function seedInventaris(): void
    {
        $jenisInventarisIds = JenisInventaris::pluck('id_jenis_inventaris')->toArray();
        $ruangIds = Ruang::pluck('id_ruang')->toArray();
        $conditions = ['baik', 'rusak', 'hilang'];
        
        for ($i = 1; $i <= 50; $i++) {
            $jenisId = $jenisInventarisIds[array_rand($jenisInventarisIds)];
            $jenis = JenisInventaris::find($jenisId);
            $tipe = Tipe::find($jenis->id_tipe);
            
            $noInv = 'INV-' . substr($tipe->nama_tipe, 0, 2) . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $qrPath = 'qr_' . strtolower(str_replace(' ', '_', $noInv)) . '.png';
            
            Inventaris::create([
                'id_jenis_inventaris' => $jenisId,
                'no_inventaris' => $noInv,
                'kondisi' => $conditions[array_rand($conditions)],
                'status' => rand(0, 1),
                'id_ruang' => $ruangIds[array_rand($ruangIds)],
                'tahun_pengadaan' => rand(2018, 2023),
                'qr_code_path' => $qrPath,
            ]);
        }
        
        $this->command->info('Inventaris seeded successfully');
    }
    
    /**
     * Seed bhp (consumable items)
     */
    private function seedBhp(): void
    {
        $bhpItems = [
            'Kertas HVS A4 80gr', 'Kertas HVS F4 80gr', 'Kertas HVS A3 80gr',
            'Tinta Printer HP', 'Tinta Printer Epson', 'Tinta Printer Canon',
            'Spidol Whiteboard', 'Spidol Permanen', 'Penghapus Papan Tulis',
            'Baterai AA', 'Baterai AAA', 'Baterai 9V', 'Baterai CR2032',
            'Flashdisk 16GB', 'Flashdisk 32GB', 'Flashdisk 64GB',
            'DVD-R', 'CD-R', 'Amplop Coklat', 'Amplop Putih',
            'Pensil 2B', 'Pulpen Hitam', 'Pulpen Biru', 'Pulpen Merah',
            'Staples', 'Isi Staples', 'Paper Clip', 'Binder Clip',
            'Selotip', 'Double Tape', 'Lakban', 'Isolasi',
            'Lem Stick', 'Lem Cair', 'Tinta Stempel', 'Stempel Pad',
            'Post-it Notes', 'Sticky Notes', 'Buku Tulis', 'Buku Folio',
            'Map Plastik', 'Map Kertas', 'Ordner', 'Box File',
            'Tali ID Card', 'Casing ID Card', 'Name Tag', 'Label Nama',
            'Tissue', 'Hand Sanitizer'
        ];
        
        // Create 50 BHP items
        foreach ($bhpItems as $index => $itemName) {
            Bhp::create([
                'nama_bhp' => $itemName,
                'kondisi_bagus' => rand(50, 1000),
                'kondisi_rusak' => rand(0, 20),
                'qr_code_path' => 'qr_bhp_' . strtolower(str_replace([' ', '-'], '_', $itemName)) . '.png',
                'peruntukan' => $this->getRandomPeruntukan(),
            ]);
            
            if ($index >= 49) {
                break; // Stop after 50 records
            }
        }
        
        $this->command->info('BHP seeded successfully');
    }
}