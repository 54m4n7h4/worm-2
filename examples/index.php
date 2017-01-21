<?php
declare(strict_types=1);

require "../vendor/autoload.php";

use WoohooLabs\Worm\Connection\MySqlPdoConnection;
use WoohooLabs\Worm\Examples\Model\StudentModel;
use WoohooLabs\Worm\Query\Condition\ConditionBuilder;
use WoohooLabs\Worm\Worm;

$worm = new Worm(
    MySqlPdoConnection::create(
        "mysql",
        "mysql",
        (int) getenv("MYSQL_PORT"),
        getenv("MYSQL_DATABASE"),
        getenv("MYSQL_USER"),
        getenv("MYSQL_PASSWORD")
    )
);

$result = $worm
    ->selectModel(new StudentModel())
    ->where("first_name", "=", "Nino", "and")
    ->whereNested(
        function (ConditionBuilder $condition) {
            $condition->add("last_name", "=", "Fillmer", "and");
        }
    )
    ->execute();

echo "Result Set:<br/>";
echo "<pre>";
print_r($result);
echo "</pre>";
