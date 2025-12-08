<?php
session_start();

session_unset();
session_destroy();

header("Location: /Leilife/public/index.php?page=home");
exit;
