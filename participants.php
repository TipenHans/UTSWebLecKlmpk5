<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';
require 'fpdf186/fpdf.php'; 
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
