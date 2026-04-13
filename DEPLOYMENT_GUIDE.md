# DEPLOYMENT GUIDE - Admin/Tim 1 Multi-Team Management

Step-by-step guide untuk deploy fitur ke production.

---

## 📋 Pre-Deployment Checklist

### Code Review ✓
- [ ] All changes reviewed by senior developer
- [ ] No hardcoded values or debug code left
- [ ] All variable names consistent
- [ ] Comments added where needed
- [ ] No security vulnerabilities found

### Testing ✓
- [ ] All 20 test cases in TESTING_CHECKLIST.md passed
- [ ] No bugs found during QA
- [ ] Authorization properly tested
- [ ] Performance acceptable
- [ ] Mobile/tablet UI responsive

### Database ✓
- [ ] Database backed up
- [ ] No migration needed (using existing columns/tables)
- [ ] Existing data structure compatible
- [ ] No breaking changes

### Documentation ✓
- [ ] IMPLEMENTASI_RINGKASAN.md reviewed
- [ ] IMPLEMENTASI_FITUR_HONOR.md reviewed
- [ ] QUICK_REFERENCE.md reviewed
- [ ] TESTING_CHECKLIST.md completed
- [ ] Comments in code adequate
- [ ] No outdated documentation

### Approval ✓
- [ ] Business stakeholders approved
- [ ] Tech lead approved
- [ ] Project manager reviewed
- [ ] Security reviewed

---

## 🔄 Deployment Steps

### Step 1: Code Backup (5 min)

```bash
# Create backup tag of current production code
git tag -a backup-pre-admintim1-2024 -m "Backup before admin/tim1 multi-team feature"
git push origin backup-pre-admintim1-2024

# Verify tag created
git tag -l | grep backup
```

**Verify:** Tag should be visible in git log

---

### Step 2: Database Backup (10 min)

```bash
# Full database backup
mysqldump -u root -p laravelapp > backup_$(date +%Y%m%d_%H%M%S).sql

# Verify backup created
ls -lh backup_*.sql

# Test restore in separate instance (optional but recommended)
mysql -u root -p laravelapp_test < backup_*.sql
```

**Verify:** Backup file size > 1MB, can be restored without errors

---

### Step 3: Code Deployment (5 min)

```bash
# Pull latest changes to production
git pull origin main

# Verify correct commit hash
git log -1 --oneline

# Check git status (should be clean)
git status
```

**Verify:** 
- Latest commit matches expected hash
- No uncommitted changes

---

### Step 4: Composer Dependencies (2 min)

```bash
# Install any new dependencies (if any)
composer install --optimize-autoloader --no-dev

# Verify no errors
composer diagnose
```

**Verify:** No "error" in composer diagnose output

---

### Step 5: NPM Assets (3 min)

```bash
# Install JavaScript dependencies (if any)
npm install

# Build assets
npm run build

# Verify build successful
ls -l public/build/
```

**Verify:** public/build/ directory exists and contains files

---

### Step 6: Cache Clear (2 min)

```bash
# Clear application cache
php artisan cache:clear

# Clear view cache
php artisan view:clear

# Clear config cache
php artisan config:clear

# Recreate config cache
php artisan config:cache

# Verify success
php artisan cache:clear && echo "Cache cleared successfully"
```

**Verify:** All commands exit with code 0

---

### Step 7: Service Restart (3 min)

```bash
# For Laravel-specific processes (if using queue workers)
php artisan queue:restart

# Restart web server (if using systemd)
sudo systemctl restart php-fpm   # or
sudo service apache2 restart      # or  
sudo service nginx restart

# Verify services running
sudo systemctl status php-fpm
sudo systemctl status nginx
```

**Verify:** Services show "active (running)" status

---

### Step 8: Smoke Test (10 min)

```bash
# Test application accessibility
curl -I https://app.production.com
# Should show: HTTP/1.1 200 OK

# Test admin login
curl -X POST https://app.production.com/login \
  -d "email=admin@test.com&password=..." 
# Should show: 302 redirect to dashboard

# Test survey functionality
curl https://app.production.com/penempatan-mitra/survei
# Should show 200 response with page

# Check error logs
tail -f storage/logs/laravel.log
# Should show no errors
```

