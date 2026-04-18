# Route Error Fix Instructions

## Error Description
```
Route [fees-collect.collect-transactions] not defined.
```

This error occurs in the sidebar when trying to access the "Online Transactions" menu item.

## Root Cause
The route exists in the codebase but is not recognized by Laravel on the production server. This is a **route caching issue**.

## Verification
The route is properly defined:
- **File**: `routes/fees.php` (line 73)
- **Route Name**: `fees-collect.collect-transactions`
- **Controller**: `FeesCollectController@collect_transactions`
- **View**: `resources/views/backend/fees/collect/transactions.blade.php`

## Solution

### Method 1: Via SSH/Command Line (Recommended)
If you have SSH access to the production server, run these commands:

```bash
cd /home/softdvbl/nalopaschool.softecki.com
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:cache
php artisan config:cache
```

### Method 2: Via Web Browser (If no SSH access)
1. Upload the `clear-cache.php` file to your production server root directory
2. Access it via browser: `https://nalopaschool.softecki.com/clear-cache.php`
3. Wait for all commands to complete
4. **IMPORTANT**: Delete the `clear-cache.php` file immediately after use for security

### Method 3: Via cPanel or File Manager
If you have cPanel access:
1. Go to cPanel → Terminal
2. Navigate to your application directory
3. Run the commands from Method 1

## After Clearing Cache
1. Refresh your browser
2. Try accessing the sidebar menu again
3. The "Online Transactions" link should now work

## Prevention
To prevent this issue in the future:
- Always run `php artisan route:cache` after deploying new routes
- Clear caches before caching: `php artisan route:clear && php artisan route:cache`
- Consider adding these commands to your deployment script

## Additional Notes
- The same issue affects these routes:
  - `fees-collect.collect-list` (Transactions)
  - `fees-collect.collect-amendment` (Amendments)
- All three routes are defined and should work after clearing the cache
