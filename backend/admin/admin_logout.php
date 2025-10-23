<?php
session_start();

session_unset();
session_destroy();

header("Location: /leilife/public/index.php");
exit;
