# 🚀 Quick Edit Reference Card

## How to Update Your Website Content in 3 Steps

### Step 1: Open the File
```bash
📁 config/frontend_content.php
```

### Step 2: Edit What You Want
Find the section and change the text:

```php
'sliders' => [
    [
        'title' => 'Change This Text',           // ← Edit here
        'description' => 'Change this too...',   // ← And here
    ],
],
```

### Step 3: Apply Changes
```bash
php artisan config:clear
```

**Refresh your browser - Done!** ✅

---

## 📝 What Can You Edit?

### 1️⃣ Hero Slider
```php
'sliders' => [
    [
        'title' => 'Your Title Here',
        'description' => 'Your description...',
        'image' => 'frontend/img/sliders/01.webp',
    ],
],
```

### 2️⃣ Vision & Mission
```php
'statement' => [
    'items' => [
        [
            'title' => 'Our Vision',
            'description' => 'Your vision text...',
        ],
    ],
],
```

### 3️⃣ Core Values
```php
'core_values' => [
    'values' => [
        [
            'title' => 'OUR PHILOSOPHY',
            'description' => 'Your philosophy text...',
        ],
    ],
],
```

### 4️⃣ Explore Tabs
```php
'explore' => [
    'tabs' => [
        [
            'tab' => 'Campus Life',
            'title' => 'Vibrant Campus Life',
            'description' => 'Your description...',
        ],
    ],
],
```

### 5️⃣ About Section
```php
'about' => [
    [
        'title' => 'Discover Our Campus',
        'description' => 'Your description...',
    ],
],
```

### 6️⃣ Contact Information
```php
'contact' => [
    'phone' => '+255764 652 388',              // ← Change phone
    'email' => 'info@aceastafricaregion.org', // ← Change email
],
```

---

## 🖼️ How to Change Images

### Step 1: Upload New Image
Put your image in:
```
public/frontend/img/sliders/my-new-image.jpg
```

### Step 2: Update Config
```php
'sliders' => [
    [
        'image' => 'frontend/img/sliders/my-new-image.jpg', // ← Change this
    ],
],
```

### Step 3: Clear Cache
```bash
php artisan config:clear
```

---

## ⚡ Common Tasks

### Change School Phone Number
**File:** `config/frontend_content.php`
```php
'contact' => [
    'phone' => '+255 XXX XXX XXX',  // ← Change this
],
```
**Command:** `php artisan config:clear`

---

### Change Hero Slider Text
**File:** `config/frontend_content.php`
```php
'sliders' => [
    [
        'title' => 'New Title',           // ← Change this
        'description' => 'New text...',   // ← Change this
    ],
],
```
**Command:** `php artisan config:clear`

---

### Update Vision Statement
**File:** `config/frontend_content.php`
```php
'statement' => [
    'items' => [
        [
            'title' => 'Our Vision',
            'description' => 'Your new vision...',  // ← Change this
        ],
    ],
],
```
**Command:** `php artisan config:clear`

---

## 🛠️ Essential Commands

| Task | Command |
|------|---------|
| Clear config cache | `php artisan config:clear` |
| Cache for production | `php artisan config:cache` |
| Clear all caches | `php artisan optimize:clear` |
| Check syntax | `php -l config/frontend_content.php` |

---

## ⚠️ Important Notes

1. **Always clear cache** after editing
2. **Test locally** before deploying
3. **Keep backups** of your config file
4. **Use proper quotes** in PHP strings
5. **Don't forget commas** between array items

---

## 🆘 Troubleshooting

### Changes not showing?
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```
Then **hard refresh** browser (Ctrl + Shift + R)

### Syntax error?
Check for:
- Missing commas `,`
- Missing brackets `]` or `}`
- Unclosed quotes `'` or `"`

**Validate syntax:**
```bash
php -l config/frontend_content.php
```

### White screen?
You have a PHP syntax error!
```bash
tail -f storage/logs/laravel.log
```

---

## 💡 Pro Tips

✅ **DO:**
- Edit config file
- Clear cache after changes
- Test before deploying
- Keep backups

❌ **DON'T:**
- Edit live without testing
- Forget to clear cache
- Delete array structure
- Skip commas

---

## 📞 Need Help?

1. Check the full guide: `FRONTEND_CONTENT_GUIDE.md`
2. Check summary: `FRONTEND_FILE_BASED_CONVERSION_SUMMARY.md`
3. Review config: `config/frontend_content.php`

---

**Remember:** One file, one command, infinite possibilities! 🚀

```
Edit → Clear Cache → Refresh → Done! ✅
```

