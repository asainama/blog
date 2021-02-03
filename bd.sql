CREATE TABLE IF NOT EXISTS role(
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(25),
    UNIQUE KEY unique_role (role)
);

CREATE TABLE IF NOT EXISTS user(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    validate TINYINT NOT NULL,
    code VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    UNIQUE KEY unique_email (email)
);

ALTER TABLE `blogp5`.`user` ADD CONSTRAINT user_role_fk FOREIGN KEY (role_id) REFERENCES `blogp5`.`role` (id) ON DELETE CASCADE

CREATE TABLE IF NOT EXISTS post(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255)NOT NULL,
    slug VARCHAR(255) NOT NULL,
    chapo TEXT(10000) NOT NULL,
    draft TINYINT NOT NULL,
    content TEXT(650000) NOT NULL,
    created_at DATETIME NOT NULL ,
    user_id INT NOT NULL
);
ALTER TABLE `blogp5`.`post` ADD CONSTRAINT post_user_fk FOREIGN KEY (user_id) REFERENCES `blogp5`.`user` (id) ON DELETE CASCADE

CREATE TABLE IF NOT EXISTS comment(
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT(650000) NOT NULL,
    created_at DATETIME NOT NULL,
    validate TINYINT NOT NUll,
    user_id INT NOT NULL,
    post_id INT NOT NULL
);
ALTER TABLE `blogp5`.`comment` ADD CONSTRAINT comment_user_fk FOREIGN KEY (user_id) REFERENCES `blogp5`.`user` (id) ON DELETE CASCADE
ALTER TABLE `blogp5`.`comment` ADD CONSTRAINT comment_post_fk FOREIGN KEY (post_id) REFERENCES `blogp5`.`post` (id) ON DELETE CASCADE

-- INSERT INTO `blogp5`.`post` (name,slug,draft,content,created_at,user_id) VALUES('Blog','blog',0,'Ceci est un message de test',NOW(),1)
-- INSERT INTO `blogp5`.`user` (email,password) VALUES ('a@a.fr','message')