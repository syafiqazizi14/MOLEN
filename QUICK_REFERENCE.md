# QUICK REFERENCE GUIDE - Admin/Tim 1 Multi-Team Management

Quick reference untuk developers dan QA untuk memahami fitur dengan cepat.

---

## 🎯 Feature Overview

**Goal:** Allow Admin and Team 1 members to manage (create, edit, delete) surveys, mitra placements, and rates for ANY team, while regular team leaders can only manage their own team.

**Three Modules:**
- 📋 **Survei** - Survey management via "Penempatan Mitra" → "Kelola Survei"
- 👥 **Mitra** - Mitra assignment via "Penempatan Mitra" → "Tambah Mitra"
- 💰 **Honor** - Rate management via "Rekap" → "Atur Standar Honor"

---

## 🔑 Key Constants

```
Authorization Rule:
  canManageAllTeams = is_admin OR team_id == 1
  
  IF canManageAllTeams:
    - Can select any team in dropdown
    - Can manage surveys/mitra/rates for all teams
    - Dropdown ENABLED in UI
    
  IF NOT canManageAllTeams:
    - Can only see own team
    - Can only manage own team data
    - Dropdown DISABLED in UI
    
User Types:
  1. Admin (is_admin=1) → canManageAllTeams=true
  2. Team 1 Leader (team_id=1) → canManageAllTeams=true
  3. Other Team Leader (team_id>1) → canManageAllTeams=false
```

---

## 📁 Core Files

### 1. Backend - Controllers

#### TeamSurveyController.php
**Location:** `app/Http/Controllers/TeamSurveyController.php`

Key Method: `store($request)`
```php
public function store(Request $request)
{
    // Validate team exists
    $team = Team::findOrFail($request->target_team_id);
    
    // Check: is_admin || team_id==1 to manage all teams
    if (!Auth::user()->is_admin && Auth::user()->team_id != 1) {
        // Restrict to own team
        if (Auth::user()->team_id != $request->target_team_id) {
            return back()->with('error', 'Tidak berhak');
        }
    }
    
    // Add survey to team's available_surveys JSON column
    // ...
}
```

#### PlacementController.php
**Location:** `app/Http/Controllers/PlacementController.php`

Key Modifications:
- `index()`: Shows all teams for admin/team1, own team for others
- `store()`: Same authorization check + team_id validation

```php
public function index($model, $id)
{
    $canManageAllTeams = Auth::user()->is_admin || Auth::user()->team_id == 1;
    
    if ($canManageAllTeams) {
        $teams = Team::all();  // Show all teams
    } else {
        $teams = [Auth::user()->team];  // Show only own team
    }
    
    // ...
}
```

#### RateController.php
**Location:** `app/Http/Controllers/RateController.php`

Key Modifications:
- `index()`: ALL method - shows all teams for admin/team1
- `store()`: Validates team_id + authorization check
- `update()` & `destroy()`: Allow admin/team1 to edit/delete any team's rates

---

### 2. Frontend - Views

#### penemplatanMitra.blade.php
**Location:** `resources/views/mitrabps/penempatan/penemplatanMitra.blade.php`

Key Section: Team Dropdown in Modal
```blade
@can('manage-all-teams')
    {{-- Show dropdown for Admin/Team1 --}}
    <input type="hidden" id="isUserAdmin" value="1">
@endcan

<!-- Modal for Survei -->
<select id="surveiTeamSelect" name="team_id" 
    {{ !Auth::user()->is_admin && Auth::user()->team_id != 1 ? 'disabled' : '' }}>
    @foreach($teams as $team)
        <option value="{{ $team->id }}">{{ $team->name }}</option>
    @endforeach
</select>

<script>
// Called when team changes via dropdown
function loadTeamSurveys() {
    let selectedTeam = document.getElementById('surveiTeamSelect').value;
    fetch(`/penempatan-mitra/survey-list?team_id=${selectedTeam}`)
        .then(...)
        .then(surveys => {
            updateSurveyDropdown(surveys);
        });
}
</script>
```

#### rates/index.blade.php
**Location:** `resources/views/mitrabps/rates/index.blade.php`

Key Section: Team Dropdown for Rates
```blade
@php
    $canSelectAnyTeam = Auth::user()->is_admin || Auth::user()->team_id == 1;
    $isRegularLeader = Auth::user()->team_id && !Auth::user()->is_admin && Auth::user()->team_id != 1;
@endphp

<!-- Team dropdown in modal -->
<select name="team_id" {{ $isRegularLeader ? 'disabled' : '' }}>
    @if($canSelectAnyTeam)
        @foreach($allTeams as $team)
            <option value="{{ $team->id }}">{{ $team->name }}</option>
        @endforeach
    @else
        <option value="{{ Auth::user()->team_id }}">{{ Auth::user()->team->name }}</option>
    @endif
</select>

<!-- Hidden input for disabled dropdown -->
@if($isRegularLeader)
    <input type="hidden" name="team_id" value="{{ Auth::user()->team_id }}">
@endif
```

---

## 🔄 Data Flow Examples

### Example 1: Admin Adds Survey to Team 2

