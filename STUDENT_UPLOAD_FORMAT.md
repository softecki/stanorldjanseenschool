# 📊 STUDENT UPLOAD EXCEL FORMAT

## ✅ Required Excel Format for Student Upload

The upload function expects an Excel file (`.xlsx`, `.xls`, or `.csv`) with the following columns:

### Required Columns (Must be present):

| Column Name | Description | Example | Notes |
|------------|-------------|---------|-------|
| **student_name** | Full name of student | "John Doe" or "John Doe Smith" | Required. Will be split: first word = first_name, rest = last_name |
| **class** | Class name | "Form 1", "Baby class", "Standard 1" | Required. Will create class if it doesn't exist |
| **section** | Section name | "A", "B", "C" | Required. Will create section if it doesn't exist |
| **gender** | Gender | "Male" or "Female" | Required. Must match exactly |

### Optional Columns (Can be left empty):

| Column Name | Description | Example | Default Value |
|------------|-------------|---------|---------------|
| **category** | Student category | "Day", "Boarding", "Day A", "Day B" | Defaults to "Day" if not provided |
| **phone_number** | Phone number | "+255712345678" or "0712345678" | NULL if not provided |
| **email** | Parent/Guardian email | "parent@example.com" | Auto-generated if not provided (firstname+lastname@gmail.com) |
| **admission_no** | Admission number | "ADM001" | NULL if not provided |
| **admission_date** | Admission date | "2026-01-01" | Defaults to "2026-01-01" if not provided |

---

## 📋 Excel Format Example

### Sample Data:

| student_name | class | section | gender | category | phone_number | email | admission_no | admission_date |
|--------------|-------|---------|--------|----------|--------------|-------|--------------|----------------|
| John Doe | Form 1 | A | Male | Day | +255712345678 | john@example.com | ADM001 | 2026-01-15 |
| Jane Smith | Form 2 | B | Female | Boarding | 0712345678 | | ADM002 | |
| Peter Johnson | Baby class | A | Male | Day A | | peter@example.com | | 2026-02-01 |
| Mary Williams | Standard 1 | C | Female | | +255765432109 | | | |

---

## 📝 Column Details

### 1. **student_name** (Required)
- **Format**: Full name (can be 2 or 3 words)
- **Processing**:
  - First word → `first_name`
  - Second word → `last_name` (also used as parent name)
  - Third word (if exists) → Added to `last_name` and parent name
- **Example**: 
  - "John Doe" → first_name: "John", last_name: "Doe"
  - "John Doe Smith" → first_name: "John", last_name: "Doe Smith"

### 2. **class** (Required)
- **Format**: Class name as string
- **Behavior**: If class doesn't exist, it will be created automatically
- **Example**: "Form 1", "Baby class", "Standard 1", "Nursery"

### 3. **section** (Required)
- **Format**: Section name as string
- **Behavior**: If section doesn't exist, it will be created automatically
- **Example**: "A", "B", "C", "1", "2"

### 4. **gender** (Required)
- **Format**: Must be exactly "Male" or "Female" (case-sensitive)
- **Example**: "Male" or "Female"

### 5. **category** (Optional)
- **Format**: Student category name
- **Default**: "Day" if not provided
- **Behavior**: If category doesn't exist, it will be created automatically
- **Example**: "Day", "Boarding", "Day A", "Day B", "Day C"

### 6. **phone_number** (Optional)
- **Format**: Phone number with or without country code
- **Default**: NULL if not provided
- **Example**: "+255712345678" or "0712345678"
- **Note**: Used for both student and parent/guardian

### 7. **email** (Optional)
- **Format**: Valid email address
- **Default**: Auto-generated as `firstname+lastname@gmail.com` if not provided
- **Example**: "parent@example.com"
- **Note**: Used for parent/guardian account creation

### 8. **admission_no** (Optional)
- **Format**: Admission number as string
- **Default**: NULL if not provided
- **Example**: "ADM001", "2026-001"

### 9. **admission_date** (Optional)
- **Format**: Date in YYYY-MM-DD format
- **Default**: "2026-01-01" if not provided
- **Example**: "2026-01-15", "2026-02-01"

---

## 🎯 How to Use

1. **Download Template** (if available):
   - Go to Student Upload page
   - Click "Download Students Excel Format" button
   - This will download a template with correct headers

2. **Prepare Your Data**:
   - Open the Excel file
   - Fill in the required columns: `student_name`, `class`, `section`, `gender`
   - Optionally fill in: `category`, `phone_number`, `email`, `admission_no`, `admission_date`
   - **Important**: First row must be headers (column names)

3. **Save the File**:
   - Save as `.xlsx`, `.xls`, or `.csv`
   - Ensure column names match exactly (case-sensitive)

4. **Upload**:
   - Go to Student Upload page
   - Select your file
   - Click Submit

---

## ⚠️ Important Notes

### Column Names:
- **Must match exactly** (case-sensitive)
- **First row must be headers**
- Column order doesn't matter, but names must be exact

### Data Requirements:
- **student_name**: Cannot be empty
- **class**: Cannot be empty
- **section**: Cannot be empty
- **gender**: Must be exactly "Male" or "Female"
- **category**: If provided, will be used; otherwise defaults to "Day"

### Auto-Creation:
The system will automatically create:
- Classes (if they don't exist)
- Sections (if they don't exist)
- Student Categories (if they don't exist)
- Parent/Guardian accounts (if email doesn't exist)
- User accounts for parents/guardians

### What Gets Created:
For each student row, the system creates:
1. User account (for parent/guardian)
2. Parent/Guardian record
3. Student record
4. Session Class Student record (links student to class/section for current session)
5. Member record
6. Fee assignments (Outstanding Balance and Outstanding Transport)

---

## 📄 Minimum Required Format

For a basic upload, you only need these 4 columns:

| student_name | class | section | gender |
|--------------|-------|---------|--------|
| John Doe | Form 1 | A | Male |
| Jane Smith | Form 2 | B | Female |

All other columns are optional and will use default values or be auto-generated.

---

## ✅ Validation Rules

- File must be `.xlsx`, `.xls`, or `.csv`
- First row must contain column headers
- `student_name` cannot be empty
- `class` cannot be empty
- `section` cannot be empty
- `gender` must be "Male" or "Female"
- Empty rows are skipped
- Duplicate students (same parent + class) will update existing record instead of creating new one

---

**Status**: ✅ Ready to Use