**Verify:**
- [ ] Application accessible (HTTP 200)
- [ ] Login works
- [ ] Survey page loads
- [ ] No errors in log file

---

## ✅ Post-Deployment Validation

### Immediate Checks (15 min)

#### 1. Data Integrity
```sql
-- Verify no data corrupted during deployment
SELECT COUNT(*) FROM teams;
SELECT COUNT(*) FROM rates;
SELECT COUNT(*) FROM placements;
SELECT COUNT(*) FROM surveys;

-- Verify data structure
SHOW COLUMNS FROM rates;
-- Should see: id, team_id, survey_name, month, year, amount, ..., timestamps
```

#### 2. Feature Verification (Manual)

```bash
# Using browser or API client

# Login as Admin
POST /login
{
  "email": "admin@test.com",
  "password": "..."
}

# Test Survey Create
POST /team/survey/store
{
  "target_team_id": 2,
  "name": "Test Survey",
  "kro": "TEST123"
}
# Expected: 302 redirect with success message

# Test Mitra Create
POST /mitra/penempatan
{
  "team_id": 2,
  "surveyor_id": 5,
  "survey_name": "Test Survey",
  "month": 4,
  "year": 2024
}
# Expected: 302 redirect with success message

# Test Rate Create
POST /mitra/rates
{
  "team_id": 2,
  "survey_name": "Test Survey",
  "month": 4,
  "year": 2024,
  "amount": 100000
}
# Expected: 302 redirect with success message
```

#### 3. Authorization Check
```bash
# Login as Team 2 Leader (non-admin, team_id=2)
POST /login
{
  "email": "team2leader@test.com",
  "password": "..."
}

# Try to create rate for Team 3 (should fail)
POST /mitra/rates
{
  "team_id": 3,  # Different team
  "survey_name": "Survey",
  "amount": 100000
}
# Expected: 403 or redirect with error
```

#### 4. Log Check
```bash
# Monitor error logs for 5 minutes
tail -f storage/logs/laravel.log

# Search for errors
grep -i "error\|exception" storage/logs/laravel.log | head -20
# Should show: no errors related to survey/mitra/rate
```

**Verify:**
- [ ] All 3 features work (survey, mitra, rate)
- [ ] Admin can create for any team
- [ ] Leaders limited to own team
- [ ] No errors in logs
- [ ] Response times acceptable (<500ms)

---

## 📊 Performance Baseline

Establish baseline metrics post-deployment:

```bash
# Test response time for list pages
time curl https://app.production.com/penempatan-mitra/survei
# Expected: <500ms

# Test modal load time (API endpoint)
time curl https://app.production.com/penempatan-mitra/survey-list?team_id=2
# Expected: <300ms

# Monitor server resources
vmstat 1 10
free -h
df -h

# Check database query performance
# (Enable slow query log for 1 hour post-deployment)
```

**Baseline Metrics to Record:**
- Survey list page load: _____ ms
- API endpoint response: _____ ms
- CPU usage: _____ %
- Memory usage: _____ %
- Disk space: _____ GB
- Database size: _____ MB

---

## 🚨 Rollback Plan (If Needed)

If critical issues found:

### Quick Rollback (< 5 min)

```bash
# 1. Revert code to previous commit
git revert HEAD --no-edit
git push origin main

# 2. Clear cache
php artisan cache:clear
php artisan view:clear

# 3. Restart services
sudo systemctl restart php-fpm
sudo systemctl restart nginx

# 4. Monitor logs
tail -f storage/logs/laravel.log
```

### Full Rollback (if database affected)

```bash
# 1. Restore from backup
mysql -u root -p laravelapp < backup_YYYYMMDD_HHMMSS.sql

# 2. Revert code
git revert HEAD --no-edit
git push origin main

# 3. Clear all cache
php artisan cache:clear
php artisan view:clear
php artisan config:clear

# 4. Restart services
sudo systemctl restart php-fpm
sudo systemctl restart nginx
sudo systemctl restart mysql

# 5. Verify restoration
curl -I https://app.production.com
```

**Note:** Rollback should take < 15 minutes total

---

## 📞 Support Information

### During Deployment
- **Estimated downtime:** 0 minutes (rolling deployment)
- **Estimated deployment time:** 30 minutes total
- **Rollback time if needed:** < 15 minutes

### Post-Deployment Support
- **Monitoring period:** 1 hour intensive, 24 hours light
- **On-call support:** Available 24/7 for first week
- **Escalation:** Contact tech lead if issues found

### Contact Information
- **Tech Lead:** [Name] - [Email/Phone]
- **DevOps:** [Name] - [Email/Phone]
- **Database Admin:** [Name] - [Email/Phone]
- **Business Lead:** [Name] - [Email/Phone]

---

## 📝 Deployment Log Template

```
=== DEPLOYMENT LOG ===
Date: 2026-04-13
Time: 14:00 UTC
Deployed By: [Your Name]
Approved By: [Tech Lead Name]

PRE-DEPLOYMENT
  ✓ Code backup: backup-pre-admintim1-2026
  ✓ Database backup: backup_20260413_140000.sql
  ✓ All tests passed: 20/20 ✓
  ✓ Code review: Approved
  ✓ Business approval: Approved

DEPLOYMENT
  ✓ Step 1 - Code Backup: Completed 14:01
  ✓ Step 2 - Database Backup: Completed 14:02
  ✓ Step 3 - Code Deployment: Completed 14:05
  ✓ Step 4 - Composer: Completed 14:06
  ✓ Step 5 - NPM Build: Completed 14:08
  ✓ Step 6 - Cache Clear: Completed 14:09
  ✓ Step 7 - Service Restart: Completed 14:10
  ✓ Step 8 - Smoke Test: Completed 14:15

POST-DEPLOYMENT VALIDATION
  ✓ Data integrity checked
  ✓ Feature verification done
  ✓ Authorization checks passed
  ✓ Log monitoring clean
  ✓ Performance baseline established

ISSUES FOUND: None

NEXT STEPS:
  - Monitor logs for 1 hour
  - Send confirmation to stakeholders
  - Archive this log
  - Update deployment documentation

STATUS: ✅ DEPLOYMENT SUCCESSFUL
=== END LOG ===
```

---

## 🎯 Success Criteria

Deployment is considered **SUCCESSFUL** when:

- ✅ All 3 features (survey, mitra, rate) work correctly
- ✅ Authorization properly enforced for all user types
- ✅ No cross-team data leakage
- ✅ No errors in application logs
- ✅ Response times acceptable (< 500ms)
- ✅ All 20 smoke tests pass
- ✅ No database issues
- ✅ No security vulnerabilities found

---

## 📌 Known Limitations & Future Improvements

### Current Limitations
- Team dropdown only available for modal forms (not standalone page)
- KRO list uses AJAX (no server-side pagination for large datasets)
- Survey format supports both old string and new array format (temporary)

### Future Improvements
- [ ] Add pagination for large team dropdown
- [ ] Optimize KRO list loading for 1000+ surveys
- [ ] Standardize survey format across all modules
- [ ] Add bulk operations for rates
- [ ] Add audit logging for admin actions
- [ ] Add team-specific permissions model

---

## ✅ Final Sign-Off

### Deployment Checklist
- [ ] All code changes deployed
- [ ] Database backup confirmed
- [ ] Testing passed
- [ ] Documentation complete
- [ ] Support team notified
- [ ] Monitoring enabled
- [ ] Post-deployment validation complete
- [ ] No issues found

### Approvals
- [ ] Tech Lead: ________________ Date: ________
- [ ] QA Lead: ________________ Date: ________
- [ ] Project Manager: ________________ Date: ________
- [ ] Deployed By: ________________ Date: ________

---

## 📚 Related Documentation

- `IMPLEMENTASI_RINGKASAN.md` - Feature overview
- `TESTING_CHECKLIST.md` - All test cases
- `QUICK_REFERENCE.md` - Developer guide
- Git commit history: Detailed change log

---

Version: 1.0  
Created: 2026-04-13  
Environment: Production  
Status: Ready for Deployment
