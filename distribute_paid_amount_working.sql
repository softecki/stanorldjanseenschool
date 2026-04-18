-- WORKING Pure SQL Solution to Distribute Paid Amount Across Quarters
-- This script processes all students and distributes their total paid_amount
-- across quarters in order: q1 -> q2 -> q3 -> q4, then next entry

-- ============================================
-- METHOD 1: Using Temporary Table (Most Reliable)
-- ============================================

-- Step 1: Create temporary table
DROP TEMPORARY TABLE IF EXISTS temp_distribution;
CREATE TEMPORARY TABLE temp_distribution (
    id INT PRIMARY KEY,
    student_id INT,
    entry_order INT,
    total_paid DECIMAL(15,2),
    q1 DECIMAL(15,2) DEFAULT 0,
    q2 DECIMAL(15,2) DEFAULT 0,
    q3 DECIMAL(15,2) DEFAULT 0,
    q4 DECIMAL(15,2) DEFAULT 0,
    new_q1 DECIMAL(15,2) DEFAULT 0,
    new_q2 DECIMAL(15,2) DEFAULT 0,
    new_q3 DECIMAL(15,2) DEFAULT 0,
    new_q4 DECIMAL(15,2) DEFAULT 0,
    INDEX idx_student_order (student_id, entry_order)
);

-- Step 2: Insert all entries with student totals
INSERT INTO temp_distribution (id, student_id, entry_order, total_paid, q1, q2, q3, q4, new_q1, new_q2, new_q3, new_q4)
SELECT 
    fac.id,
    fac.student_id,
    ROW_NUMBER() OVER (PARTITION BY fac.student_id ORDER BY fac.id) as entry_order,
    (SELECT SUM(paid_amount) FROM fees_assign_childrens WHERE student_id = fac.student_id) as total_paid,
    COALESCE(fac.quater_one, 0) as q1,
    COALESCE(fac.quater_two, 0) as q2,
    COALESCE(fac.quater_three, 0) as q3,
    COALESCE(fac.quater_four, 0) as q4,
    COALESCE(fac.quater_one, 0) as new_q1,
    COALESCE(fac.quater_two, 0) as new_q2,
    COALESCE(fac.quater_three, 0) as new_q3,
    COALESCE(fac.quater_four, 0) as new_q4
FROM fees_assign_childrens fac
ORDER BY fac.student_id, fac.id;

-- Step 3: Process distribution (requires application code to loop through students)
-- OR use the stored procedure approach below

-- ============================================
-- METHOD 2: CORRECTED Stored Procedure (Recommended)
-- ============================================

DELIMITER $$

DROP PROCEDURE IF EXISTS distribute_paid_amount$$

CREATE PROCEDURE distribute_paid_amount()
BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE v_student INT;
    DECLARE cur_students CURSOR FOR
        SELECT DISTINCT student_id
        FROM fees_assign_childrens
        ORDER BY student_id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur_students;

    read_loop: LOOP
        FETCH cur_students INTO v_student;
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- Total paid amount for this student
        SET @totalPaid := (SELECT COALESCE(SUM(paid_amount), 0) 
                           FROM fees_assign_childrens 
                           WHERE student_id = v_student);

        -- Process each entry for this student
        SET @done_entry := 0;
        SET @v_entry_id := 0;
        SET @v_q1 := 0;
        SET @v_q2 := 0;
        SET @v_q3 := 0;
        SET @v_q4 := 0;

        -- Loop through entries for this student
        entry_loop: LOOP
            -- Get next entry
            SELECT id, 
                   COALESCE(quater_one, 0), 
                   COALESCE(quater_two, 0), 
                   COALESCE(quater_three, 0), 
                   COALESCE(quater_four, 0)
            INTO @v_entry_id, @v_q1, @v_q2, @v_q3, @v_q4
            FROM fees_assign_childrens
            WHERE student_id = v_student
              AND id > @v_entry_id
            ORDER BY id
            LIMIT 1;

            -- Exit if no more entries
            IF @v_entry_id IS NULL OR @v_entry_id = 0 THEN
                LEAVE entry_loop;
            END IF;

            -- Exit if no more paid amount to distribute
            IF @totalPaid <= 0 THEN
                LEAVE entry_loop;
            END IF;

            -- Process Quarter 1
            IF @v_q1 > 0 AND @totalPaid > 0 THEN
                IF @totalPaid >= @v_q1 THEN
                    SET @totalPaid = @totalPaid - @v_q1;
                    SET @v_q1 = 0;
                ELSE
                    SET @v_q1 = @v_q1 - @totalPaid;
                    SET @totalPaid = 0;
                END IF;
            END IF;

            -- Process Quarter 2
            IF @v_q2 > 0 AND @totalPaid > 0 THEN
                IF @totalPaid >= @v_q2 THEN
                    SET @totalPaid = @totalPaid - @v_q2;
                    SET @v_q2 = 0;
                ELSE
                    SET @v_q2 = @v_q2 - @totalPaid;
                    SET @totalPaid = 0;
                END IF;
            END IF;

            -- Process Quarter 3
            IF @v_q3 > 0 AND @totalPaid > 0 THEN
                IF @totalPaid >= @v_q3 THEN
                    SET @totalPaid = @totalPaid - @v_q3;
                    SET @v_q3 = 0;
                ELSE
                    SET @v_q3 = @v_q3 - @totalPaid;
                    SET @totalPaid = 0;
                END IF;
            END IF;

            -- Process Quarter 4
            IF @v_q4 > 0 AND @totalPaid > 0 THEN
                IF @totalPaid >= @v_q4 THEN
                    SET @totalPaid = @totalPaid - @v_q4;
                    SET @v_q4 = 0;
                ELSE
                    SET @v_q4 = @v_q4 - @totalPaid;
                    SET @totalPaid = 0;
                END IF;
            END IF;

            -- Update the entry with new quarter values and remained amount
            UPDATE fees_assign_childrens
            SET quater_one = @v_q1,
                quater_two = @v_q2,
                quater_three = @v_q3,
                quater_four = @v_q4,
                remained_amount = @v_q1 + @v_q2 + @v_q3 + @v_q4
            WHERE id = @v_entry_id;

        END LOOP entry_loop;

    END LOOP read_loop;

    CLOSE cur_students;
