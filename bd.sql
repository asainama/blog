CREATE TABLE IF NOT EXISTS Role(
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(25),
    UNIQUE KEY unique_role (role)
);

CREATE TABLE IF NOT EXISTS User(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    validate TINYINT NOT NULL,
    code VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    UNIQUE KEY unique_email (email)
);

ALTER TABLE
    user
ADD
    CONSTRAINT user_role_fk FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE CREATE TABLE IF NOT EXISTS Post(
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL,
        chapo TEXT(10000) NOT NULL,
        draft TINYINT NOT NULL,
        content TEXT(650000) NOT NULL,
        created_at DATETIME NOT NULL,
        user_id INT NOT NULL
    );

ALTER TABLE
    post
ADD
    CONSTRAINT post_user_fk FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE CREATE TABLE IF NOT EXISTS Comment(
        id INT AUTO_INCREMENT PRIMARY KEY,
        content TEXT(650000) NOT NULL,
        created_at DATETIME NOT NULL,
        validate TINYINT NOT NUll,
        user_id INT NOT NULL,
        post_id INT NOT NULL
    );

ALTER TABLE
    comment
ADD
    CONSTRAINT comment_user_fk FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
ALTER TABLE
    comment
ADD
    CONSTRAINT comment_post_fk FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE