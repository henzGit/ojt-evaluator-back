CREATE DATABASE IF NOT EXISTS demo_project;
USE demo_project;

CREATE TABLE account (
  id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  first_name TINYTEXT NOT NULL,
  last_name TINYTEXT NOT NULL,
  account_type TINYINT UNSIGNED NOT NULL,
  email TINYTEXT NOT NULL,
  password TINYTEXT NOT NULL,
  mentor MEDIUMINT UNSIGNED,
  mentee MEDIUMINT UNSIGNED,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE phase (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  account_id MEDIUMINT UNSIGNED,
  name TINYTEXT NOT NULL,
  submitted BOOLEAN DEFAULT FALSE,
  status TINYINT SIGNED,
  start_date date NOT NULL,
  end_date date NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE INDEX account_id_in_phase
  ON phase (account_id);

CREATE TABLE task (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  account_id MEDIUMINT UNSIGNED,
  phase_id INT UNSIGNED,
  name TINYTEXT NOT NULL,
  start_date date NOT NULL,
  end_date date NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE INDEX account_id_in_task
  ON task (account_id);
CREATE INDEX phase_id_in_task
  ON task (phase_id)