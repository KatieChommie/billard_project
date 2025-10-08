<?php
$page_title = "หน้าหลัก"; 
//include 'db_connect.php';
include 'include/header.php';
?>
<section class="hero-section">
    <h1>Let's Billiard!</h1>
    <p>เราเป็นบริการจองโต๊ะบิลเลียดทั่วอาณาบริเวณเทคโน ฯ ลาดกระบัง</p>

    <div class = "services">
        <h2>เราทำอะไรได้บ้าง</h2>
        <p>จองโต๊ะบิลเลียดรอบเทคโน ฯ ลาดกระบัง</p>
        <p>ซื้ออาหารระหว่างการเล่น</p>
        <p>รีวิวร้านบิลเลียดที่เคยเล่น</p>
        <p>สะสมแต้มเพื่อแลกส่วนลด</p>
    </div>
    
    <div class="cta-buttons">
        <a href="/billiard/booking.php" class="btn btn-primary" style="color: inherit;">จองโต๊ะเลย!</a>
        <a href="/billiard/menu.php" class="btn btn-secondary" style="color: inherit;">ดูเมนูอาหาร</a>
    </div>
</section>

<?php include 'include/footer.php'; ?>
