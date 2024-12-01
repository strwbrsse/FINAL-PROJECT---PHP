create schema vaccination;

use vaccination;

select * from user_name;

CREATE TABLE user_name (
    name_id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(255) NOT NULL,
    mname VARCHAR(255),
    lname VARCHAR(255) NOT NULL
);

CREATE TABLE personal (
    personal_id INT AUTO_INCREMENT PRIMARY KEY,
    name_id INT NOT NULL,
    sex VARCHAR(30) NOT NULL,
    civilstat VARCHAR(20) NOT NULL,
    birthday DATE,
    nationality VARCHAR(255) NOT NULL,
    FOREIGN KEY (name_id) REFERENCES user_name(name_id)
);

CREATE TABLE address (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    name_id INT NOT NULL,
    address VARCHAR(300) NOT NULL,
    barangay VARCHAR(300) NOT NULL,
    FOREIGN KEY (name_id) REFERENCES user_name(name_id)
);

CREATE TABLE contact (
    contact_id INT AUTO_INCREMENT PRIMARY KEY,
    name_id INT NOT NULL,
    contact VARCHAR(30) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    FOREIGN KEY (name_id) REFERENCES user_name(name_id)
);

CREATE TABLE employment (
    emp_id INT AUTO_INCREMENT PRIMARY KEY,
    name_id INT NOT NULL,
    employment_stat VARCHAR(255) NOT NULL,
    employer VARCHAR(255),
    profession VARCHAR(255) NOT NULL,
    FOREIGN KEY (name_id) REFERENCES user_name(name_id)
);

CREATE TABLE User_Auth (
    auth_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES personal(personal_id)
);

CREATE TABLE Health (
    health_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    allergy_description TEXT,
    disease_description TEXT,
    FOREIGN KEY (user_id) REFERENCES personal(personal_id)
);

CREATE TABLE Vaccine (
    vaccine_id INT AUTO_INCREMENT PRIMARY KEY,	
    user_id INT NOT NULL,
    vaccine_name VARCHAR(100) NOT NULL,
    date_administered DATE NOT NULL,
    dose_number INT NOT NULL,
    date_booked DATE,
    FOREIGN KEY (user_id) REFERENCES personal(personal_id)
);


TRUNCATE TABLE user_name;
TRUNCATE TABLE personal;
TRUNCATE TABLE address;
TRUNCATE TABLE contact;
TRUNCATE TABLE employment;
TRUNCATE TABLE User_Auth;
TRUNCATE TABLE Health;
TRUNCATE TABLE Vaccine;