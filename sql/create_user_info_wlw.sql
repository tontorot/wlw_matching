use wlw;
CREATE TABLE user_info(
 user_id int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
 user_name varchar(10) NOT NULL,
 is_f tinyint(1) NOT NULL DEFAULT 0,
 is_a tinyint(1) NOT NULL DEFAULT 0,
 is_s tinyint(1) NOT NULL DEFAULT 0,
 is_matching tinyint(1) NOT NULL DEFAULT 1
);
