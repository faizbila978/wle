<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\AlergiPelanggan;
use App\Models\Obat;
use App\Models\ScreeningAlergi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AllergyScreeningController extends Controller
{
    public function history()
    {
        $pelanggans = Pelanggan::with('alergis')->latest()->get();
        return view('skrining.history', compact('pelanggans'));
    }

    public function index(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'nullable|string|max:100',
            'nama_alergi'    => 'nullable|string|max:255',
            'keyword'        => 'nullable|string|max:100',
        ]);

        $namaPelanggan   = trim((string) $request->input('nama_pelanggan'));
        $namaAlergiInput = trim((string) $request->input('nama_alergi'));
        $keyword         = trim((string) $request->input('keyword'));

        $screeningResults = [];
        $allergyList      = [];
        $safeCount = $unsafeCount = 0;

        if ($namaPelanggan !== '') {
            $pelanggan = Pelanggan::firstOrCreate(['nama_pelanggan' => $namaPelanggan]);

            // Alergi yang dipakai untuk skrining ini:
            // - jika kolom alergi diisi -> pakai isian itu (dan simpan yang baru)
            // - jika kosong -> pakai alergi yang sudah tersimpan
            if ($namaAlergiInput !== '') {
                $names = collect(explode(',', $namaAlergiInput))
                    ->map(fn ($n) => trim($n))
                    ->filter()
                    ->unique(fn ($n) => mb_strtolower($n));

                $alergiRecords = $names->map(fn ($n) => AlergiPelanggan::firstOrCreate([
                    'id_pelanggan' => $pelanggan->id_pelanggan,
                    'nama_alergi'  => $n,
                ]));
            } else {
                $alergiRecords = $pelanggan->alergis()->get();
            }

            $allergyList = $alergiRecords->pluck('nama_alergi')->all();

            $query = Obat::with('kandungans')->where('stok', '>', 0);

            if ($keyword !== '') {
                $obatTable = (new Obat)->getTable();
                $query->where(function ($q) use ($keyword, $obatTable) {
                    $q->where('nama_obat', 'like', "%{$keyword}%")
                      ->orWhere('jenis', 'like', "%{$keyword}%")
                      ->orWhereHas('kandungans', fn ($k) => $k->where('nama_kandungan', 'like', "%{$keyword}%"));

                    // cari juga di semua kolom lain tabel obat (indikasi, kategori, khasiat, dll.)
                    $skip = ['id_obat', 'stok', 'harga', 'created_at', 'updated_at', 'deleted_at'];
                    foreach (Schema::getColumnListing($obatTable) as $col) {
                        if (!in_array($col, $skip) && !str_starts_with($col, 'id_') && !str_ends_with($col, '_id')) {
                            $q->orWhere($col, 'like', "%{$keyword}%");
                        }
                    }
                });
            }

            foreach ($query->get() as $obat) {
                $triggers = [];

                foreach ($obat->kandungans as $kandungan) {
                    foreach ($alergiRecords as $alergi) {
                        if ($m = self::matchAllergy($kandungan->nama_kandungan, $alergi->nama_alergi)) {
                            $triggers[] = [
                                'kandungan' => $kandungan->nama_kandungan,
                                'alergi'    => $alergi->nama_alergi,
                                'tipe'      => $m['type'],
                                'label'     => $m['label'],
                                'id_alergi' => $alergi->id_alergi,
                            ];
                        }
                    }
                }

                // jika obat tidak punya data kandungan, cek juga nama obatnya
                if ($obat->kandungans->isEmpty()) {
                    foreach ($alergiRecords as $alergi) {
                        if ($m = self::matchAllergy($obat->nama_obat, $alergi->nama_alergi)) {
                            $triggers[] = [
                                'kandungan' => $obat->nama_obat,
                                'alergi'    => $alergi->nama_alergi,
                                'tipe'      => $m['type'],
                                'label'     => $m['label'],
                                'id_alergi' => $alergi->id_alergi,
                            ];
                        }
                    }
                }

                $isSafe    = empty($triggers);
                $hasilText = $isSafe
                    ? 'Aman Digunakan (Alternatif Cocok)'
                    : 'Tidak Aman (Memicu Alergi: ' . collect($triggers)->pluck('kandungan')->unique()->implode(', ') . ')';

                ScreeningAlergi::updateOrCreate(
                    ['id_pelanggan' => $pelanggan->id_pelanggan, 'id_obat' => $obat->id_obat],
                    [
                        'id_alergi'     => $triggers[0]['id_alergi'] ?? null,
                        'tgl_screening' => Carbon::now(),
                        'hasil'         => mb_substr($hasilText, 0, 250),
                    ]
                );

                $isSafe ? $safeCount++ : $unsafeCount++;

                $screeningResults[] = [
                    'obat'     => $obat,
                    'is_safe'  => $isSafe,
                    'hasil'    => $hasilText,
                    'triggers' => $triggers,
                ];
            }

            // obat aman tampil lebih dulu, lalu urut nama
            usort($screeningResults, fn ($a, $b) =>
                [$b['is_safe'], $a['obat']->nama_obat] <=> [$a['is_safe'], $b['obat']->nama_obat]
            );
        }

        $recentPelanggan = Pelanggan::with('alergis')->latest()->take(5)->get();

        return view('skrining.index', compact(
            'namaPelanggan', 'namaAlergiInput', 'keyword', 'allergyList',
            'screeningResults', 'safeCount', 'unsafeCount', 'recentPelanggan'
        ));
    }

    // ==================== LOGIKA PENCOCOKAN ALERGI ====================

    // kunci kanonik => varian penulisan / nama lain
    private const INGREDIENTS = [
        'amoxicillin'   => ['amoxicillin', 'amoxycillin', 'amoxicilin', 'amoxilin', 'amoksisilin', 'amoksilin', 'amoxil'],
        'ampicillin'    => ['ampicillin', 'ampisilin', 'ampicilin'],
        'cloxacillin'   => ['cloxacillin', 'kloksasilin', 'cloxacilin'],
        'benzylpenicillin' => ['benzylpenicillin', 'penicillin g', 'penicillin v', 'phenoxymethylpenicillin'],
        'cefadroxil'    => ['cefadroxil', 'sefadroksil'],
        'cefixime'      => ['cefixime', 'sefiksim', 'cefixim'],
        'cefuroxime'    => ['cefuroxime', 'sefuroksim'],
        'cephalexin'    => ['cephalexin', 'cefalexin', 'sefaleksin'],
        'ceftriaxone'   => ['ceftriaxone', 'seftriakson'],
        'cefotaxime'    => ['cefotaxime', 'sefotaksim'],
        'sulfamethoxazole' => ['sulfamethoxazole', 'sulfametoksazol', 'cotrimoxazole', 'kotrimoksazol', 'co-trimoxazole'],
        'paracetamol'   => ['paracetamol', 'parasetamol', 'acetaminophen', 'asetaminofen'],
        'ibuprofen'     => ['ibuprofen', 'ibuprophen'],
        'aspirin'       => ['aspirin', 'asetosal', 'acetylsalicylic', 'asam asetilsalisilat'],
        'diclofenac'    => ['diclofenac', 'diklofenak'],
        'naproxen'      => ['naproxen', 'naproksen'],
        'mefenamic'     => ['mefenamic', 'asam mefenamat', 'mefenamat'],
        'ketoprofen'    => ['ketoprofen'],
        'meloxicam'     => ['meloxicam', 'meloksikam'],
        'azithromycin'  => ['azithromycin', 'azitromisin'],
        'erythromycin'  => ['erythromycin', 'eritromisin'],
        'clarithromycin'=> ['clarithromycin', 'klaritromisin'],
        'chlorpheniramine' => ['chlorpheniramine', 'klorfeniramin', 'ctm'],
        'dextromethorphan' => ['dextromethorphan', 'dekstrometorfan', 'dmp'],
        'guaifenesin'   => ['guaifenesin', 'gliseril guaiakolat', 'gg'],
        'ambroxol'      => ['ambroxol', 'ambroksol'],
        'noscapine'     => ['noscapine', 'noskapin', 'noscapin', 'noscapine hcl'],
    ];

    // nama golongan => [alias golongan, anggota (kunci kanonik)]
    private const GROUPS = [
        'penisilin' => [
            'alias'   => ['penisilin', 'penicillin', 'penisillin', 'golongan penisilin', 'beta laktam', 'beta-lactam'],
            'members' => ['amoxicillin', 'ampicillin', 'cloxacillin', 'benzylpenicillin'],
        ],
        'sefalosporin' => [
            'alias'   => ['sefalosporin', 'cephalosporin', 'cefalosporin'],
            'members' => ['cefadroxil', 'cefixime', 'cefuroxime', 'cephalexin', 'ceftriaxone', 'cefotaxime'],
        ],
        'sulfa' => [
            'alias'   => ['sulfa', 'sulfonamide', 'sulfonamid'],
            'members' => ['sulfamethoxazole'],
        ],
        'makrolida' => [
            'alias'   => ['makrolida', 'macrolide'],
            'members' => ['azithromycin', 'erythromycin', 'clarithromycin'],
        ],
        'nsaid' => [
            'alias'   => ['nsaid', 'aine', 'antiinflamasi nonsteroid', 'anti inflamasi nonsteroid'],
            'members' => ['ibuprofen', 'aspirin', 'diclofenac', 'naproxen', 'mefenamic', 'ketoprofen', 'meloxicam'],
        ],
    ];

    private static function normalizeText(string $text): string
    {
        $t = mb_strtolower($text);
        $t = preg_replace('/\d+([.,]\d+)?\s*(mg|mcg|ml|g|iu|%)?/u', ' ', $t);
        $t = preg_replace('/[^\p{L}\s\-]/u', ' ', $t);
        return trim(preg_replace('/\s+/u', ' ', $t));
    }

    /** Kunci kanonik zat aktif yang terdeteksi dalam teks. */
    private static function detectIngredients(string $text): array
    {
        $norm   = self::normalizeText($text);
        $tokens = explode(' ', $norm);
        $found  = [];

        foreach (self::INGREDIENTS as $key => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($norm, $alias)) {
                    $found[$key] = true;
                    continue 2;
                }
                // toleransi 1 huruf salah ketik untuk kata tunggal >= 6 huruf
                if (!str_contains($alias, ' ') && strlen($alias) >= 6) {
                    foreach ($tokens as $tok) {
                        if (strlen($tok) >= 6 && levenshtein($tok, $alias) <= 1) {
                            $found[$key] = true;
                            continue 3;
                        }
                    }
                }
            }
        }
        return array_keys($found);
    }

    /** Nama golongan yang disebut dalam teks alergi. */
    private static function detectGroups(string $text): array
    {
        $norm  = self::normalizeText($text);
        $found = [];
        foreach (self::GROUPS as $name => $g) {
            foreach ($g['alias'] as $alias) {
                if ($norm === $alias || preg_match('/(^|\s)' . preg_quote($alias, '/') . '(\s|$)/u', $norm)) {
                    $found[] = $name;
                    break;
                }
            }
        }
        return $found;
    }

    /**
     * @return array|null  null = tidak cocok; selain itu ['type' => ..., 'label' => ...]
     */
    private static function matchAllergy(string $kandungan, string $alergi): ?array
    {
        $kandIng = self::detectIngredients($kandungan);
        $algIng  = self::detectIngredients($alergi);

        if ($common = array_intersect($kandIng, $algIng)) {
            return ['type' => 'zat aktif', 'label' => ucfirst(reset($common))];
        }

        foreach (self::detectGroups($alergi) as $group) {
            if (array_intersect($kandIng, self::GROUPS[$group]['members'])) {
                return ['type' => 'golongan', 'label' => 'Golongan ' . ucfirst($group)];
            }
        }

        // cadangan: pencocokan teks biasa untuk zat yang tidak ada di daftar
        $k = self::normalizeText($kandungan);
        $a = self::normalizeText($alergi);
        if (strlen($a) >= 4 && strlen($k) >= 4 && (str_contains($k, $a) || str_contains($a, $k))) {
            return ['type' => 'teks', 'label' => $alergi];
        }

        return null;
    }
}