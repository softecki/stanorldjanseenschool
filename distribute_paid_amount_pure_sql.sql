-- Pure SQL Solution to Distribute Paid Amount Across Quarters
-- This script distributes total paid_amount per student across all their entries' quarters
-- Processing: quater_one -> quater_two -> quater_three -> quater_four, then next entry

-- IMPORTANT: This requires running for each student separately OR using application code to loop
-- For batch processing all students, use the stored procedure version

-- ============================================
-- OPTION 1: Single Query Approach (MySQL 8.0+)
-- ============================================

-- This creates a comprehensive update using CTEs and window functions
-- Note: This is complex and may need adjustment based on your exact requirements

WITH student_totals AS (
    SELECT 
        student_id,
        SUM(paid_amount) as total_paid
    FROM fees_assign_childrens
    GROUP BY student_id
),
ordered_entries AS (
    SELECT 
        fac.id,
        fac.student_id,
        fac.paid_amount,
        COALESCE(fac.quater_one, 0) as q1,
        COALESCE(fac.quater_two, 0) as q2,
        COALESCE(fac.quater_three, 0) as q3,
        COALESCE(fac.quater_four, 0) as q4,
        ROW_NUMBER() OVER (PARTITION BY fac.student_id ORDER BY fac.id) as entry_num,
        st.total_paid
    FROM fees_assign_childrens fac
    JOIN student_totals st ON st.student_id = fac.student_id
)
UPDATE fees_assign_childrens fac
JOIN ordered_entries oe ON fac.id = oe.id
SET 
    fac.quater_one = GREATEST(0, oe.q1 - LEAST(oe.q1, oe.total_paid - 
        COALESCE((SELECT SUM(q1) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num < oe.entry_num), 0))),
    fac.quater_two = GREATEST(0, oe.q2 - LEAST(oe.q2, GREATEST(0, oe.total_paid - 
        COALESCE((SELECT SUM(q1) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num <= oe.entry_num), 0) -
        COALESCE((SELECT SUM(q2) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num < oe.entry_num), 0)))),
    fac.quater_three = GREATEST(0, oe.q3 - LEAST(oe.q3, GREATEST(0, oe.total_paid - 
        COALESCE((SELECT SUM(q1 + q2) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num <= oe.entry_num), 0) -
        COALESCE((SELECT SUM(q3) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num < oe.entry_num), 0)))),
    fac.quater_four = GREATEST(0, oe.q4 - LEAST(oe.q4, GREATEST(0, oe.total_paid - 
        COALESCE((SELECT SUM(q1 + q2 + q3) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num <= oe.entry_num), 0) -
        COALESCE((SELECT SUM(q4) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num < oe.entry_num), 0)))),
    fac.remained_amount = (
        GREATEST(0, oe.q1 - LEAST(oe.q1, oe.total_paid - 
            COALESCE((SELECT SUM(q1) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num < oe.entry_num), 0))) +
        GREATEST(0, oe.q2 - LEAST(oe.q2, GREATEST(0, oe.total_paid - 
            COALESCE((SELECT SUM(q1) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num <= oe.entry_num), 0) -
            COALESCE((SELECT SUM(q2) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num < oe.entry_num), 0))) +
        GREATEST(0, oe.q3 - LEAST(oe.q3, GREATEST(0, oe.total_paid - 
            COALESCE((SELECT SUM(q1 + q2) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num <= oe.entry_num), 0) -
            COALESCE((SELECT SUM(q3) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num < oe.entry_num), 0))) +
        GREATEST(0, oe.q4 - LEAST(oe.q4, GREATEST(0, oe.total_paid - 
            COALESCE((SELECT SUM(q1 + q2 + q3) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num <= oe.entry_num), 0) -
            COALESCE((SELECT SUM(q4) FROM ordered_entries WHERE student_id = oe.student_id AND entry_num < oe.entry_num), 0)))
    );

-- ============================================
-- OPTION 2: Simpler Approach - Process Per Student
-- ============================================
-- This is more reliable and easier to understand
-- Run this query for EACH student_id

-- Example for student_id = 1212:
SET @student_id = 1212;
SET @remaining_paid = (SELECT SUM(paid_amount) FROM fees_assign_childrens WHERE student_id = @student_id);
SET @entry_id = 0;
SET @q1 = 0;
SET @q2 = 0;
SET @q3 = 0;
SET @q4 = 0;

-- Process each entry for this student
UPDATE fees_assign_childrens
SET 
    quater_one = CASE 
        WHEN @remaining_paid > 0 AND COALESCE(quater_one, 0) > 0 THEN
            CASE 
                WHEN @remaining_paid >= COALESCE(quater_one, 0) THEN
                    (@remaining_paid := @remaining_paid - COALESCE(quater_one, 0), 0)
                ELSE
                    (@q1 := COALESCE(quater_one, 0) - @remaining_paid, 
                     @remaining_paid := 0, 
                     @q1)
            END
        ELSE COALESCE(quater_one, 0)
    END,
    quater_two = CASE 
        WHEN @remaining_paid > 0 AND COALESCE(quater_two, 0) > 0 THEN
            CASE 
                WHEN @remaining_paid >= COALESCE(quater_two, 0) THEN
                    (@remaining_paid := @remaining_paid - COALESCE(quater_two, 0), 0)
                ELSE
                    (@q2 := COALESCE(quater_two, 0) - @remaining_paid, 
                     @remaining_paid := 0, 
                     @q2)
            END
        ELSE COALESCE(quater_two, 0)
    END,
    quater_three = CASE 
        WHEN @remaining_paid > 0 AND COALESCE(quater_three, 0) > 0 THEN
            CASE 
                WHEN @remaining_paid >= COALESCE(quater_three, 0) THEN
                    (@remaining_paid := @remaining_paid - COALESCE(quater_three, 0), 0)
                ELSE
                    (@q3 := COALESCE(quater_three, 0) - @remaining_paid, 
                     @remaining_paid := 0, 
                     @q3)
            END
        ELSE COALESCE(quater_three, 0)
    END,
    quater_four = CASE 
        WHEN @remaining_paid > 0 AND COALESCE(quater_four, 0) > 0 THEN
            CASE 
                WHEN @remaining_paid >= COALESCE(quater_four, 0) THEN
                    (@remaining_paid := @remaining_paid - COALESCE(quater_four, 0), 0)
                ELSE
                    (@q4 := COALESCE(quater_four, 0) - @remaining_paid, 
                     @remaining_paid := 0, 
                     @q4)
            END
        ELSE COALESCE(quater_four, 0)
    END,
    remained_amount = (
        CASE 
            WHEN @remaining_paid > 0 AND COALESCE(quater_one, 0) > 0 THEN
                CASE 
                    WHEN @remaining_paid >= COALESCE(quater_one, 0) THEN
                        (@remaining_paid := @remaining_paid - COALESCE(quater_one, 0), 0)
                    ELSE
                        (@q1 := COALESCE(quater_one, 0) - @remaining_paid, 
                         @remaining_paid := 0, 
                         @q1)
                END
            ELSE COALESCE(quater_one, 0)
        END +
        CASE 
            WHEN @remaining_paid > 0 AND COALESCE(quater_two, 0) > 0 THEN
                CASE 
                    WHEN @remaining_paid >= COALESCE(quater_two, 0) THEN
                        (@remaining_paid := @remaining_paid - COALESCE(quater_two, 0), 0)
                    ELSE
                        (@q2 := COALESCE(quater_two, 0) - @remaining_paid, 
                         @remaining_paid := 0, 
                         @q2)
                END
            ELSE COALESCE(quater_two, 0)
        END +
        CASE 
            WHEN @remaining_paid > 0 AND COALESCE(quater_three, 0) > 0 THEN
                CASE 
                    WHEN @remaining_paid >= COALESCE(quater_three, 0) THEN
                        (@remaining_paid := @remaining_paid - COALESCE(quater_three, 0), 0)
                    ELSE
                        (@q3 := COALESCE(quater_three, 0) - @remaining_paid, 
                         @remaining_paid := 0, 
                         @q3)
                END
            ELSE COALESCE(quater_three, 0)
        END +
        CASE 
            WHEN @remaining_paid > 0 AND COALESCE(quater_four, 0) > 0 THEN
                CASE 
                    WHEN @remaining_paid >= COALESCE(quater_four, 0) THEN
                        (@remaining_paid := @remaining_paid - COALESCE(quater_four, 0), 0)
                    ELSE
                        (@q4 := COALESCE(quater_four, 0) - @remaining_paid, 
                         @remaining_paid := 0, 
                         @q4)
                END
            ELSE COALESCE(quater_four, 0)
        END
    )
WHERE student_id = @student_id
ORDER BY id;

-- ============================================
-- OPTION 3: RECOMMENDED - Use Temporary Table Approach
-- ============================================
-- This is the most reliable pure SQL approach

-- Step 1: Create temporary working table
DROP TEMPORARY TABLE IF EXISTS temp_quarter_distribution;
CREATE TEMPORARY TABLE temp_quarter_distribution (
    id INT PRIMARY KEY,
    student_id INT,
    entry_order INT,
    original_q1 DECIMAL(15,2),
    original_q2 DECIMAL(15,2),
    original_q3 DECIMAL(15,2),
    original_q4 DECIMAL(15,2),
    new_q1 DECIMAL(15,2),
    new_q2 DECIMAL(15,2),
    new_q3 DECIMAL(15,2),
    new_q4 DECIMAL(15,2),
    INDEX idx_student_order (student_id, entry_order)
);

-- Step 2: Populate with all entries ordered by student and id
INSERT INTO temp_quarter_distribution 
    (id, student_id, entry_order, original_q1, original_q2, original_q3, original_q4,
     new_q1, new_q2, new_q3, new_q4)
SELECT 
    id,
    student_id,
    ROW_NUMBER() OVER (PARTITION BY student_id ORDER BY id) as entry_order,
    COALESCE(quater_one, 0) as original_q1,
    COALESCE(quater_two, 0) as original_q2,
    COALESCE(quater_three, 0) as original_q3,
    COALESCE(quater_four, 0) as original_q4,
    COALESCE(quater_one, 0) as new_q1,
    COALESCE(quater_two, 0) as new_q2,
    COALESCE(quater_three, 0) as new_q3,
    COALESCE(quater_four, 0) as new_q4
FROM fees_assign_childrens
ORDER BY student_id, id;

-- Step 3: Process each student (this part requires application-level looping)
-- For each student_id, run this block:

-- Get list of all students to process:
SELECT DISTINCT student_id FROM fees_assign_childrens ORDER BY student_id;

-- For each student, execute the following (replace @student_id with actual student_id):
SET @student_id = 1212;  -- Replace with actual student_id
SET @remaining = (SELECT SUM(paid_amount) FROM fees_assign_childrens WHERE student_id = @student_id);

-- Process quarters in order
UPDATE temp_quarter_distribution
SET 
    new_q1 = CASE 
        WHEN @remaining > 0 AND original_q1 > 0 THEN
            IF(@remaining >= original_q1, 
               (@remaining := @remaining - original_q1, 0),
               (@remaining := 0, original_q1 - @remaining))
        ELSE original_q1
    END,
    new_q2 = CASE 
        WHEN @remaining > 0 AND original_q2 > 0 THEN
            IF(@remaining >= original_q2, 
               (@remaining := @remaining - original_q2, 0),
               (@remaining := 0, original_q2 - @remaining))
        ELSE original_q2
    END,
    new_q3 = CASE 
        WHEN @remaining > 0 AND original_q3 > 0 THEN
            IF(@remaining >= original_q3, 
               (@remaining := @remaining - original_q3, 0),
               (@remaining := 0, original_q3 - @remaining))
        ELSE original_q3
    END,
    new_q4 = CASE 
        WHEN @remaining > 0 AND original_q4 > 0 THEN
            IF(@remaining >= original_q4, 
               (@remaining := @remaining - original_q4, 0),
               (@remaining := 0, original_q4 - @remaining))
        ELSE original_q4
    END
WHERE student_id = @student_id
ORDER BY entry_order;

-- Step 4: Update the main table
UPDATE fees_assign_childrens fac
JOIN temp_quarter_distribution tqd ON fac.id = tqd.id
SET 
    fac.quater_one = tqd.new_q1,
    fac.quater_two = tqd.new_q2,
    fac.quater_three = tqd.new_q3,
    fac.quater_four = tqd.new_q4,
    fac.remained_amount = tqd.new_q1 + tqd.new_q2 + tqd.new_q3 + tqd.new_q4
WHERE fac.student_id = @student_id;

-- ============================================
-- RECOMMENDATION: Use the Stored Procedure
-- ============================================
-- The stored procedure you provided is the best solution for this problem.
-- Pure SQL without stored procedures cannot easily handle the sequential logic required.
-- 
-- To use your stored procedure:
-- CALL distribute_paid_amount();

