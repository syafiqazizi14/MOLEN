# IMPLEMENTASI FITUR - Admin/Tim 1 Mengelola Honor (Rate)

## ✅ Perubahan yang Telah Dilakukan

### 1. RateController.php

**✅ index() Method**
```php
$canManageAllTeams = $isAdmin || $user->team_id == 1;
```
- Updated: Team query logic - admin atau team_id 1 lihat semua tim, user biasa lihat selain team 1
- Logika: `if ($isLeader && !$canManageAllTeams)` - cek jika bukan admin/team 1

**✅ store() Method - Authorization Check**
```php
if (!$user->is_admin && $user->team_id != 1) {
    if ($user->team_id != $requestedTeamId) {
        return back()->with('error', 'Anda hanya berhak mengatur harga tim Anda sendiri.');
    }
}
```
- Logic:
  - Admin atau team_id 1: bisa set harga ke tim MANAPUN ✓
  - Regular leader: hanya bisa set ke tim sendiri ✓
  - Unauthorized: error message ✓

**✅ update() & destroy() Methods**
- Updated: Allow check = `if (!$user->is_admin && $user->team_id != 1 && $user->team_id != $rate->team_id)`
- Logic: admin/team1 bisa update/delete rate tim manapun, user biasa hanya punya tim

### 2. rates/index.blade.php

**✅ Team Dropdown Authorization**
```php
$canSelectAnyTeam = Auth::user()->is_admin || Auth::user()->team_id == 1;
$isRegularLeader = Auth::user()->team_id && !Auth::user()->is_admin && Auth::user()->team_id != 1;
```
- Result:
  - Regular leaders: dropdown **DISABLED** (hanya tim sendiri)
  - Admin/Team 1: dropdown **ENABLED** (pilih tim manapun)
  - Hidden input fallback untuk leader

**✅ Survey Dropdown Update**
- Updated `updateSurveyDropdown()` untuk handle format survey baru (dengan kro)

**✅ JavaScript Updates**
```javascript
const canManageAllTeams = @json($canManageAllTeams ?? false);
const isLeader = @json($isLeader && !$canManageAllTeams);
```
- Export `canManageAllTeams` ke JS
- Survey list refresh on team change

---

## 🔄 Flow Implementasi

### Admin/Tim 1 Menambah Harga untuk Tim Lain:
```
1. Klik "Tambah Harga"
   └─> openRateModal()
       └─> Team dropdown ENABLED (tidak ada disabled)

2. Pilih Tim dari dropdown
   └─> onchange trigger: updateSurveyDropdown()
   
3. Pilih Survei & isi harga
   
4. Submit form
   └─> POST /mitra/rates
       {
         team_id: 2,        // Bisa berbeda dari user's team
         survey_name: "...",
         month: 4,
         year: 2026,
         cost: 150000,
         unit: "Dokumen"
       }

5. Controller check auth & create/update Rate
   ├─> Check: is_admin || team_id==1 ? OK : check user->team_id == team_id
   ├─> Rate::updateOrCreate(...)
   └─> Return success

6. Harga masuk ke Rate table dengan team_id=2
```

### Tim Leader Menambah Harga (Tetap):
```
1. Klik "Tambah Harga"
   └─> openRateModal()
       └─> Team dropdown DISABLED (hanya tim mereka)
       └─> Auto-select: tim sendiri

2. Pilih survei & isi harga

3. Submit
   └─> POST /mitra/rates
       {
         team_id: 3,  // Forced ke user's team_id
         ...
       }

4. Harga masuk ke Rate table dengan team_id=3
```

---

## 🛡️ Security Checks

- ✅ Server-side authorization di RateController (all methods)
- ✅ team_id validation di store() request rules
- ✅ Double-check: auth logic sebelum create/update/delete
- ✅ Non-admin users tidak bisa override team_id
- ✅ UI hanya show dropdown untuk authorized users (via disabled attribute)
- ✅ Hidden input fallback untuk leader

---

## 📋 Comparison: Survei vs Mitra vs Honor

| Aspek | Survei | Mitra | Honor |
|-------|--------|-------|-------|
| Where dropdown | Modal survei | Modal mitra | Modal rate |
| Disabled logic | Blade PHP | Blade PHP | Blade PHP |
| Data store | Team.available_surveys | Placement table | Rate table |
| Auth check | TeamSurveyController.store | PlacementController.store | RateController.store |
| Allow team1 | Yes (team_id==1) | Yes (team_id==1) | Yes (team_id==1) |
| Team query | All teams jika admin/team1 | All teams jika admin/team1 | All teams jika admin/team1 |
| List display | By selected team | By selected team | By selected team |

---

## 🚀 Verifikasi Implementasi

- ✅ RateController.php updated (auth logic in index, store, update, destroy)
- ✅ rates/index.blade.php updated (team dropdown, survey dropdown, JS variables)
- ✅ Survey list refresh on team change
- ✅ No syntax errors (except pre-existing Tailwind warning)
- ✅ Authorization logic consistent with mitra & survei
- ✅ Ready for testing

---

## 🧪 Testing Checklist

| Test Case | Expected | Priority |
|-----------|----------|----------|
| Admin buka modal rate - lihat semua team | Dropdown enabled, all teams visible | HIGH |
| Admin pilih team lain - survei update | Survey list refresh | HIGH |
| Admin set harga ke team lain | Rate created with correct team_id | HIGH |
| Admin update rate dari team lain | Rate updated successfully | HIGH |
| Admin delete rate dari team lain | Rate deleted successfully | HIGH |
| Leader buka modal - hanya team sendiri | Dropdown disabled, read-only | HIGH |
| Leader set harga - team_id override | Tetap ke tim leader | MEDIUM |
| Unauthorized user attempt | Error message | MEDIUM |

---

## 📝 Implementation Summary

```
BEFORE:
├─ Admin: lihat semua tim rate ✓
├─ Leader: lihat tim sendiri saja ✓
└─ Add rate: hanya ke tim sendiri (even admin!)

AFTER:
├─ Admin/Team1: lihat semua tim ✓ → dapat pilih tim dropdown ✓
├─ Admin/Team1: set rate ke tim manapun ✓
├─ Admin/Team1: update/delete rate dari tim manapun ✓
├─ Leader: tetap hanya tim sendiri ✓
└─ List: selalu sesuai tim yang dipilih ✓
```

Status: ✅ IMPLEMENTATION COMPLETE - READY FOR TESTING
