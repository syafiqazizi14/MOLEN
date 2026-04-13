# TESTING CHECKLIST - Admin/Tim 1 Kelola Semua Tim

Checklist lengkap untuk testing semua 3 fitur yang telah diimplementasikan.

---

## 🔐 AUTHORIZATION TESTS

### Admin User Tests
- [ ] Login as Admin user
- [ ] Open "Penempatan Mitra" → "Kelola Survei"
  - [ ] Team dropdown VISIBLE
  - [ ] Can select any team
  - [ ] Survey list updates when team changes
- [ ] Open "Penempatan Mitra" → "Tambah Mitra"
  - [ ] Team dropdown VISIBLE
  - [ ] Can select any team
  - [ ] Survey list updates when team changes
- [ ] Open "Rekap" → "Atur Standar Honor" → "Tambah Harga"
  - [ ] Team dropdown VISIBLE
  - [ ] Can select any team
  - [ ] Survey list updates when team changes

### Team 1 Member Tests (Non-Admin)
- [ ] Login as Team 1 member (not admin)
- [ ] Open "Penempatan Mitra" → "Kelola Survei"
  - [ ] Team dropdown VISIBLE and enabled
  - [ ] Can select any team (since team_id=1)
  - [ ] Survey list updates when team changes
- [ ] Open "Penempatan Mitra" → "Tambah Mitra"
  - [ ] Team dropdown VISIBLE and enabled
  - [ ] Can select any team
  - [ ] Survey list updates when team changes
- [ ] Open "Rekap" → "Atur Standar Honor" → "Tambah Harga"
  - [ ] Team dropdown VISIBLE and enabled
  - [ ] Can select any team
  - [ ] Survey list updates when team changes

### Regular Team Leader Tests (Team 2-N)
- [ ] Login as Team Leader (e.g., team_id = 2)
- [ ] Open "Penempatan Mitra" → "Kelola Survei"
  - [ ] Team dropdown DISABLED (read-only)
  - [ ] Shows ONLY their team
  - [ ] Cannot select other teams
- [ ] Open "Penempatan Mitra" → "Tambah Mitra"
  - [ ] Team dropdown DISABLED (read-only)
  - [ ] Shows ONLY their team
  - [ ] Cannot select other teams
- [ ] Open "Rekap" → "Atur Standar Honor" → "Tambah Harga"
  - [ ] Team dropdown DISABLED (read-only)
  - [ ] Shows ONLY their team
  - [ ] Cannot select other teams

---

## 📋 SURVEI (KELOLA SURVEI) FEATURE TESTS

### Test 1: Admin Adds Survey to Another Team
**Precondition:** Admin user logged in, Team 2 exists, list is clean
```
Steps:
  1. Open Penempatan Mitra → Kelola Survei
  2. Click "Tambah Data Survei"
  3. Select Team: "Tim 2"
  4. Fill form:
     - Nama: "Survei Kepuasan Layanan"
     - KRO: "SURVEI-KPS-2024"
  5. Click Submit
  
Expected Result:
  ✓ Modal closes
  ✓ Success message appears: "Survei berhasil ditambahkan"
  ✓ New survei appears in Team 2 survey list
  ✓ Team 1 survey list should NOT contain this survei
  ✓ Database: Team 2's available_surveys updated
```

### Test 2: Survey List Refreshes on Team Change
**Precondition:** Multiple teams have different surveys, admin logged in
```
Steps:
  1. Open Penempatan Mitra → Kelola Survei
  2. Click "Tambah Data Survei"
  3. Select Team: "Tim 1" → observe survey list
  4. Change to Team: "Tim 2" → observe survey list changes
  5. Change to Team: "Tim 3" → observe survey list changes
  
Expected Result:
  ✓ Survey list updates immediately without page reload
  ✓ Each team shows its own surveys
  ✓ No surveys from other teams visible
```

### Test 3: Regular Leader Can Only See Own Team Surveys
**Precondition:** Team 2 leader logged in
```
Steps:
  1. Open Penempatan Mitra → Kelola Survei
  2. Click "Tambah Data Survei"
  3. Observe team dropdown
  
Expected Result:
  ✓ Team dropdown is disabled/read-only
  ✓ Shows only "Tim 2"
  ✓ Cannot change team selection
  ✓ Survey list shows only Tim 2 surveys
```

### Test 4: Add Same Survey to Different Teams
**Precondition:** Admin logged in
```
Steps:
  1. Add "Survei A" to Tim 1
  2. Add "Survei A" to Tim 2
  3. Add "Survei A" to Tim 3
  4. Login as Team 1 leader, check surveys
  5. Login as Team 2 leader, check surveys
  
Expected Result:
  ✓ Each team has "Survei A" in their list
  ✓ Each team's dropdown shows their own "Survei A"
  ✓ No data leakage between teams
```

---

## 👥 MITRA (PENEMPATAN) FEATURE TESTS

### Test 5: Admin Adds Mitra to Another Team
**Precondition:** Admin logged in, surveyor exists, Team 2 exists
```
Steps:
  1. Open Penempatan Mitra → Tambah Mitra
  2. Select Team: "Tim 2"
  3. Select Month: "April 2024"
  4. Select Mitra/Surveyor: "John Doe"
  5. Select Survey: (pick from Tim 2 surveys)
  6. Click Submit
  
Expected Result:
  ✓ Modal closes
  ✓ Success message appears
  ✓ Mitra appears in Tim 2 placement list
  ✓ Mitra does NOT appear in other teams' lists
  ✓ Database: Placement record created with team_id=2
```

### Test 6: Mitra List Updates on Team Change
**Precondition:** Multiple teams have different mitra placements
```
Steps:
  1. Open Penempatan Mitra → Tambah Mitra
  2. Select Team: "Tim 1" → observe mitra list for April
  3. Change to Team: "Tim 2" → observe mitra list changes
  4. Change to Team: "Tim 3" → observe mitra list changes
  
Expected Result:
  ✓ Mitra list updates immediately
  ✓ Each team shows only its mitra
  ✓ No cross-team data visible
```

### Test 7: Survey Dropdown Updates on Team Change
**Precondition:** Each team has different surveys
```
Steps:
  1. Open Penempatan Mitra → Tambah Mitra
  2. Select Team: "Tim 1"
  3. Observe survey options in dropdown
  4. Change to Team: "Tim 2"
  5. Observe survey dropdown updated
  
Expected Result:
  ✓ Survey dropdown shows only selected team's surveys
  ✓ Updates when team changes
  ✓ No surveys from other teams appear
```

### Test 8: Regular Leader Cannot Override Team
**Precondition:** Team 2 leader logged in
```
Steps:
  1. Open Penempatan Mitra → Tambah Mitra
  2. Try to select different team (if possible)
  3. Submit data for their team
  
Expected Result:
  ✓ Team dropdown disabled
  ✓ Can only add mitra to own team
  ✓ Submitted data assigned to own team only
```

---

## 💰 HONOR/RATE (REKAP HONOR) FEATURE TESTS

### Test 9: Admin Sets Rate for Another Team
**Precondition:** Admin logged in, Team 2 exists
```
Steps:
  1. Open Rekap → Atur Standar Honor → Tambah Harga
  2. Select Team: "Tim 2"
  3. Select Survey: (from Tim 2 surveys)
  4. Fill form:
     - Entry A: 100,000
     - Entry B: 80,000
     - Field Coordinator: 150,000
     - etc.
  5. Click Submit
  
Expected Result:
  ✓ Modal closes
  ✓ Success message appears
  ✓ Rates appear in Tim 2 honor list
  ✓ Rates do NOT appear in other teams' lists
  ✓ Database: Rate records created with team_id=2
```

### Test 10: Rate List Updates on Team Change
**Precondition:** Multiple teams have different rates set
```
Steps:
  1. Open Rekap → Atur Standar Honor
  2. Tab Tim 1 → observe rates
  3. Click on Team: "Tim 2" (if clicking)
  4. Observe rates list changes
  5. Click on Team: "Tim 3" (if clicking)
  
Expected Result:
  ✓ Rate list updates on team selection change
  ✓ Each team shows only its rates
  ✓ No cross-team rate data visible
```

### Test 11: Survey Dropdown Updates on Team Change
**Precondition:** Each team has different surveys with rates
```
Steps:
  1. Open Rekap → Atur Standar Honor → Tambah Harga
  2. Select Team: "Tim 1"
  3. Observe survey dropdown
  4. Change to Team: "Tim 2"
  5. Observe survey dropdown updated
  
Expected Result:
  ✓ Survey dropdown shows only Tim 2's surveys
  ✓ Updates immediately when team changes
  ✓ No surveys from other teams appear
```

### Test 12: Edit Rate from Another Team
**Precondition:** Admin logged in, rates exist for multiple teams
```
Steps:
  1. Open Rekap → Atur Standar Honor
  2. Find rate from Tim 2
  3. Click Edit
  4. Modify values
  5. Click Submit
  
Expected Result:
  ✓ Rate updated for Tim 2 only
  ✓ Tim 1 rates not affected
  ✓ Database updated with correct team_id
```

### Test 13: Delete Rate from Another Team
**Precondition:** Admin logged in, rates exist for multiple teams
```
Steps:
  1. Open Rekap → Atur Standar Honor
  2. Find rate from Tim 2
  3. Click Delete
  4. Confirm deletion
  
Expected Result:
  ✓ Rate deleted from Tim 2
  ✓ Tim 1 rates not affected
  ✓ Database record removed for correct team_id
```

---

## 🔒 SECURITY & EDGE CASE TESTS

### Test 14: Verify Authorization Error Handling
**Precondition:** Any user logged in
```
Steps:
  1. Using browser console/network tab
  2. Try to submit survey/mitra/rate with team_id != user's team
  3. Observe error response
  
Expected Result:
  ✓ Request rejected with error message
  ✓ Error message: "Anda hanya berhak mengatur... tim Anda sendiri"
  ✓ Data NOT saved to database
```

