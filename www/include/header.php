<?php
// Shared page header. Include before any page-specific <style>, <script>,
// or </head>; the including page is responsible for closing <head>, opening
// <body>, and eventually pairing with include/footer.php.
//
// Variables the including page may set before include:
//   $title   — the <title> contents (defaults to 'homespring.cloud')
$title = isset($title) ? $title . ' - Homespring.cloud' : 'Homespring.cloud';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<style>
<?php include(__DIR__.'/../common.css'); ?>
</style>
