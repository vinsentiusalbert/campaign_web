<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class BackController extends Controller
{
    public function registerStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'nope' => 'required',
            'email' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
            'role' => 'required|in:Super,Admin,Tsel',
        ]);
        $data = [
            'name' => $request->name,
            'nohp' => $request->nope,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make('123456'),
            'status' => 'Aktif'
        ];
        $nope = $request->nope;
        if (Str::startsWith($nope, '08')) {
            $nope = '62' . substr($nope, 1);
        }
        $data['nohp'] = $nope;
        User::create($data);
        return redirect()->back()->with('success', 'User berhasil didaftarkan!');
    }
    public function login(Request $request)
    {
        // 1. Validasi form
        $request->validate([
            'email' => [
                'required',
                'email',
                // 'regex:/^[a-zA-Z0-9._%+-]+@telkomsel\.co\.id$/', // hanya email @telkomsel.co.id
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', // semua domain
            ],
            'password' => 'required',
        ], [
            'email.regex' => 'Email harus menggunakan domain @telkomsel.co.id',
        ]);

        // 2. Coba login
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // 3. Cek status
            if ($user->status !== 'Aktif') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda belum aktif.',
                ])->withInput();
            }
                        // dd($user->role);

            // 4. Arahkan sesuai role
            switch ($user->role) {
                case 'User':
                    return redirect()->route('campaign-waba-interaktif.index');
                case 'KAM':
                    return redirect()->route('campaign-kam-dashboard.index');
                case 'Tsel':
                case 'Super':
                case 'Admin':
                default:
                    return redirect()->route('campaign-mobile.index'); // fallback
            }
        }

        // 5. Kalau gagal login
        return back()->withErrors([
            'email' => 'Email atau Password Anda salah.',
        ])->withInput();
    }

    public function getPadiUmkmData(Request $request)
    {
        if ($request->has('tanggal') && !empty($request->tanggal)) {
            $tanggal = $request->tanggal;
            $date = Carbon::parse($tanggal);
            $month = $date->month;
            $year = $date->year;
            $data = DB::table('summary_padi_umkm')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $data = DB::table('summary_padi_umkm')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }
    public function getPadiUmkmSummary(Request $request)
    {
        $query = DB::table('summary_padi_umkm');

        if ($request->has('tanggal') && !empty($request->tanggal)) {
            $tanggal = $request->tanggal;
            $date = Carbon::parse($tanggal);
            $month = $date->month;
            $year = $date->year;
            $query->whereMonth('created_at', $month)
                ->whereYear('created_at', $year);
        }

        $totalForm = $query->count();

        // Clone query for aggregation
        $topupQuery = clone $query;

        $jumlahTopup = $topupQuery->sum('jumlah_topup');
        $totalTopup = $topupQuery->sum('total_topup');

        return response()->json([
            'total_form'    => $totalForm,
            'jumlah_topup'  => $jumlahTopup,
            'total_topup'   => $totalTopup,
        ]);
    }
    public function getEventSponsorship(Request $request)
    {
        $data = EventSponsorhip::orderBy('created_at', 'desc')->get();

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }


    public function getCreatorPartner(Request $request)
    {
        // Ambil semua creator + total_invited (subquery)
        $creators = CreatorPartner::select(
            'creator_partner.*',
            DB::raw('(SELECT COUNT(*) FROM rekruter_kol WHERE rekruter_kol.referral_code = creator_partner.referral_code) as total_invited')
        )
            ->when($request->area, function ($query, $area) {
                $query->where('creator_partner.area', $area);
            })
            ->when($request->region, function ($query, $region) {
                $query->where('creator_partner.regional', $region);
            })
            ->when($request->jenis_kol, function ($query, $jenis_kol) {
                $query->where('creator_partner.jenis_kol', $jenis_kol);
            })
            ->orderBy('creator_partner.created_at', 'desc')
            ->get();

        // Hitung tier untuk setiap creator partner
        $creators->transform(function ($creator) use ($request) {
            // ambil list email rekruter untuk referral ini
            $rekruterEmails = DB::table('rekruter_kol')
                ->where('referral_code', $creator->referral_code)
                ->pluck('email')
                ->toArray();

            if (empty($rekruterEmails)) {
                $creator->tier = '-';
            } else {
                // ambil total topup per email
                $topupPerAkun = DB::table('revenue_kol')
                    ->whereIn('email', $rekruterEmails)
                    ->select('email', DB::raw('SUM(jumlah_top_up) as total_topup'))
                    ->groupBy('email')
                    ->pluck('total_topup', 'email');

                // set minimum sesuai jenis_kol
                $minTopup = 0;
                if ($creator->jenis_kol === 'KOL as a Buzzer') {
                    $minTopup = 250000;
                } elseif ($creator->jenis_kol === 'KOL as a Seller Online/Afiliate') {
                    $minTopup = 200000;
                }

                // hitung berapa akun yang memenuhi minimum
                $eligibleAccounts = collect($topupPerAkun)->filter(function ($total) use ($minTopup) {
                    return $total >= $minTopup;
                })->count();

                // tentukan tier
                if ($eligibleAccounts >= 30) {
                    $creator->tier = 'Platinum';
                } elseif ($eligibleAccounts >= 20) {
                    $creator->tier = 'Gold';
                } elseif ($eligibleAccounts >= 10) {
                    $creator->tier = 'Silver';
                } elseif ($eligibleAccounts >= 5) {
                    $creator->tier = 'Bronze';
                } else {
                    $creator->tier = '-';
                }
            }

            // Filter berdasarkan tier (kalau user pilih filter tier)
            if ($request->tier && $creator->tier !== $request->tier) {
                $creator->hide = true; // kasih flag biar nanti dihapus
            }

            return $creator;
        });

        // Buang data yang tidak sesuai tier filter
        if ($request->tier) {
            $creators = $creators->reject(function ($creator) {
                return isset($creator->hide) && $creator->hide === true;
            });
        }

        // Kembalikan collection ke DataTables
        return DataTables::of($creators)->make(true);
    }
    public function getRekrutBuzzer(Request $request)
    {
        // Ambil referral_code semua creator dengan jenis KOL = Buzzer
        $buzzerReferralCodes = DB::table('creator_partner')
            ->where('jenis_kol', 'KOL as a Buzzer')
            ->pluck('referral_code');

        // Ambil semua rekruter yang referral_code-nya ada di list buzzer
        $rekrut = DB::table('rekruter_kol')
            ->whereIn('referral_code', $buzzerReferralCodes)
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform hasil biar sesuai table kamu
        $rekrut->transform(function ($item) {
            // Hitung total topup untuk email rekruter ini
            $totalTopup = DB::table('revenue_kol')
                ->where('email', $item->email)
                ->sum('jumlah_top_up');

            // Tentukan nilai minimal topup & remarks
            $minTopup = 250000; // karena jenis_kol = Buzzer
            $item->nilai_min_topup = $minTopup;
            $item->jumlah_top_up = $totalTopup;
            $item->remarks = $totalTopup >= $minTopup ? 'Eligible' : 'Not Eligible';

            return $item;
        });

        return DataTables::of($rekrut)
            ->addColumn('nilai_min_topup', function ($row) {
                return number_format($row->nilai_min_topup, 0, ',', '.');
            })
            ->addColumn('jumlah_top_up', function ($row) {
                return number_format($row->jumlah_top_up, 0, ',', '.');
            })
            ->addColumn('remarks', function ($row) {
                return $row->remarks;
            })
            ->make(true);
    }
    public function getRekruterInfluencer(Request $request)
    {
        // Ambil referral_code semua creator dengan jenis KOL = Influencer
        $influencerReferralCodes = DB::table('creator_partner')
            ->where('jenis_kol', 'KOL as a Seller Online/Afiliate')
            ->pluck('referral_code');

        // Ambil semua rekruter yang referral_code-nya ada di list influencer
        $rekrut = DB::table('rekruter_kol')
            ->whereIn('referral_code', $influencerReferralCodes)
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform hasil biar sesuai table kamu
        $rekrut->transform(function ($item) {
            // Hitung total topup untuk email rekruter ini
            $totalTopup = DB::table('revenue_kol')
                ->where('email', $item->email)
                ->sum('jumlah_top_up');

            // Tentukan nilai minimal topup & remarks
            $minTopup = 200000; // karena jenis_kol = Influencer
            $item->nilai_min_topup = $minTopup;
            $item->jumlah_top_up = $totalTopup;
            $item->remarks = $totalTopup >= $minTopup ? 'Eligible' : 'Not Eligible';

            return $item;
        });

        return DataTables::of($rekrut)
            ->addColumn('nilai_min_topup', function ($row) {
                return number_format($row->nilai_min_topup, 0, ',', '.');
            })
            ->addColumn('jumlah_top_up', function ($row) {
                return number_format($row->jumlah_top_up, 0, ',', '.');
            })
            ->addColumn('remarks', function ($row) {
                return $row->remarks;
            })
            ->make(true);
    }

    public function getAreaMarcom(Request $request)
    {
        $bulanIni = now()->format('Y-m'); // e.g. 2025-09

        // Ambil statistik per area (unikkan kol dengan COUNT DISTINCT)
        $areas = DB::table('creator_partner as cp')
            ->leftJoin('rekruter_kol as rk', DB::raw('cp.referral_code COLLATE utf8mb4_unicode_ci'), '=', DB::raw('rk.referral_code COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('revenue_kol as rv', DB::raw('rk.email COLLATE utf8mb4_unicode_ci'), '=', DB::raw('rv.email COLLATE utf8mb4_unicode_ci'))
            ->select(
                'cp.area',
                DB::raw("COUNT(DISTINCT cp.id) as total_kol"),
                DB::raw("SUM(CASE WHEN cp.jenis_kol = 'KOL as a Buzzer' THEN 1 ELSE 0 END) as jumlah_buzzer"),
                DB::raw("SUM(CASE WHEN cp.jenis_kol = 'KOL as a Seller Online/Afiliate' THEN 1 ELSE 0 END) as jumlah_influencer"),
                DB::raw("COUNT(DISTINCT rk.id) as total_rekruter"),
                DB::raw("COALESCE(SUM(rv.jumlah_top_up), 0) as total_topup"),
                // max topup dari revenue_kol yang terjadi pada bulan ini (untuk rule 3)
                DB::raw("MAX(CASE WHEN DATE_FORMAT(rv.created_at, '%Y-%m') = '{$bulanIni}' THEN rv.jumlah_top_up ELSE 0 END) as max_topup_bulan_ini")
            )
            ->groupBy('cp.area')
            ->orderBy('cp.area', 'asc')
            ->get();

        // Untuk setiap area: hitung bintang sesuai aturan:
        // 1) ada KOL -> 1 bintang
        // 2) ada minimal 1 creator di area yang punya >=5 akun rekruter eligible (eligible = akun punya total_topup >= minTopup tergantung jenis_kol)
        // 3) ada akun yang topup >= 1.000.000 pada bulan ini -> 1 bintang
        foreach ($areas as $areaRow) {
            $bintang = 0;

            // Rule 2: cek apakah ada creator di area yang sudah mencapai Tier >= Bronze
            // (Tier Bronze: ada >=5 rekruter dengan total_topup >= minTopup; minTopup berbeda per jenis_kol)
            $adaTierBronze = false;

            // Ambil semua creator di area (referral_code + jenis_kol)
            $creators = DB::table('creator_partner')
                ->where('area', $areaRow->area)
                ->select('referral_code', 'jenis_kol')
                ->get();

            foreach ($creators as $creator) {
                if (empty($creator->referral_code)) continue;

                // ambil list email rekruter untuk referral ini
                $emails = DB::table('rekruter_kol')
                    ->where('referral_code', $creator->referral_code)
                    ->pluck('email')
                    ->filter() // hapus null/empty
                    ->unique()
                    ->values()
                    ->toArray();

                if (empty($emails)) continue;

                // set minimum per jenis_kol
                $minTopup = $creator->jenis_kol === 'KOL as a Buzzer' ? 250000 : 200000;

                // ambil total topup per email (all-time)
                $topupPerEmail = DB::table('revenue_kol')
                    ->whereIn('email', $emails)
                    ->select('email', DB::raw('SUM(jumlah_top_up) as total'))
                    ->groupBy('email')
                    ->pluck('total')
                    ->toArray();

                // hitung berapa email yang memenuhi minTopup
                $eligibleCount = collect($topupPerEmail)->filter(fn($tot) => $tot >= $minTopup)->count();

                if ($eligibleCount >= 5) {
                    $adaTierBronze = true;
                    break; // cukup ada 1 creator yang memenuhi -> area dapat bintang tambahan
                }
            }

            if ($adaTierBronze) $bintang += 2;

            // Rule 3: ada akun rekruter yang topup >= 1jt pada bulan ini?
            if ((int)$areaRow->max_topup_bulan_ini >= 1000000) {
                $bintang++;
            }

            $areaRow->remarks = $bintang; // hanya kirim angka 0..3
        }

        return DataTables()->of($areas)
            ->addIndexColumn()
            ->make(true);
    }


    public function getSimpatiTiktok(Request $request)
    {
        $data = DB::table('summary_simpati_tiktok as sst')
            ->select(
                'sst.*'
            )
            ->orderBy('sst.created_at', 'desc')
            ->get();

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }

    public function getReferralChampionAm(Request $request)
    {
        $data = ReferralChampionAm::orderBy('created_at', 'desc')->get();

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }
    public function getSultamRacing(Request $request)
    {
        $data = SultamRacing::orderBy('created_at', 'desc')->get();

        return DataTables()->of($data)
            ->addIndexColumn()
            ->make(true);
    }
    public function getVoucherStats()
    {
        // Menghitung total semua voucher
        $totalVoucher = DB::table('myads_voucher')->count();

        // Menghitung voucher yang sudah diklaim (user_id tidak null)
        $totalClaimed = DB::table('myads_voucher')->whereNotNull('user_id')->count();

        // Menghitung sisa voucher yang belum diklaim
        $totalNotClaimed = $totalVoucher - $totalClaimed;

        $data = [
            'total_voucher'   => $totalVoucher,
            'total_claimed'   => $totalClaimed,
            'total_not_claim' => $totalNotClaimed,
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getVouchers(Request $request)
    {
        // Mengecek apakah ini adalah request AJAX dari DataTables
        if ($request->ajax()) {
            $data = DB::table('myads_voucher')
                ->select('id', 'voucher', 'created_at', 'user_id')
                ->orderBy('created_at', 'desc'); // Mengurutkan dari yang terbaru

            return DataTables::of($data)
                ->addColumn('status_klaim', function ($row) {
                    // Logika untuk menampilkan status klaim
                    if ($row->user_id) {
                        return 'claimed'; // Kirim 'claimed' jika sudah diklaim
                    }
                    return 'not_claimed'; // Kirim 'not_claimed' jika belum
                })
                ->addColumn('aksi', function ($row) {
                    // Menambahkan tombol Edit dan Hapus dengan ikon di setiap baris
                    $btn = '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-primary btn-sm editVoucher"><i class="fas fa-edit"></i> Edit</a> ';
                    $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" class="btn btn-danger btn-sm hapusVoucher"><i class="fas fa-trash-alt"></i> Hapus</a>';
                    return $btn;
                })
                ->rawColumns(['aksi']) // Memberitahu DataTables bahwa kolom 'aksi' berisi HTML
                ->make(true);
        }
    }

    /**
     * Function 2: Menyimpan data voucher baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function tambahVoucher(Request $request)
    {
        // Validasi input dari form
        $validator = Validator::make($request->all(), [
            'voucher' => 'required|string|max:255|unique:myads_voucher,voucher',
        ], [
            'voucher.required' => 'Kolom voucher wajib diisi.',
            'voucher.unique'   => 'Kode voucher ini sudah ada.',
        ]);

        // Jika validasi gagal, kembalikan pesan error
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Jika validasi berhasil, simpan data ke database
        DB::table('myads_voucher')->insert([
            'voucher'    => $request->voucher,
            // user_id bisa ditambahkan jika perlu, contoh: 'user_id' => auth()->id()
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return response()->json(['success' => 'Voucher berhasil ditambahkan.']);
    }

    /**
     * Function 3: Mengupdate data voucher yang sudah ada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVoucher(Request $request, $id)
    {
        // Validasi input, pastikan voucher unik tapi abaikan id saat ini
        $validator = Validator::make($request->all(), [
            'voucher' => 'required|string|max:255|unique:myads_voucher,voucher,' . $id,
        ], [
            'voucher.required' => 'Kolom voucher wajib diisi.',
            'voucher.unique'   => 'Kode voucher ini sudah digunakan oleh data lain.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cari voucher berdasarkan ID
        $voucher = DB::table('myads_voucher')->where('id', $id)->first();

        // Jika voucher tidak ditemukan
        if (!$voucher) {
            return response()->json(['error' => 'Data voucher tidak ditemukan.'], 404);
        }

        // Update data di database
        DB::table('myads_voucher')->where('id', $id)->update([
            'voucher'    => $request->voucher,
            'updated_at' => Carbon::now(),
        ]);

        return response()->json(['success' => 'Voucher berhasil diperbarui.']);
    }

    /**
     * Function 4: Menghapus data voucher.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function hapusVoucher($id)
    {
        // Cari voucher berdasarkan ID
        $voucher = DB::table('myads_voucher')->where('id', $id)->first();

        // Jika voucher tidak ditemukan
        if (!$voucher) {
            return response()->json(['error' => 'Data voucher tidak ditemukan.'], 404);
        }

        // Hapus data dari database
        DB::table('myads_voucher')->where('id', $id)->delete();

        return response()->json(['success' => 'Voucher berhasil dihapus.']);
    }

    public function getClaimedVouchers(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('myads_voucher as mv')
                ->join('myads_user as mu', 'mv.user_id', '=', 'mu.id')
                ->whereNotNull('mv.user_id') // Hanya tampilkan voucher yang sudah ada user_id-nya
                ->select(
                    'mu.id as user_id', // ID dari tabel user untuk proses update
                    'mv.id as voucher_id', // ID dari tabel voucher untuk proses 'unclaim'
                    'mu.created_at as tanggal_daftar',
                    'mu.nama',
                    'mu.usaha',
                    'mu.email',
                    'mu.nomor_hp',
                    'mv.voucher as kode_voucher'
                );

            return DataTables::of($data)
                ->addColumn('aksi', function ($row) {
                    // Tombol Edit merujuk pada user_id, Tombol Hapus merujuk pada voucher_id
                    $btn = '<a href="javascript:void(0)" data-user-id="' . $row->user_id . '" class="btn btn-primary btn-sm editUser"><i class="fas fa-edit"></i> Edit User</a> ';
                    $btn .= '<a href="javascript:void(0)" data-voucher-id="' . $row->voucher_id . '" class="btn btn-warning btn-sm unclaimVoucher"><i class="fas fa-unlink"></i> Lepas Klaim</a>';
                    return $btn;
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    /**
     * FUNGSI UPDATE: Mengupdate data user yang telah klaim voucher.
     */
    public function updateUser(Request $request, $user_id)
    {
        // Validasi input, pastikan email unik tapi abaikan email user saat ini
        $validator = Validator::make($request->all(), [
            'nama'      => 'required|string|max:191',
            'usaha'     => 'nullable|string|max:191',
            'email'     => 'required|email|max:191|unique:myads_user,email,' . $user_id,
            'nomor_hp'  => 'nullable|string|max:32',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'  => 'Email ini sudah digunakan oleh user lain.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cari user berdasarkan ID
        $user = DB::table('myads_user')->where('id', $user_id)->first();
        if (!$user) {
            return response()->json(['error' => 'Data user tidak ditemukan.'], 404);
        }

        // Update data di tabel myads_user
        DB::table('myads_user')->where('id', $user_id)->update([
            'nama'       => $request->nama,
            'usaha'      => $request->usaha,
            'email'      => $request->email,
            'nomor_hp'   => $request->nomor_hp,
            'updated_at' => Carbon::now(),
        ]);

        return response()->json(['success' => 'Data user berhasil diperbarui.']);
    }

    /**
     * FUNGSI DELETE: Melepaskan klaim voucher dari user (user_id di-set NULL).
     */
    public function unclaimVoucher($voucher_id)
    {
        // Cari voucher berdasarkan ID-nya
        $voucher = DB::table('myads_voucher')->where('id', $voucher_id)->first();

        if (!$voucher) {
            return response()->json(['error' => 'Data voucher tidak ditemukan.'], 404);
        }

        // Set kolom user_id menjadi NULL
        DB::table('myads_voucher')->where('id', $voucher_id)->update([
            'user_id' => null,
            'updated_at' => Carbon::now(),
        ]);

        return response()->json(['success' => 'Klaim voucher berhasil dilepaskan.']);
    }
    public function downloadVouchers()
    {
        // 1. Tentukan nama file
        $fileName = 'claimed_vouchers_' . Carbon::now()->format('Y-m-d') . '.csv';

        // 2. Tentukan header untuk file CSV
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // 3. Kolom yang akan ada di file CSV
        $columns = ['Tanggal Daftar', 'Nama', 'Usaha', 'Email', 'Nomor HP', 'Kode Voucher'];

        // 4. Buat callback untuk streaming data
        $callback = function () use ($columns) {
            // Buka output stream
            $file = fopen('php://output', 'w');

            // Tulis baris header ke file CSV dengan delimiter ~
            fputcsv($file, $columns, '~');

            // Ambil data dari database
            $data = DB::table('myads_voucher as mv')
                ->join('myads_user as mu', 'mv.user_id', '=', 'mu.id')
                ->whereNotNull('mv.user_id')
                ->select(
                    'mu.created_at',
                    'mu.nama',
                    'mu.usaha',
                    'mu.email',
                    'mu.nomor_hp',
                    'mv.voucher'
                )
                ->orderBy('mu.created_at', 'desc')
                ->get();

            // Tulis setiap baris data ke file CSV
            foreach ($data as $row) {
                $rowData = [
                    Carbon::parse($row->created_at)->format('Y-m-d H:i:s'),
                    $row->nama,
                    $row->usaha,
                    $row->email,
                    $row->nomor_hp,
                    $row->voucher,
                ];
                fputcsv($file, $rowData, '~');
            }

            // Tutup output stream
            fclose($file);
        };

        // 5. Kembalikan response sebagai file download
        return response()->stream($callback, 200, $headers);
    }
    
}

