ALTER TABLE users
    ADD COLUMN first_name VARCHAR(100) NULL AFTER username,
    ADD COLUMN last_name VARCHAR(100) NULL AFTER first_name,
    ADD COLUMN harcerski_stopien ENUM('mlodzik', 'wywiadowca', 'cwik', 'harcerz_orli', 'harcerz_rzeczypospolitej') NULL AFTER last_name,
    ADD COLUMN instruktorski_stopien ENUM('pwd', 'phm', 'hm') NULL AFTER harcerski_stopien;

ALTER TABLE users
    MODIFY role ENUM('admin', 'kadra', 'druh', 'zastepowy', 'druzynowy') NOT NULL DEFAULT 'druh';

UPDATE users
SET
    role = 'druh'
WHERE role = 'kadra';

ALTER TABLE users
    MODIFY role ENUM('admin', 'druh', 'zastepowy', 'druzynowy') NOT NULL DEFAULT 'druh';

UPDATE users
SET
    harcerski_stopien = CASE
        WHEN stopien IN ('mlodzik', 'wywiadowca', 'cwik', 'harcerz_orli', 'harcerz_rzeczypospolitej') THEN stopien
        ELSE harcerski_stopien
    END,
    instruktorski_stopien = CASE
        WHEN stopien IN ('pwd', 'phm', 'hm') THEN stopien
        ELSE instruktorski_stopien
    END;