# Soft Delete Deployment Checklist

## Pre-Deployment ✅

### Code Review
- [x] `app/models/inventoryModel.php` - Reviewed and updated
- [x] `app/controllers/inventoryController.php` - Reviewed and updated
- [x] `app/views/technicians/inventory.php` - Reviewed and updated
- [x] `public/js/inventorySoftDelete.js` - New file, reviewed
- [x] `public/js/showAlert.js` - Updated, reviewed
- [x] All changes follow MVC architecture
- [x] Code follows existing project conventions
- [x] No breaking changes to existing functionality

### Database Preparation
- [ ] Backup database BEFORE applying migration
  ```bash
  mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
  ```
- [x] Migration file created: `2026_04_07_add_soft_delete_columns.sql`
- [ ] Test migration on staging environment first
- [ ] Verify no conflicts with existing tables/columns

### Documentation
- [x] Technical documentation - `SOFT_DELETE_IMPLEMENTATION.md`
- [x] Setup guide - `SOFT_DELETE_SETUP_GUIDE.md`
- [x] Developer guide - `SOFT_DELETE_DEVELOPER_GUIDE.md`
- [x] Summary document - `SOFT_DELETE_SUMMARY.md`
- [x] This checklist - `SOFT_DELETE_DEPLOYMENT_CHECKLIST.md`

---

## Deployment Steps

### Step 1: Database Migration (⚠️ CRITICAL)
```bash
# CREATE BACKUP FIRST!
mysqldump -u user -p -h localhost database > backup_before_softdelete.sql

# Apply migration
mysql -u user -p -h localhost database < config/migrations/2026_04_07_add_soft_delete_columns.sql

# Verify columns were added
mysql -u user -p -h localhost -e "SHOW COLUMNS FROM inventory WHERE Field LIKE 'deleted%';" database
mysql -u user -p -h localhost -e "SHOW COLUMNS FROM stock_history WHERE Field LIKE 'deleted%';" database
```

### Step 2: Deploy Code Files
```bash
# Copy modified files to production
# Option A: Git pull (if using version control)
git pull origin main

# Option B: Manual copy
cp app/models/inventoryModel.php [production_path]/app/models/
cp app/controllers/inventoryController.php [production_path]/app/controllers/
cp app/views/technicians/inventory.php [production_path]/app/views/technicians/
cp public/js/inventorySoftDelete.js [production_path]/public/js/
cp public/js/showAlert.js [production_path]/public/js/
```

- [ ] All PHP files deployed
- [ ] All JavaScript files deployed
- [ ] File permissions set correctly (644 for files, 755 for dirs)
- [ ] No syntax errors in PHP files
  ```bash
  php -l app/models/inventoryModel.php
  php -l app/controllers/inventoryController.php
  php -l app/views/technicians/inventory.php
  ```

### Step 3: Clear Caches
- [ ] Clear browser cache (or instruct users)
- [ ] Clear web server cache (if applicable)
- [ ] Clear application cache (if exists)
- [ ] Restart PHP-FPM (if applicable)
  ```bash
  sudo systemctl restart php-fpm
  ```

### Step 4: Verify Installation
- [ ] Check database columns exist
  ```sql
  SELECT * FROM information_schema.COLUMNS 
  WHERE TABLE_NAME='inventory' AND COLUMN_NAME LIKE 'deleted%';
  ```
- [ ] Check JavaScript file is accessible
  ```bash
  curl -I http://[domain]/lab_sync/public/js/inventorySoftDelete.js
  ```
- [ ] Check PHP file syntax
  ```bash
  php -l app/controllers/inventoryController.php
  ```

---

## Testing Checklist

### Functional Testing
- [ ] Delete button visible on inventory items
- [ ] Click delete → confirmation dialog appears
- [ ] Cancel confirmation → nothing happens
- [ ] Confirm deletion → row disappears with animation
- [ ] Success notification appears
- [ ] Deleted item no longer in table after refresh
- [ ] Database shows soft delete columns populated

### User Experience Testing
- [ ] Row removal smooth (0.3s fade animation)
- [ ] Notification visible and readable
- [ ] Notification auto-dismisses after ~4 seconds
- [ ] No page reload required
- [ ] Dashboard stats update without reload
- [ ] Error messages are clear

### Integration Testing
- [ ] Edit functionality still works
- [ ] Category listing excludes deleted items
- [ ] Dropdown selections exclude deleted items
- [ ] Dashboard statistics are correct
- [ ] Stock history updated correctly

### Security Testing
- [ ] Logout, try delete → fails with "not authorized"
- [ ] Login with different user → deletion tracked with correct user ID
- [ ] Check database for `deleted_date`, `deleted_time`, `deleted_by` values
- [ ] Verify user_id is recorded correctly

### Performance Testing
- [ ] Soft delete completes in <500ms
- [ ] Page load time unchanged
- [ ] Dashboard stats load normally
- [ ] No database locks or timeouts

### Error Handling Testing
- [ ] Network error during delete → error notification shown
- [ ] Button becomes re-enabled for retry
- [ ] Invalid item ID → appropriate error message
- [ ] Deleted item already deleted → graceful handling

### Browser Compatibility
- [ ] Chrome/Chromium - ✅ Test
- [ ] Firefox - ✅ Test
- [ ] Safari - ✅ Test
- [ ] Edge - ✅ Test

---

## Post-Deployment Verification

