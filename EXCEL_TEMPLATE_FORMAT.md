# 📊 STUDENT UPLOAD EXCEL FORMAT

## ✅ Required Excel Format

The upload function expects an Excel file with these **8 mandatory columns** and **1 optional column**:

### Required Columns (In Order):

1. **student_name** - Full name of student (e.g., "John Doe" or "John Doe Smith")
2. **class** - Class name (e.g., "Form 1", "Form 2")
3. **section** - Section name (e.g., "A", "B", "C")
4. **gender** - Must be "Male" or "Female"
5. **category** - Student category (e.g., "Day", "Day A", "Day B", "Day C", "Day D", "Day E", "Day F", "Day G", "Day H", "Day I", "Day J", or "Boarding")
6. **fees_amount** - Total fee amount (numbers only, no commas, e.g., 500000)
7. **current** - Current balance/remaining amount (numbers only, no commas, e.g., 400000)
8. **paid_amount** - Amount already paid (numbers only, no commas, e.g., 100000)

### Optional Columns:

9. **phone_number** - Phone number (e.g., "+255712345678" or "0712345678")

---

## 📋 Excel Format Example

| student_name | class | section | gender | category | phone_number | fees_amount | current | paid_amount |
|--------------|-------|---------|--------|----------|--------------|------------|---------|-------------|
| John Doe | Form 1 | A | Male | Day | +255712345678 | 500000 | 400000 | 100000 |
| Jane Smith | Form 2 | B | Female | Day A | 0712345678 | 600000 | 600000 | 0 |
| Peter Johnson | Form 3 | C | Male | Boarding | +255765432109 | 800000 | 500000 | 300000 |

---

## ✅ What Has Been Created

### 1. **Excel Template Export Class**
**File**: `app/Exports/StudentTemplateExport.php`
- Creates Excel file with only required columns
- Formatted headers (blue background, white text)
- Optimized column widths

### 2. **Download Route**
**Route**: `GET /student/download-template`
**Name**: `student.downloadTemplate`

### 3. **Download Controller Method**
**File**: `app/Http/Controllers/StudentInfo/StudentController.php`
**Method**: `downloadTemplate()`

### 4. **Updated Upload View**
**File**: `resources/views/backend/student-info/student/upload.blade.php`
- Added "Download Students Excel Format" button
- Shows required fields information

---

## 🎯 How to Use

1. Go to **Student Upload** page
2. Click **"Download Students Excel Format"** button
3. Excel file downloads with correct column headers
4. Fill in the data:
   - **student_name**: Full name (required)
   - **class**: Class name (required)
   - **section**: Section letter (required)
   - **gender**: Male or Female (required)
   - **category**: Day/Boarding category (required)
   - **fees_amount**: Total fee amount (required, numbers only)
   - **current**: Current balance/remaining amount (required, numbers only)
   - **paid_amount**: Amount already paid (required, numbers only)
   - **phone_number**: Phone number (optional)
5. Save the Excel file
6. Upload using the form

---

## 📝 Important Notes

- **Column names must match exactly** (case-sensitive)
- **First row must be headers** (student_name, class, section, gender, category, fees_amount, current, paid_amount, phone_number)
- **All 8 required fields** must be filled for each student
- **Optional field** (phone_number) can be left empty
- **Gender must be exactly**: "Male" or "Female"
- **Category examples**: "Day", "Day A", "Boarding", etc.
- **Phone format**: Can be with or without country code (e.g., "+255712345678" or "0712345678")
- **Amount fields**: Numbers only, no commas (e.g., 500000 not 500,000)

---

## ✅ Files Created/Modified

**Created**:
- `app/Exports/StudentTemplateExport.php` - Excel template export

**Modified**:
- `app/Http/Controllers/StudentInfo/StudentController.php` - Added downloadTemplate method
- `routes/student_info.php` - Added download route
- `resources/views/backend/student-info/student/upload.blade.php` - Updated download button

---

**Status**: ✅ Complete and Ready to Use

