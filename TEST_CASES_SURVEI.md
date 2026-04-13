# Test Cases - Fitur Admin/Tim 1 Mengelola Survei Semua Tim

## Test Case 1: Admin User Menambahkan Survei ke Tim Lain

### Prerequisites
- Login sebagai user dengan `is_admin = 1`

### Steps
1. Navigasi ke halaman "Penempatan Mitra" (Alokasi Mitra)
2. Klik tombol "Kelola Survei" (harus visible)
3. Modal "Daftar Survei Tim Saya" terbuka
4. **EXPECTED**: Dropdown "Pilih Tim" ada dan visible
5. Pilih Team A dari dropdown
6. **EXPECTED**: 
   - List survei update menampilkan survei dari Team A
   - KRO dropdown update menampilkan KRO dari Team A
7. Isi form tambah survei:
   - Nama Survei: "Survei Test Admin"
   - KRO: "KRO123"
   - Tanggal Mulai: [pilih date]
   - Tanggal Selesai: [pilih date]
8. Klik "Tambah Survei"
9. **EXPECTED**: 
   - Success notification muncul
   - Survei baru muncul di list
   - Modal masih terbuka
10. Pilih Team B dari dropdown
11. **EXPECTED**: List survei update menampilkan survei dari Team B
12. Ulangi step 7-8 untuk Team B dengan nama "Survei Test Admin 2"
13. **VERIFICATION**:
    - Buka halaman "Daftar Survei"
    - Cek survei "Survei Test Admin" ada di list Team A
    - Cek survei "Survei Test Admin 2" ada di list Team B

---

## Test Case 2: Tim 1 Member Menambahkan Survei ke Tim Lain

### Prerequisites  
- Login sebagai user dengan `team_id = 1` dan BUKAN admin

### Steps
1. Navigasi ke halaman "Penempatan Mitra"
2. Klik tombol "Kelola Survei" (harus visible)
3. Modal terbuka
4. **EXPECTED**: Dropdown "Pilih Tim" ada dan visible
5. Select Team C
6. **EXPECTED**: List survei update
7. Tambah survei ke Team C dengan nama "Survei Team1"
8. **VERIFICATION**: Survei muncul di Team C's survey list

---

## Test Case 3: Regular Tim Member Menambahkan Survei (Kontrol)

### Prerequisites
- Login sebagai user dengan `team_id = X` (bukan 1, bukan admin)

### Steps
1. Navigasi ke halaman "Penempatan Mitra"
2. Klik tombol "Kelola Survei" (harus visible)
3. Modal terbuka
4. **EXPECTED**: TIDAK ada dropdown "Pilih Tim"
5. **EXPECTED**: `target_team_id` hidden input value = user's team_id
6. Tambah survei
7. **EXPECTED**: Survei hanya bisa ditambah ke tim mereka sendiri (Team X)

---

## Test Case 4: Authorization Validation

### Prerequisites
- Persiapkan user dengan team_id = X (bukan 1, bukan admin)

### API Test: GET /api/kro-list
```bash
# Valid - user's own team
curl "http://localhost:8000/api/kro-list?team_id=X"
# Expected: 200 OK, return KRO list

# Invalid - other team
curl "http://localhost:8000/api/kro-list?team_id=Y"  
# Expected: 200 OK, empty array (unauthorized)
```

### API Test: POST /mitra/surveys
```bash
# Valid - user's own team
POST /mitra/surveys
{
  "survey_name": "Test",
  "kro": "KRO1",
  "target_team_id": "X",
  "tanggal_mulai": "2024-01-01",
  "tanggal_selesai": "2024-01-31"
}
# Expected: 200 OK, success message

# Invalid - other team (should be rejected)
POST /mitra/surveys
{
  "survey_name": "Test2", 
  "kro": "KRO2",
  "target_team_id": "Y",
  "tanggal_mulai": "2024-01-01",
  "tanggal_selesai": "2024-01-31"
}
# Expected: 302 Redirect, error message "tidak memiliki izin"
```

---

## Test Case 5: Survey List Dinamis Update

### Prerequisites
- Login as admin/tim 1

### Steps
1. Buka modal Kelola Survei
2. Team A selected dengan 3 survei
3. **EXPECTED**: List menampilkan 3 survei Team A
4. Pilih Team B (tidak ada survei)
5. **EXPECTED**: List instantly update menampilkan "Belum ada survei"
6. Kembali ke Team A
7. **EXPECTED**: Kembali menampilkan 3 survei Team A

---

## Bug Detection Checklist

- [ ] Modal dropdown tidak crash saat pergantian tim
- [ ] Form tidak mix-up antara tim saat user switch
- [ ] Survei tersimpan di team yang benar
- [ ] KRO list akurat per team
- [ ] Authorization error handling proper
- [ ] No unauthorized team access via API
- [ ] UI consistent (button visible/hidden rules)

---

## Known Limitations

1. Edit/Delete survei hanya bisa dari admin/team 1 dengan API adjustment
2. Survei display masih hardcoded PHP pada page load (tapi refres via JS saat team change)
3. Tidak ada audit log untuk survei creation

---

## Rollback Instructions

Jika ada issue critical:

```bash
# Revert TeamSurveyController.php changes
git checkout app/Http/Controllers/TeamSurveyController.php

# Revert view changes  
git checkout resources/views/mitrabps/penempatan/penempatanMitra.blade.php
```
