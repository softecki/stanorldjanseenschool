-- Pure SQL Script to Distribute Paid Amount Across Quarters
-- This script distributes paid_amount across quater_one, quater_two, quater_three, quater_four
-- for all entries of each student

-- Step 1: Create temporary table to store working data
DROP TEMPORARY TABLE IF EXISTS temp_fees_distribution;
CREATE TEMPORARY TABLE temp_fees_distribution (
    id INT PRIMARY KEY,
    student_id INT,
    entry_order INT,
    paid_amount DECIMAL(15,2),
    quater_one DECIMAL(15,2),
    quater_two DECIMAL(15,2),
    quater_three DECIMAL(15,2),
    quater_four DECIMAL(15,2),
    new_quater_one DECIMAL(15,2),
    new_quater_two DECIMAL(15,2),
    new_quater_three DECIMAL(15,2),
    new_quater_four DECIMAL(15,2),
    remaining_paid DECIMAL(15,2),
    INDEX idx_student_order (student_id, entry_order)
);

-- Step 2: Insert all entries with their order for each student
INSERT INTO temp_fees_distribution 
    (id, student_id, entry_order, paid_amount, quater_one, quater_two, quater_three, quater_four,
     new_quater_one, new_quater_two, new_quater_three, new_quater_four, remaining_paid)
SELECT 
    fac.id,
    fac.student_id,
    ROW_NUMBER() OVER (PARTITION BY fac.student_id ORDER BY fac.id) as entry_order,
    fac.paid_amount,
    COALESCE(fac.quater_one, 0) as quater_one,
    COALESCE(fac.quater_two, 0) as quater_two,
    COALESCE(fac.quater_three, 0) as quater_three,
    COALESCE(fac.quater_four, 0) as quater_four,
    COALESCE(fac.quater_one, 0) as new_quater_one,
    COALESCE(fac.quater_two, 0) as new_quater_two,
    COALESCE(fac.quater_three, 0) as new_quater_three,
    COALESCE(fac.quater_four, 0) as new_quater_four,
    SUM(fac.paid_amount) OVER (PARTITION BY fac.student_id) as remaining_paid
FROM fees_assign_childrens fac
ORDER BY fac.student_id, fac.id;

-- Step 3: Initialize remaining_paid per student (total paid amount for each student)
UPDATE temp_fees_distribution tfd
SET remaining_paid = (
    SELECT SUM(paid_amount) 
    FROM fees_assign_childrens 
    WHERE student_id = tfd.student_id
);

-- Step 4: Distribute paid amount across quarters
-- This requires multiple passes, so we'll use a loop-like approach with multiple UPDATE statements

-- For MySQL 8.0+, we can use a recursive CTE approach, but for compatibility, 
-- here's a simpler approach using multiple UPDATE statements in sequence

-- Note: Since pure SQL can't easily loop, this script processes one student at a time
-- You may need to run this for each student, or use the stored procedure version

-- Alternative: Use a script that processes all students
-- This approach uses variables (works in MySQL)

SET @current_student := 0;
SET @remaining_paid := 0;
SET @current_entry_id := 0;
SET @q1 := 0;
SET @q2 := 0;
SET @q3 := 0;
SET @q4 := 0;

-- Process each entry in order
UPDATE temp_fees_distribution tfd
JOIN (
    SELECT 
        id,
        student_id,
        entry_order,
        @remaining_paid := CASE 
            WHEN @current_student != student_id THEN 
                (SELECT SUM(paid_amount) FROM fees_assign_childrens WHERE student_id = tfd.student_id)
            ELSE @remaining_paid
        END as total_paid,
        @current_student := student_id,
        @q1 := COALESCE(quater_one, 0),
        @q2 := COALESCE(quater_two, 0),
        @q3 := COALESCE(quater_three, 0),
        @q4 := COALESCE(quater_four, 0),
        -- Distribute payment
        CASE WHEN @remaining_paid > 0 AND @q1 > 0 THEN
            CASE WHEN @remaining_paid >= @q1 THEN 
                (@remaining_paid := @remaining_paid - @q1, @q1 := 0, @q1)
            ELSE 
                (@q1 := @q1 - @remaining_paid, @remaining_paid := 0, @q1)
            END
        ELSE @q1 END as new_q1,
        CASE WHEN @remaining_paid > 0 AND @q2 > 0 THEN
            CASE WHEN @remaining_paid >= @q2 THEN 
                (@remaining_paid := @remaining_paid - @q2, @q2 := 0, @q2)
            ELSE 
                (@q2 := @q2 - @remaining_paid, @remaining_paid := 0, @q2)
            END
        ELSE @q2 END as new_q2,
        CASE WHEN @remaining_paid > 0 AND @q3 > 0 THEN
            CASE WHEN @remaining_paid >= @q3 THEN 
                (@remaining_paid := @remaining_paid - @q3, @q3 := 0, @q3)
            ELSE 
                (@q3 := @q3 - @remaining_paid, @remaining_paid := 0, @q3)
            END
        ELSE @q3 END as new_q3,
        CASE WHEN @remaining_paid > 0 AND @q4 > 0 THEN
            CASE WHEN @remaining_paid >= @q4 THEN 
                (@remaining_paid := @remaining_paid - @q4, @q4 := 0, @q4)
            ELSE 
                (@q4 := @q4 - @remaining_paid, @remaining_paid := 0, @q4)
            END
        ELSE @q4 END as new_q4
    FROM temp_fees_distribution
    ORDER BY student_id, entry_order
) as calc ON calc.id = tfd.id
SET 
    tfd.new_quater_one = calc.new_q1,
    tfd.new_quater_two = calc.new_q2,
    tfd.new_quater_three = calc.new_q3,
    tfd.new_quater_four = calc.new_q4,
    tfd.remaining_paid = @remaining_paid;

-- The above approach is complex. Here's a simpler, more reliable approach:

-- SIMPLER APPROACH: Process using a cursor-like logic with multiple UPDATEs
-- This requires running for each student, but here's a batch version:

-- Step 1: Reset all quarter values to original (if needed)
-- UPDATE fees_assign_childrens 
-- SET quater_one = COALESCE(quater_one, 0),
--     quater_two = COALESCE(quater_two, 0),
--     quater_three = COALESCE(quater_three, 0),
--     quater_four = COALESCE(quater_four, 0);

-- Step 2: For each student, process their entries
-- This query processes one student at a time
-- You would need to loop through students in application code

-- Here's a working solution using a stored procedure converted to a simpler SQL approach:


