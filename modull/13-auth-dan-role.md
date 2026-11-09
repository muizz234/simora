# 13 — Auth, Role & Kepemilikan Data

## SSO (Single Sign-On berbasis session)
- Login: `POST /login` (email + password, throttle 5/menit) → session cookie `web` middleware.
- `GET /login` diarahkan ke halaman login bila sudah login.
- Logout: `POST /logout`.
- Presensi QR khusus: URL bertanda tangan (`URL::signedRoute`) tanpa session — peserta cukup scan & isi nama.
- API: `GET /api/v1/me` via kolom `users.api_token` + middleware `api.token` (mint + header `Authorization: Bearer`).

## Role user
| Role | Hak |
|---|---|
| `developer` | Melihat semua organisasi; pilih `?organization=slug`; hanya GET (tulis diblokir `role:organization`) |
| `organization` | Hanya menulis data `organization_id` miliknya; kolom lain dibatasi 403 |

## OrganizationScope
`app/Support/OrganizationScope.php`:
- `forUser(Query $q, $user, $column)` — membatasi query listing ke organisasi user.
- `assertCanAccessOrganization($user, $organizationId)` — abort 403 jika tidak memiliki akses (dipakai semua `update/destroy` dan aksi seperti keputusan/realisasi).
- Validasi relasi selalu memastikan objek target (proker, kegiatan, budget, RAB proker) milik organisasi peminta.

## Pola halaman (konsisten semua modul)
1. `organizationContext()` → driver/developer sama seperti E-Progress.
2. Query di-scope ke organisasi (atau semua bila developer tanpa pilihan).
3. Form tulis hanya role organization terlihat (developer melihat pesan view-only).
4. Route tulis terdaftar dengan `->middleware('role:organization')`.

## Data audit
- `created_by` / `uploaded_by` / `submitted_by` / `decided_by` menunjuk `users` (FK nullOnDelete).
- `domain_events` (outbox): Observer `Organization`/`User` menulis event agar sinkronisasi antar modul tercatat.
- `api_token` rotasi manual; tidak diekspos di view.

## Keamanan lain
- CSRF aktif (middleware web); kecuali route presensi publik (signed, tanpa state perubahan selain check-in nama).
- Upload dibatasi tipe & ukuran (mimes + max); storage disk `public`; nama file dari hash storage.
- Pesan error field lokal `@error`; pesan sukses via `session('status')`.
