# Frontend Content Management Guide

## 📋 Overview

The frontend content is now **file-based** instead of database-driven. All content is managed through the configuration file:

```
config/frontend_content.php
```

## ✅ Advantages

- ✨ **No Database Required**: Content loads directly from file
- 🚀 **Faster Performance**: No database queries needed
- 📝 **Easy to Edit**: Simple PHP array structure
- 🔄 **Version Control**: Track changes with Git
- 🎯 **Clear Structure**: All content in one place
- 💾 **No Admin Panel Needed**: Direct file editing

## 📂 File Structure

```php
config/frontend_content.php
├── sliders          // Hero section slides
├── statement        // Vision & Mission
├── core_values      // Philosophy, Motto, Teachers
├── explore          // Campus Life, Academics, etc.
└── about            // About section items
```

## 🎨 How to Update Content

### 1. Update Hero Sliders

Edit `config/frontend_content.php`:

```php
'sliders' => [
    [
        'title' => 'Your New Title Here',
        'description' => 'Your description...',
        'image' => 'frontend/img/sliders/01.webp',
    ],
    // Add more slides...
],
```

### 2. Update Vision & Mission

```php
'statement' => [
    'image' => 'frontend/img/accreditation/accreditation.webp',
    'items' => [
        [
            'title' => 'Our Vision',
            'description' => 'Your vision statement...',
        ],
        [
            'title' => 'Our Mission',
            'description' => 'Your mission statement...',
        ],
    ],
],
```

### 3. Update Core Values

```php
'core_values' => [
    'background_image' => 'frontend/img/banner/cta_bg.webp',
    'values' => [
        [
            'icon' => 'layers', // Options: layers, heart, users
            'title' => 'YOUR TITLE',
            'description' => 'Your description...',
        ],
        // Add more values...
    ],
],
```

### 4. Update Explore Tabs

```php
'explore' => [
    'image' => 'frontend/img/explore/1.webp',
    'tabs' => [
        [
            'id' => 'unique-id',
            'tab' => 'Tab Name',
            'title' => 'Full Title',
            'description' => 'Tab content description...',
        ],
        // Add more tabs...
    ],
],
```

## 🔄 After Making Changes

### Method 1: Clear Config Cache
```bash
php artisan config:clear
```

### Method 2: Cache the Config (Production)
```bash
php artisan config:cache
```

Then refresh your browser!

## 📸 Managing Images

### Image Paths
Images are stored in: `public/frontend/img/`

Structure:
```
public/frontend/img/
├── sliders/         # Hero slider images
├── accreditation/   # Vision/Mission images
├── banner/          # Background images
├── explore/         # Explore section images
├── about-gallery/   # About section images
└── icon/           # Icon images
```

### Adding New Images
1. Upload image to appropriate folder in `public/frontend/img/`
2. Update path in `config/frontend_content.php`
3. Clear config cache

## 🌐 Multi-language Support

To add Bengali or other languages, create:

```php
config/frontend_content_bn.php  // Bengali
config/frontend_content_es.php  // Spanish
```

Then in your view, use:
```php
config('frontend_content_' . app()->getLocale() . '.sliders')
```

## 🎯 Key Benefits Over Database

| Feature | Database | File-Based |
|---------|----------|-----------|
| Speed | Slower (queries) | **Faster** (direct) |
| Editing | Admin panel | **Direct file** |
| Deployment | Database migration | **Git commit** |
| Version Control | ❌ Difficult | ✅ **Easy** |
| Backup | Database export | **Git history** |
| Caching | Query cache needed | **Config cache** |

## 🚨 Important Notes

1. **Always clear cache** after editing config files
2. **Use proper escaping** for quotes in strings
3. **Maintain array structure** to avoid errors
4. **Test changes** locally before deploying
5. **Keep backups** before major changes

## 💡 Tips for Managing Content

### Use Heredoc for Long Text
```php
'description' => <<<'EOD'
This is a very long description
that spans multiple lines
and is easier to read.
EOD,
```

### Use Constants for Repeated Values
```php
const BASE_IMG_PATH = 'frontend/img/';

'image' => self::BASE_IMG_PATH . 'sliders/01.webp',
```

### Comment Your Sections
```php
// HERO SLIDERS - Main page carousel
'sliders' => [
    // Slide 1: Dreams
    [
        'title' => '...',
    ],
],
```

## 🔧 Troubleshooting

### Content not updating?
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Errors after editing?
- Check for **syntax errors** (missing commas, brackets)
- Validate **PHP array structure**
- Use **proper quotes escaping**

### Images not showing?
- Check **file path** is correct
- Verify **file exists** in `public/` folder
- Check **file permissions**

## 📞 Need Help?

If you encounter issues:
1. Check PHP error logs
2. Validate PHP syntax: `php -l config/frontend_content.php`
3. Clear all caches
4. Review this guide

---

**Last Updated**: November 2025  
**Maintained By**: Development Team

