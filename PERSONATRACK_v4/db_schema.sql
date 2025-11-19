CREATE DATABASE barangay_profiling;
USE barangay_profiling;

-- TABLE 1: PROFILE
CREATE TABLE profile (
    resident_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    household_id INT(10),
    first_name VARCHAR(18) NOT NULL,
    middle_name VARCHAR(18),
    last_name VARCHAR(18) NOT NULL,
    suffix VARCHAR(3),
    birth_date DATE NOT NULL,
    gender ENUM('M','F','O') NOT NULL,
    civil_status ENUM('Single','Married','Widowed','Separated') NOT NULL,
    nationality VARCHAR(20) NOT NULL,
    religion VARCHAR(20),
    occupation VARCHAR(20),
    educational_attainment VARCHAR(30),
    social_status ENUM('Employed','Unemployed','Student','Senior Citizen','PWD') NOT NULL,
    FOREIGN KEY (household_id) REFERENCES household(household_id) ON DELETE SET NULL
);

-- TABLE 2: CONTACT INFORMATION
CREATE TABLE contact_information (
    contact_no VARCHAR(11) PRIMARY KEY UNIQUE,
    email VARCHAR(20),
    residency_status ENUM('Active','Deceased','Moved Out') NOT NULL DEFAULT 'Active',
    date_registered DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    registered_by INT(10),
    FOREIGN KEY (registered_by) REFERENCES account(account_id) ON DELETE SET NULL
);

-- TABLE 3: HOUSEHOLD / ADDRESS INFORMATION
CREATE TABLE household (
    household_id INT(11) PRIMARY KEY AUTO_INCREMENT,
    house_no VARCHAR(10),
    street VARCHAR(20),
    purok VARCHAR(10),
    barangay VARCHAR(15) NOT NULL,
    city VARCHAR(20) NOT NULL,
    province VARCHAR(20) NOT NULL,
    zipcode CHAR(4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLE 4: ACCOUNT
CREATE TABLE account (
    account_id INT(10) PRIMARY KEY AUTO_INCREMENT,
    resident_id INT(10),
    role_id INT(10) NOT NULL,
    dept_id INT(10),
    username VARCHAR(15) UNIQUE NOT NULL,
    password_hash VARCHAR(100) NOT NULL,
    status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    FOREIGN KEY (resident_id) REFERENCES profile(resident_id) ON DELETE SET NULL,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (dept_id) REFERENCES department(dept_id) ON DELETE SET NULL
);

-- TABLE 5: ROLES
CREATE TABLE roles (
    role_id INT(10) PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

-- TABLE 6: DEPARTMENT
CREATE TABLE department (
    dept_id INT(10) PRIMARY KEY AUTO_INCREMENT,
    dept_name VARCHAR(30) UNIQUE NOT NULL
);

-- TABLE 7: ACTIVITY_LOG
CREATE TABLE activity_log (
    log_id INT(10) PRIMARY KEY AUTO_INCREMENT,
    profile_id INT(10),
    account_id INT(10) NOT NULL,
    action_type ENUM('Add','Update','Delete','View','Login') NOT NULL,
    timestamp DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    remarks VARCHAR(100),
    FOREIGN KEY (profile_id) REFERENCES profile(resident_id) ON DELETE SET NULL,
    FOREIGN KEY (account_id) REFERENCES account(account_id) ON DELETE CASCADE
);

-- Pre-insert sample roles and departments
INSERT INTO roles (role_name) VALUES ('Admin'), ('Staff');
INSERT INTO department (dept_name) VALUES ('Barangay Office');