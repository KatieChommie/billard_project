<?php
$page_title = "เมนูอาหาร"; 
// ในที่สุดโค้ด PHP จะถูกใช้ที่นี่เพื่อดึงข้อมูลจาก db_connect.php
// แต่ตอนนี้เราจะเน้นที่ HTML/CSS ก่อนครับ
include 'include/header.php';
?>

<section class="menu-container">
    <h2>เมนูอาหารและเครื่องดื่ม</h2>
    <div class="menu-grid">
        <!--Menus-->
        <div class="menu-item">
            <img src="/billiard/picture/a_plate_of_ff.webp" alt="เฟรนช์ฟรายส์">
            <h3>เฟรนช์ฟรายส์ (French Fries)</h3>
            <p class="description"></p>
            <p class="price">฿ 69</p>
            <button class="add-to-cart-btn">สั่งเลย</button>
        </div>

        <div class="menu-item">
            <img src="/billiard/picture/a_can_of_coke.webp" alt="โค้ก">
            <h3>โค้ก (Coke)</h3>
            <p class="description">เครื่องดื่มเย็นชื่นใจ เหมาะกับเกมบิลเลียด</p>
            <p class="price">฿ 30</p>
            <button class="add-to-cart-btn">สั่งเลย</button>
        </div>

        </div>
</section>

<?php 
// ...
include 'include/footer.php'; 
?>
</main>
</body>
</html>