```
1. Admin clicks "Penempatan Mitra" → "Kelola Survei"
   ↓
2. Modal opens, team dropdown VISIBLE (admin=true)
   ↓
3. Admin selects "Tim 2" from dropdown
   ↓
4. Frontend: loadTeamSurveys() triggered
   - Fetch: GET /penempatan-mitra/survey-list?team_id=2
   - Backend: returns Tim 2's surveys only
   - Frontend: Updates survey dropdown with Tim 2 surveys
   ↓
5. Admin fills form:
   - Div: Team = Tim 2 [hidden in form as select value]
   - Nama: "Susenas 2024"
   - KRO: "SUSENAS2024"
   ↓
6. Admin clicks Submit
   - POST /team/survey/store
   - Data: {
       target_team_id: 2,
       name: "Susenas 2024",
       kro: "SUSENAS2024"
     }
   ↓
7. Backend: TeamSurveyController.store()
   - Check: is_admin? YES → allowed
   - Find Team with id=2 → Found
   - Add survey to Team 2's available_surveys JSON
   - Save to DB
   ↓
8. Response: Redirect with success message
   ↓
9. Frontend: Modal closes, survey list refreshes
   - Shows "Susenas 2024" in Tim 2 surveys only
   - Tim 1/Tim 3 do NOT see this survey
```

### Example 2: Team 2 Leader Views Rates

```
1. Team 2 Leader (team_id=2, is_admin=false) logs in
   ↓
2. Clicks "Rekap" → "Atur Standar Honor"
   ↓
3. Page loads: RateController.index()
   - Check: is_admin? NO; team_id==1? NO
   - canManageAllTeams = false
   - Query: Rate.where('team_id', 2).all()
   - Only show Team 2 rates
   ↓
4. Team 2 Leader clicks "Tambah Harga"
   ↓
5. Modal opens
   - Team dropdown: DISABLED (is_admin=false && team_id!=1)
   - Hidden input: value="2"
   - Survey dropdown: Shows only Team 2 surveys
   ↓
6. Team 2 Leader can edit/add rates only for Team 2
   - Cannot select Team 1 or Team 3
   - Even if they modify HTML, hidden input forces team_id=2
   ↓
7. Form submission:
   POST /mitra/rates/store
   - team_id from form = 2
   - Backend validates: user->team_id == 2 ✓
   - Rate saved to DB with team_id=2
```

---

## 🛡️ Security Checks

### Frontend Level
✅ Disabled dropdown prevents user interaction
✅ Visual indication of allowed teams

### Backend Level (CRITICAL)
✅ Validate `team_id` exists in teams table
✅ Check authorization: `is_admin || team_id==1` for cross-team access
✅ Check authorization: regular leaders only own team
✅ All error cases return 403/error response

### Database Level
✅ Foreign key constraints: team_id → teams.id
✅ Data fetched with `where('team_id', $id)` filters

---

## 🧪 Quick Debug Commands

### Check Authorization in Database
```sql
-- List users by type
SELECT id, name, is_admin, team_id, 
       CASE 
         WHEN is_admin=1 THEN 'Admin'
         WHEN team_id=1 THEN 'Team 1 Leader'
         ELSE 'Team Leader'
       END as user_type
FROM users;

-- Check which team data belongs to
SELECT * FROM rates WHERE team_id = 2;
SELECT * FROM placements WHERE team_id = 2;
SELECT * FROM teams WHERE id = 2;
```

### Check Frontend Variables
```javascript
// In browser console
console.log(isUserAdmin);           // Should be true for admin
console.log(teamSurveys);          // Array of surveys for selected team
console.log(canManageAllTeams);    // true for admin/team1, false for others
```

### Test Authorization Error
```bash
# Try to POST rate for team 3 as team 2 user
curl -X POST http://app.test/mitra/rates/store \
  -H "Authorization: Bearer token" \
  -d "team_id=3&..." 

# Expected: 403 error or redirect with error message
```

---

## 📋 Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| Admin can't see team dropdown | Blade condition wrong | Check $canSelectAnyTeam variable |
| Survey list not updating | loadTeamSurveys() not called | Check onchange handler on team dropdown |
| Cross-team data visible | Query missing team_id filter | Add whereteam_id in controller query |
| Leader can override team | Hidden input not in form | Add hidden input after disabled dropdown |
| Authorization error on submit | Wrong team_id validation | Check if (!$user->is_admin && $user->team_id != 1) logic |

---

## 🚀 Deployment Checklist

Before deploying to production:

- [ ] All 20 tests in TESTING_CHECKLIST.md passed
- [ ] No console errors in browser DevTools
- [ ] Database migrations run (if any)
- [ ] `IMPLEMENTASI_RINGKASAN.md` reviewed
- [ ] `IMPLEMENTASI_FITUR_HONOR.md` reviewed
- [ ] All 3 controllers updated (TeamSurvey, Placement, Rate)
- [ ] All 2 views updated (penemplatanMitra.blade.php, rates/index.blade.php)
- [ ] Business approval obtained
- [ ] Backup database before deploy
- [ ] Monitor error logs post-deployment

---

## 📞 Support Contact

If issues found:
1. Check TESTING_CHECKLIST.md for expected behavior
2. Review IMPLEMENTASI_RINGKASAN.md for feature overview
3. Check IMPLEMENTASI_FITUR_HONOR.md for implementation details
4. Review code comments in modified controllers
5. Check database structure for team_id relationships

---

## 📚 Related Documentation

- `IMPLEMENTASI_RINGKASAN.md` - Complete feature overview
- `IMPLEMENTASI_CHECKLIST.md` - Survey feature details
- `IMPLEMENTASI_FITUR_MITRA.md` - Mitra feature details
- `IMPLEMENTASI_FITUR_HONOR.md` - Honor/Rate feature details
- `TESTING_CHECKLIST.md` - All test cases and procedures

---

Version: 1.0  
Created: 2026-04-13  
Last Updated: 2026-04-13
