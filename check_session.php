<?php session_start(); echo "user_role: " . ($_SESSION["user_role"] ?? "NULL") . "<br>role_nom: " . ($_SESSION["role_nom"] ?? "NULL") . "<br>"; ?>
