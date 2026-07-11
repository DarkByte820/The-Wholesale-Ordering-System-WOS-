<?php

$password = "12345678";

$hash = password_hash($password, PASSWORD_BCRYPT);

echo $hash;