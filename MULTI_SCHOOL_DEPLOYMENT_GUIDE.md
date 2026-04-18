# 🏫 MULTI-SCHOOL DEPLOYMENT GUIDE

## ✅ GLORYLAND SCHOOL IS NOW ACTIVE (DEFAULT)

Your `config/frontend_content.php` now contains **TWO complete school configurations**!

---

## 📁 Current Structure

### ✅ **Gloryland School** (ACTIVE - First Block)
- All content with "Gloryland School" name
- Currently active and being used
- Located at the top of config file

### 📦 **Nalopa School** (BACKUP - Second Block)
- All content with "Nalopa School" name
- Commented out (in `/* */` block)
- Available as backup/alternative
- Located at the bottom of config file

---

## 🔄 How to Switch Between Schools

### Method 1: Comment/Uncomment (Recommended)

#### To Use Gloryland School (Current - Already Active)
```php
// Gloryland School block is ACTIVE (uncommented)
// Nalopa School block is COMMENTED (in /* */)
```

#### To Switch to Nalopa School
1. **Comment out** Gloryland School block:
   ```php
   /*
   // GLORYLAND SCHOOL BLOCK
   'sliders' => [...],
   'features' => [...],
   // ... all Gloryland content
   */
   ```

2. **Uncomment** Nalopa School block:
   ```php
   // Remove /* and */ from Nalopa School block
   'sliders' => [...],
   'features' => [...],
   // ... all Nalopa content
   ```

3. **Clear cache**:
   ```bash
   php artisan config:clear
   ```

---

## 📊 What Changed for Gloryland School

### All "Nalopa School" Replaced With "Gloryland School"

| Location | Old (Nalopa) | New (Gloryland) |
|----------|--------------|-----------------|
| **Hero Sliders** | "At Nalopa School" | "At Gloryland School" |
| **About Section** | "Nalopa School" | "Gloryland School" |
| **Core Values** | "Nalopa School" | "Gloryland School" |
| **Explore Tabs** | "Nalopa School" | "Gloryland School" |
| **Home CTA** | "Nalopa School difference" | "Gloryland School difference" |
| **About Page** | "About Nalopa School" | "About Gloryland School" |
| **About Gallery** | "Nalopa School" | "Gloryland School" |
| **Contact Info** | "Nalopa School Campus" | "Gloryland School Campus" |
| **Contact Email** | "info@aceastafricaregion.org" | "info@glorylandschool.org" |
| **Contact Address** | "Nalopa School, Dar es Salaam" | "Gloryland School, Dar es Salaam" |
| **News Page** | "Nalopa School" | "Gloryland School" |
| **News Featured** | "Nalopa School Achieves..." | "Gloryland School Achieves..." |

**Total**: 20+ references updated!

---

## 🎯 Quick Switch Guide

### Step-by-Step: Switch to Nalopa School

1. **Open** `config/frontend_content.php`

2. **Find** the comment block starting with:
   ```php
   // ==========================================
   // NALOPA SCHOOL - BACKUP (SECOND OPTION)
   // ==========================================
   ```

3. **Comment out** Gloryland block (lines 3-473):
   ```php
   /*
   // GLORYLAND SCHOOL BLOCK
   return [
       'sliders' => [...],
       // ... all content
   ];
   */
   ```

4. **Uncomment** Nalopa block (remove `/*` and `*/`):
   ```php
   // Remove /* from start
   // Remove */ from end
   ```

5. **Clear cache**:
   ```bash
   php artisan config:clear
   php artisan view:clear
   ```

6. **Done!** Nalopa School is now active

---

## 📝 File Structure

```
config/frontend_content.php
├── GLORYLAND SCHOOL (ACTIVE - Lines 3-473)
│   ├── Home Page Content
│   ├── About Page Content
│   ├── Contact Page Content
│   ├── News Page Content
│   └── Events Page Content
│
└── NALOPA SCHOOL (BACKUP - Lines 475+)
    ├── Home Page Content (Commented)
    ├── About Page Content (Commented)
    ├── Contact Page Content (Commented)
    ├── News Page Content (Commented)
    └── Events Page Content (Commented)
```

---

## 🔍 Content Comparison

### Same Structure, Different Names

Both schools have:
- ✅ Same sections (31+ sections)
- ✅ Same structure
- ✅ Same config keys
- ✅ Same number of items

