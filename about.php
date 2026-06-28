<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - SimpleShop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-wrapper">
    <?php include 'header.php'; ?>
    <main class="about-page">

        <section class="about-hero">
            <div class="about-hero-text">
                <span class="about-label">Who We Are</span>
                <h1>We are a team of passionate creators and innovators.</h1>
                <p>Our journey began with a simple idea: to make everyday workflows efficient and beautiful. We build products that help businesses scale, <span class="highlight">adapt</span>, and succeed in an ever-changing technological landscape.</p>
                <p>We pride ourselves on attention to detail, transparent workflows, and an unwavering commitment to client satisfaction.</p>
                <a href="mailto:hello@simpleshop.com" class="btn-get-in-touch">Get in Touch</a>
            </div>
            <div class="about-hero-image">
                <img src="images/team.jpg" alt="Our team collaborating">
            </div>
        </section>

        <section class="about-values">
            <article class="value-card">
                <span class="value-icon">💡</span>
                <h3>Innovation</h3>
                <p>Pushing boundaries to develop creative solutions for complex problems.</p>
            </article>
            <article class="value-card">
                <span class="value-icon">🤝</span>
                <h3>Integrity</h3>
                <p>Building honest relationships based on openness and mutual trust.</p>
            </article>
            <article class="value-card">
                <span class="value-icon">🚀</span>
                <h3>Impact</h3>
                <p>Delivering measurable results that drive sustained corporate growth.</p>
            </article>
        </section>

    </main>
    <?php include 'footer.php'; ?>
</div>
</body>
</html>