use test;
CREATE TABLE user_info(
 user_id int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
 user_name varchar(10) NOT NULL,
 fighter_available tinyint(1) NOT NULL DEFAULT 0,
 attacker_available tinyint(1) NOT NULL DEFAULT 0,
 supporter_available tinyint(1) NOT NULL DEFAULT 0,
 is_matching tinyint(1) NOT NULL DEFAULT 1
);