END$$

DELIMITER ;

-- ============================================
-- METHOD 3: Single Student Processing (Pure SQL)
-- ============================================
-- Use this for processing a single student at a time

-- Replace @student_id with the actual student ID
SET @student_id = 1212;
SET @remaining = (SELECT COALESCE(SUM(paid_amount), 0) FROM fees_assign_childrens WHERE student_id = @student_id);

-- Create temp table for this student
DROP TEMPORARY TABLE IF EXISTS temp_student_dist;
CREATE TEMPORARY TABLE temp_student_dist (
    id INT PRIMARY KEY,
    entry_order INT,
    q1 DECIMAL(15,2),
    q2 DECIMAL(15,2),
    q3 DECIMAL(15,2),
    q4 DECIMAL(15,2),
    new_q1 DECIMAL(15,2),
    new_q2 DECIMAL(15,2),
    new_q3 DECIMAL(15,2),
    new_q4 DECIMAL(15,2)
);

INSERT INTO temp_student_dist (id, entry_order, q1, q2, q3, q4, new_q1, new_q2, new_q3, new_q4)
SELECT 
    id,
    ROW_NUMBER() OVER (ORDER BY id) as entry_order,
    COALESCE(quater_one, 0),
    COALESCE(quater_two, 0),
    COALESCE(quater_three, 0),
    COALESCE(quater_four, 0),
    COALESCE(quater_one, 0),
    COALESCE(quater_two, 0),
    COALESCE(quater_three, 0),
    COALESCE(quater_four, 0)
FROM fees_assign_childrens
WHERE student_id = @student_id
ORDER BY id;

-- Process each entry sequentially
-- Note: This requires multiple UPDATE statements or application-level looping
-- For pure SQL, you'd need to run this for each entry manually or use the stored procedure

-- Example for first entry:
UPDATE temp_student_dist
SET 
    new_q1 = GREATEST(0, q1 - LEAST(q1, @remaining)),
    @remaining = GREATEST(0, @remaining - LEAST(q1, @remaining))
WHERE entry_order = 1;

UPDATE temp_student_dist
SET 
    new_q2 = GREATEST(0, q2 - LEAST(q2, @remaining)),
    @remaining = GREATEST(0, @remaining - LEAST(q2, @remaining))
WHERE entry_order = 1 AND @remaining > 0;

-- Continue for q3, q4, then move to next entry...
-- This becomes tedious, so the stored procedure is recommended

-- ============================================
-- USAGE:
-- ============================================
-- To run the stored procedure:
-- CALL distribute_paid_amount();

-- To process a single student (using stored procedure logic):
-- You would need to modify the procedure to accept a student_id parameter
-- Or use application code to call it for each student


