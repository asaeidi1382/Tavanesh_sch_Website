<aside class="sidebar" id="sidebar">
  <div class="menu-title">منوی اصلی</div>
  <ul class="menu">
    <li><a href="#" data-toggle="submenu-1"><span>صفحه اصلی</span><span class="caret" id="caret-1"></span></a>
      <ul class="submenu" id="submenu-1">
        <li><a href="weekly.php">برنامه هفتگی</a></li>
        <li><a href="extra.php">کلاس‌های فوق‌برنامه</a></li>
        <li><a href="monthly.php">تقویم آزمون‌ها</a></li>
        <li><a href="tops.php">معرفی نفرات برتر</a></li>
      </ul>
    </li>
    <li><a href="#" data-toggle="submenu-2"><span>معرفی مدرسه</span><span class="caret" id="caret-2"></span></a>
      <ul class="submenu" id="submenu-2">
        <li><a href="history.php">تاریخچه</a></li>
        <li><a href="staff.php">کادر اجرایی</a></li>
        <li><a href="teachers.php">کادر آموزشی</a></li>
        <li><a href="Consulters.php">گروه مشاوران</a></li>
        <li><a href="honors.php">تالار افتخارات</a></li>
      </ul>
    </li>
    <li><a href="gallery.php">گالری تصاویر</a></li>
    <li><a href="learning.php">محتواهای آموزشی</a></li>
    <li><a href="vlearning.php">مدرسه مجازی</a></li>
    <li><a href="services.php">خدمات به مدارس</a></li>
    <li><a href="login.php">ورود کاربران</a></li>
    <li><a href="news.php">اخبار توانش</a></li>
    <li><a href="contactus.php">تماس با ما</a></li>
  </ul>
</aside>
<script>
  (function(){
  const hamburgerBtn = document.getElementById('hamburgerBtn');
  const sidebar = document.getElementById('sidebar');
  if (hamburgerBtn && sidebar) {
    hamburgerBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    document.addEventListener('click', (e) => {
      if(window.innerWidth <= 991 && !sidebar.contains(e.target) && !hamburgerBtn.contains(e.target)){
        sidebar.classList.remove('open');
      }
    });
  }
  document.querySelectorAll('[data-toggle]').forEach(item => {
    item.addEventListener('click', function(e) {
      e.preventDefault(); e.stopPropagation();
      const submenuId = this.getAttribute('data-toggle');
      const submenu = document.getElementById(submenuId);
      const caret = document.getElementById('caret-' + submenuId.split('-')[1]);
      if (submenu) submenu.classList.toggle('open');
      if (caret) caret.classList.toggle('open');
    });
  });
  })();
</script>
