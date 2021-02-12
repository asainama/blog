<?php

use App\Config\DotEnv;

require dirname(__DIR__) . '/vendor/autoload.php';

(new DotEnv(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env'))->load();
$faker = Faker\Factory::create('fr_FR');
$pdo = new PDO(
    "mysql:dbname=" . getenv('DB_NAME') . ";host=" . getenv('DB_HOST'),
    getenv('DB_USER'),
    getenv('DB_USER'),
    [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE post');
$pdo->exec('TRUNCATE TABLE user');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

// Role
$pdo->exec("INSERT INTO role SET role='admin'");
$pdo->exec("INSERT INTO role SET role='user'");

// User
for ($i = 0; $i < 5; $i++) {
    $password = password_hash($faker->password(), PASSWORD_BCRYPT);
    $pdo->exec("INSERT INTO user SET username='{$faker->name()}',
    password='{$password}',
    email='{$faker->email()}'
    , validate = 1
    , role_id = 2
    , code = 0");
}
$p =  password_hash('admin', PASSWORD_BCRYPT);
$pdo->exec("INSERT INTO user SET username='Sylvain Ainama',
    password='{$p}',
    email='s@s.fr'
    , validate = 1
    , role_id = 1
    ,code = 0");

//  Post
for ($i = 0; $i < 50; $i++) {
    try {
        $pdo->exec(
            "INSERT INTO post SET
            title='{$faker->sentence()}',
            slug='{$faker->slug()}',
            created_at='{$faker->date()} {$faker->time()}',
            content = '{$faker->text(1000)}',
            draft = 1,
            user_id = " . random_int(1, 5)
            . ",chapo = '{$faker->text(255)}'"
        );
    } catch (\PDOException $e) {
        print_r($e->getMessage());
    }
}
// Comment
for ($i = 0; $i < 100; $i++) {
    $pdo->exec(
        "INSERT INTO comment SET
        content='{$faker->text(255)}',
        created_at='{$faker->date()} {$faker->time()}',
        validate = 1,
        user_id = " . random_int(1, 5)
        . ",post_id = " . random_int(1, 50)
    );
}