### Database Integrity
```sql
-- Check soft delete columns are populated
SELECT COUNT(*) as deleted_items FROM inventory WHERE deleted_date IS NOT NULL;

-- Should be 0 or match expected deletions
SELECT * FROM inventory WHERE deleted_date IS NOT NULL LIMIT 5;

-- Verify indexes exist
SHOW INDEX FROM inventory WHERE Key_name LIKE 'idx_deleted%';
SHOW INDEX FROM stock_history WHERE Key_name LIKE 'idx_deleted%';
```

### Application Checks
- [ ] No PHP errors in logs
- [ ] No JavaScript errors in console
- [ ] All queries return correct results
- [ ] Session management working
- [ ] File uploads/downloads unaffected

### User Acceptance Testing
- [ ] Demo with test user
- [ ] Test with active inventory
- [ ] Get approval from stakeholder
- [ ] Document any issues found

---

## Rollback Trigger Points

### Immediate Rollback Needed If:
- [ ] ❌ Database migration fails
- [ ] ❌ PHP syntax error prevents page load
- [ ] ❌ Delete operation crashes application
- [ ] ❌ Authorization checks fail
- [ ] ❌ Dashboard stats show incorrect numbers
- [ ] ❌ More than 50% of tests fail

### Rollback Procedure:
```bash
# 1. Restore database from backup
mysql -u user -p database < backup_before_softdelete.sql

# 2. Restore code files from backup/version control
git revert [commit_hash]
# OR manually restore files

# 3. Clear caches
# Browser cache/app cache/server cache

# 4. Restart services
sudo systemctl restart php-fpm
sudo systemctl restart apache2  # or nginx
```

---

## Sign-Off Requirements

### Development Team
- [ ] Code reviewed and approved
- [ ] Unit tests pass
- [ ] Documentation complete
- [ ] No known issues

### QA Team  
- [ ] Functional testing passed
- [ ] Performance testing passed
- [ ] Security testing passed
- [ ] Browser compatibility verified

### DevOps Team
- [ ] Deployment procedure verified
- [ ] Backup confirmed
- [ ] Monitoring/alerts configured
- [ ] Rollback plan confirmed

### Product Owner
- [ ] Feature meets requirements
- [ ] User experience acceptable
- [ ] Ready for production

---

## Post-Deployment Monitoring

### First 24 Hours
- [ ] Monitor error logs hourly
- [ ] Check database for any issues
- [ ] Gather user feedback
- [ ] Monitor performance metrics

### First Week
- [ ] Track deletion patterns
- [ ] Monitor error rates
- [ ] Check dashboard accuracy
- [ ] Verify no data integrity issues

### Ongoing
- [ ] Weekly deletion audit
- [ ] Monthly performance review
- [ ] Track deletion trends
- [ ] Update documentation if needed

---

## Communication

### Notify
- [x] Development team - Updates provided
- [ ] QA team - Ready for testing
- [ ] Product owner - Features ready for review
- [ ] End users - Notification of changes (if needed)

### Documentation for Users
- [ ] Delete buttons now soft delete items
- [ ] Items are not permanently removed
- [ ] Deletions are tracked and logged
- [ ] No action required from users

---

## Issues & Resolution

### Issue Template
```
**Issue:** [Description]
**Severity:** [Critical/High/Medium/Low]
**Affected Component:** [Model/Controller/View/JS]
**Steps to Reproduce:** [1, 2, 3]
**Expected vs Actual:** [What should happen vs what happens]
**Resolution:** [How it was fixed]
**Date Resolved:** [YYYY-MM-DD]
```

### Known Issues (None at launch)
- [ ] Issue 1: [Description] - Status: [New/In Progress/Resolved]

---

## Completion Checklist

### Pre-Deployment
- [x] Code reviewed
- [x] Documentation complete
- [x] Database migration ready
- [x] Backup procedure defined

### Deployment
- [ ] Database backup created
- [ ] Migration applied
- [ ] Code files deployed
- [ ] Caches cleared
- [ ] Installation verified

### Testing
- [ ] Functional tests passed
- [ ] Security tests passed
- [ ] Performance verified
- [ ] Browser compatibility checked

### Post-Deployment
- [ ] Monitoring active
- [ ] Team notified
- [ ] Users informed
- [ ] Documentation updated

---

## Sign-Off

**Deployed By:** [Your Name]  
**Date:** [YYYY-MM-DD]  
**Time:** [HH:MM UTC]  
**Environment:** [Development/Staging/Production]  

**Status:** ✅ Ready for Deployment

---

## Contacts & Support

### Technical Support
- **Database Issues:** [Contact DBA]
- **Application Issues:** [Contact Dev Lead]
- **User Issues:** [Contact Product Owner]

### Emergency Contacts
- **On-Call Engineer:** [Phone/Slack]
- **DevOps Lead:** [Phone/Slack]
- **Product Manager:** [Phone/Slack]

---

## Appendix: Quick Commands

```bash
# Backup database
mysqldump -u user -p database > backup.sql

# Apply migration
mysql -u user -p database < migration.sql

# Check PHP syntax
php -l /path/to/file.php

# Clear browser cache (instruction for users)
Chrome: Ctrl+Shift+Del
Firefox: Ctrl+Shift+Del
Safari: Cmd+Shift+Delete

# Check file permissions
ls -la /path/to/file.php

# Restart services
sudo systemctl restart apache2
sudo systemctl restart php-fpm
sudo systemctl restart nginx
```

---

**Checklist Version:** 1.0  
**Last Updated:** April 7, 2026  
**Next Review:** [Date after deployment]
