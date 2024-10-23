<?php
session_start();
require 'db.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
