-- eLibrary v1 — Migration 004
-- Profile avatar support. Safe to run once on an existing database.
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER status;