**Only difference**: School name throughout!

---

## 💡 Pro Tips

### Tip 1: Use Search & Replace
When switching, use your editor's search:
- Find: `Gloryland School`
- Replace: `Nalopa School` (or vice versa)

### Tip 2: Keep Both Blocks
Always keep both blocks in the file:
- Easy to switch back
- No need to recreate
- Version control friendly

### Tip 3: Clear Cache Always
After switching:
```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### Tip 4: Test After Switch
Always test your website after switching to ensure everything works.

---

## 🎯 Current Status

### Active School
**Gloryland School** ✅

### Backup School
**Nalopa School** (Commented, ready to activate)

---

## 📋 Checklist for Deployment

### Before Deploying Gloryland School
- ✅ All "Nalopa School" → "Gloryland School"
- ✅ Contact email updated
- ✅ Contact address updated
- ✅ All references updated
- ✅ Cache cleared
- ✅ Website tested

### Before Deploying Nalopa School
- ✅ Comment out Gloryland block
- ✅ Uncomment Nalopa block
- ✅ Cache cleared
- ✅ Website tested

---

## 🚀 Deployment Workflow

### For Gloryland School (Current)
```bash
# 1. Config is already set for Gloryland
# 2. Clear cache
php artisan config:clear

# 3. Test website
# Visit: http://localhost:8000
```

### For Nalopa School
```bash
# 1. Edit config/frontend_content.php
#    - Comment Gloryland block
#    - Uncomment Nalopa block

# 2. Clear cache
php artisan config:clear

# 3. Test website
# Visit: http://localhost:8000
```

---

## 📊 Content Statistics

### Gloryland School (Active)
- **Total Sections**: 31+
- **Config Keys**: 120+
- **School Name References**: 20+
- **Status**: ✅ Active

### Nalopa School (Backup)
- **Total Sections**: 31+
- **Config Keys**: 120+
- **School Name References**: 20+
- **Status**: 📦 Commented (Ready)

---

## 🎨 What's Different

### Gloryland School Specific
- School name: "Gloryland School"
- Email: "info@glorylandschool.org"
- Address: "Gloryland School, Dar es Salaam, Tanzania"
- Campus: "Gloryland School Campus"

### Nalopa School Specific
- School name: "Nalopa School"
- Email: "info@aceastafricaregion.org"
- Address: "Nalopa School, Dar es Salaam, Tanzania"
- Campus: "Nalopa School Campus"

**Everything else is identical!**

---

## 🔧 Advanced: Add More Schools

### To Add a Third School

1. **Copy** one of the existing blocks
2. **Replace** school name throughout
3. **Update** contact information
4. **Comment out** all blocks except the one you want active
5. **Clear cache**

### Example Structure for 3 Schools
```php
return [
    // SCHOOL 1 (Active)
    'sliders' => [...],
    
    /*
    // SCHOOL 2 (Backup)
    'sliders' => [...],
    
    // SCHOOL 3 (Backup)
    'sliders' => [...],
    */
];
```

---

## ✅ Verification

### Check Active School

1. **Open** `config/frontend_content.php`
2. **Look for** uncommented `'sliders'` array
3. **Check** first description - should say "Gloryland School" (currently)
4. **Verify** contact email matches active school

### Quick Test
```php
// In blade file or tinker
dd(config('frontend_content.sliders.0.description'));
// Should show: "At Gloryland School..." (currently)
```

---

## 📝 Notes

### Important Reminders

1. **Only ONE school** should be uncommented at a time
2. **Always clear cache** after switching
3. **Test thoroughly** after each switch
4. **Keep backups** of both configurations
5. **Version control** your config file

---

## 🎉 Summary

### What You Have Now

✅ **Gloryland School** - Active (default)  
✅ **Nalopa School** - Backup (commented)  
✅ **Easy switching** - Comment/uncomment  
✅ **Same structure** - Both schools identical  
✅ **Complete content** - All pages covered  

### Current Active School
**Gloryland School** 🎓

### To Switch
1. Comment active block
2. Uncomment desired block
3. Clear cache
4. Test!

---

**File**: `config/frontend_content.php`  
**Active School**: Gloryland School  
**Backup School**: Nalopa School  
**Status**: ✅ Ready for Deployment!