### Test 15: Verify No Cross-Team Data Leakage
**Precondition:** All 3 features tested with multiple teams
```
Steps:
  1. Review database directly for each team
  2. Check Team.available_surveys for team 1
  3. Check Placement table for team_id filter
  4. Check Rate table for team_id filter
  
Expected Result:
  ✓ Team 1 surveys: only surveys added to team 1
  ✓ Team 2 placements: only mitra assigned to team 2
  ✓ Team 3 rates: only rates set for team 3
  ✓ No data mixed between teams
```

### Test 16: Verify Hidden Input Fallback
**Precondition:** Regular team leader logged in
```
Steps:
  1. Open any modal (survei/mitra/rate)
  2. Inspect HTML using browser tools
  3. Look for hidden input with team_id
  
Expected Result:
  ✓ Hidden input contains user's team_id
  ✓ Even if dropdown is disabled, team_id is correctly submitted
  ✓ Prevents team_id tampering via disabled dropdown bypass
```

---

## 🎯 INTEGRATION TESTS

### Test 17: Complete Workflow - Admin Sets Up Team 2
**Precondition:** Fresh database, admin logged in
```
Scenario: Admin wants to set up complete data for Team 2

Steps:
  1. Add Survey "Pengawasan Kualitas" to Team 2
  2. Add Mitra "Sarah Johnson" for April → Pengawasan Kualitas
  3. Set Rate for Team 2 → Pengawasan Kualitas
  4. Login as Team 2 Leader
  5. Verify they can see all 3 items
  6. Verify they can view (but not edit/delete admin's entries)
  
Expected Result:
  ✓ All 3 items appear in Team 2
  ✓ Team 2 leader can access all data
  ✓ Other teams don't see Team 2's data
  ✓ Workflow is seamless
```

### Test 18: Check Report Generation
**Precondition:** Rates set for multiple teams
```
Steps:
  1. Generate report for Team 1
  2. Check that report only contains Team 1 data
  3. Generate report for Team 2
  4. Check that report only contains Team 2 data
  
Expected Result:
  ✓ Reports correctly filtered by team
  ✓ No cross-team data in reports
  ✓ All amounts/rates are correct
```

---

## 📱 UI/UX TESTS

### Test 19: Modal Responsive Design
**Precondition:** Access app on desktop, tablet, mobile
```
Steps:
  1. Open survey/mitra/rate modal on desktop → check layout
  2. Open same modal on tablet → check layout
  3. Open same modal on mobile → check layout
  4. Try form submission on each device
  
Expected Result:
  ✓ All dropdowns work on all devices
  ✓ Form is readable and usable
  ✓ Team selection clear and accessible
  ✓ No layout breaks
```

### Test 20: Dropdown Performance
**Precondition:** Database has many teams and surveys
```
Steps:
  1. Load Penempatan Mitra page with 50+ teams
  2. Open survey/mitra modal
  3. Change team selection 10 times
  4. Measure time for list update
  
Expected Result:
  ✓ Dropdown loads in <1 second
  ✓ Team change updates list in <500ms
  ✓ No UI freeze
  ✓ No console errors
```

---

## 🐛 BUG REPORT TEMPLATE

If issues found during testing:

```
BUG: [Title]
Severity: [Critical/High/Medium/Low]
Reproducible: [Yes/No/Sometimes]

Steps to Reproduce:
1. [Step 1]
2. [Step 2]
3. ...

Expected Result:
[What should happen]

Actual Result:
[What actually happened]

Screenshots:
[Attach if applicable]

Environment:
- Browser: [Chrome/Firefox/Safari/Edge]
- Version: [Version]
- OS: [Windows/Mac/Linux]
```

---

## ✅ SIGN OFF

### QA Testing Completed ✓
- [ ] All 20 test cases passed
- [ ] No critical bugs found
- [ ] Authorization working correctly
- [ ] No data leakage between teams
- [ ] UI/UX acceptable
- [ ] Performance acceptable

**Tested By:** ________________  
**Date:** ________________  
**Status:** READY FOR PRODUCTION / NEEDS FIXES

### Business Approval ✓
- [ ] Feature meets requirements
- [ ] User workflow acceptable
- [ ] Ready for production deployment

**Approved By:** ________________  
**Date:** ________________

---

## 📊 Test Results Summary

| Feature | Tests Passed | Tests Failed | Status |
|---------|-------------|--------------|--------|
| Survei | 0/4 | 0/4 | Pending |
| Mitra | 0/4 | 0/4 | Pending |
| Honor/Rate | 0/5 | 0/5 | Pending |
| Security | 0/3 | 0/3 | Pending |
| Integration | 0/2 | 0/2 | Pending |
| UI/UX | 0/2 | 0/2 | Pending |
| **TOTAL** | **0/20** | **0/20** | **Pending** |

---

Generated: 2026-04-13
For: Admin/Team 1 Multi-Team Management Feature
