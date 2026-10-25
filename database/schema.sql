-- Faculty Feedback System - database schema (MySQL 5.7+ / MariaDB 10.3+)
-- Contains no data. Create the first admin with: php database/create_admin.php

CREATE DATABASE IF NOT EXISTS feedback_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE feedback_system;

CREATE TABLE admins (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE faculty (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(50)  NOT NULL,
    designation   VARCHAR(100) NOT NULL,
    programme     VARCHAR(50)  NOT NULL,
    semester      VARCHAR(5)   NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    mobile        VARCHAR(15)  NOT NULL,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_faculty_semester (semester, is_active)
) ENGINE=InnoDB;

CREATE TABLE students (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(40)  NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    mobile        VARCHAR(15)  NOT NULL,
    programme     VARCHAR(20)  NOT NULL,
    semester      VARCHAR(5)   NOT NULL,
    gender        CHAR(1)      NOT NULL,
    hobbies       VARCHAR(100) NOT NULL DEFAULT '',
    image         VARCHAR(64)  NULL,
    dob           DATE         NOT NULL,
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- One row per (student, faculty). Ratings are 1 (strongly disagree) .. 5 (strongly agree).
CREATE TABLE feedback (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    faculty_id INT UNSIGNED NOT NULL,
    q1  TINYINT UNSIGNED NOT NULL, q2  TINYINT UNSIGNED NOT NULL, q3  TINYINT UNSIGNED NOT NULL,
    q4  TINYINT UNSIGNED NOT NULL, q5  TINYINT UNSIGNED NOT NULL, q6  TINYINT UNSIGNED NOT NULL,
    q7  TINYINT UNSIGNED NOT NULL, q8  TINYINT UNSIGNED NOT NULL, q9  TINYINT UNSIGNED NOT NULL,
    q10 TINYINT UNSIGNED NOT NULL, q11 TINYINT UNSIGNED NOT NULL, q12 TINYINT UNSIGNED NOT NULL,
    liked      TEXT NOT NULL,
    disliked   TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_feedback_student_faculty (student_id, faculty_id),
    KEY idx_feedback_faculty (faculty_id),
    CONSTRAINT fk_feedback_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE,
    CONSTRAINT fk_feedback_faculty FOREIGN KEY (faculty_id) REFERENCES faculty (id) ON DELETE CASCADE,
    CONSTRAINT chk_feedback_ratings CHECK (
        q1 BETWEEN 1 AND 5 AND q2 BETWEEN 1 AND 5 AND q3 BETWEEN 1 AND 5 AND q4 BETWEEN 1 AND 5 AND
        q5 BETWEEN 1 AND 5 AND q6 BETWEEN 1 AND 5 AND q7 BETWEEN 1 AND 5 AND q8 BETWEEN 1 AND 5 AND
        q9 BETWEEN 1 AND 5 AND q10 BETWEEN 1 AND 5 AND q11 BETWEEN 1 AND 5 AND q12 BETWEEN 1 AND 5
    )
) ENGINE=InnoDB;

CREATE TABLE contact (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50)  NOT NULL,
    email      VARCHAR(100) NOT NULL,
    mobile     VARCHAR(15)  NOT NULL,
    message    TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
