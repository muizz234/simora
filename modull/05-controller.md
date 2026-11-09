# 05 — Controller

## Controller publik & panel
| Controller | Fungsi |
|---|---|
| `LandingController` | Landing publik (daftar organisasi dari DB) |
| `LandingStructureController` | Bagan struktural publik (index + show, 4 tingkat dari `group_type`) |
| `LandingKegiatanController` | Daftar & detail kegiatan publik (yang berdokumentasi) |
| `ContactController` | Halaman kontak |
| `PanelController` | Panel utama developer & organisasi (statistik nyata) |
| `Auth\LoginController` | Login/logout SSO session (throttle) |

## ORGMATE
| Controller | Fungsi |
|---|---|
| `OrgmateController` | Dashboard + halaman: struktur, anggota (+pagination), rekrutmen, arsip, pengumuman, panduan |
| `PositionController` | CRUD jabatan + pemegang anggota (1 jabatan = 1 pemegang) |
| `MemberController` | CRUD anggota mahasiswa (NIM unik per org), foto, modal edit |
| `ArchiveController` | CRUD arsip SK/surat + upload file |
| `AnnouncementController` | CRUD pengumuman |

## E-PROGRESS
| Controller | Fungsi |
|---|---|
| `EProgressController` | Dashboard statistik nyata + halaman: kalender, kepanitiaan, presensi, dokumentasi, evaluasi, monitoring, panduan |
| `WorkProgramController` | CRUD proker (nama, deskripsi, status tahapan, tanggal) |
| `ActivityController` | CRUD kegiatan kalender (nama, proker, lokasi, waktu, status) |
| `CommitteeController` | CRUD panitia per kegiatan (nama + peran) |
| `AttendanceController` | Catat manual + hapus presensi; halaman publik check-in QR bertanda tangan |
| `DocumentationController` | Upload multi-berkas dokumentasi (maks 10/files) + edit/hapus |
| `EvaluationController` | CRUD evaluasi/LPJ (judul, isi, tipe, status) |

## SENTRA
| Controller | Fungsi |
|---|---|
| `SentraController` | Dashboard (kas nyata + grafik bulanan + total anggaran) + halaman: kas, rab, iuran, realisasi, laporan, panduan |
| `CashTransactionController` | CRUD transaksi kas (WAJIB bukti: upload/kamera) |
| `BudgetController` | CRUD RAB per proker+tahun (opional taut ke proker E-Progress) |
| `DuesController` | CRUD iuran (WAJIB bukti) + Tandai Lunas → otomatis masuk kas |
| `RealizationController` | Catat realisasi: pengeluaran kas + realisasi RAB naik (WAJIB bukti) |

## Lainnya
| Controller | Fungsi |
|---|---|
| `AccountController` | Kelola akun login (developer) |
| `OrganizationController` | Kelola organisasi (developer) |
| `OrganizationRegistrationController` | Link pendaftaran organisasi |
| `OrganizationSocialController` | Sosial media organisasi |
| `OrganizationVisionMissionController` | Visi & misi |
| `LandingManagementController` | Kelola landing (footer, konten) |
| `Api\V1\OrganizationController` | API publik read-only |

## Pola controller standar
```php
public function store(Request $request): RedirectResponse
{
    $organization = $request->user()->organization;
    abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');
    $data = $this->normalize($request->validate($this->rules()));
    $data['organization_id'] = $organization->id;
    Model::create($data);
    return redirect()->route('...')->with('status', '... berhasil ...');
}

public function update/destroy(... {
    OrganizationScope::assertCanAccessOrganization($request->user(), $model->organization_id);
    ...
}
```
- Query halaman selalu dibatasi `organization_id`; developer boleh memfilter `?organization=slug`.
- Validasi selalu memeriksa relasi milik organisasi sendiri (mis. `work_program_id` harus proker org sendiri → error bila asing).
- File: `storePublicly('...', 'public')`; ganti/hapus file lama saat update/destroy.
- Uang: `proof` wajib (foto/PDF ≤10 MB); iuran lunas & realisasi menyalin bukti ke transaksi kas yang mereka buat.

## Diagram controller flow

```mermaid
flowchart LR
    REQ[Request] --> CTX[organizationContext<br/>developer vs organisasi]
    CTX --> AUTHZ[OrganizationScope<br/>assertCanAccessOrganization]
    AUTHZ -->|gagal| F403[403]
    AUTHZ -->|lolos| VAL[Validasi input + relasi milik org]
    VAL -->|gagal| BACK[Redirect + error field]
    VAL -->|lolos| WRITE[Simpan / ubah / hapus + file bukti]
    WRITE --> FLASH[Flash session status]
    FLASH --> REDIR[Redirect halaman menu]
