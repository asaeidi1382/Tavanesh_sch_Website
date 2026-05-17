<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تماس با ما - توانش</title>
    <style>
        :root {
            --turquoise-50: #e0f7fa;
            --turquoise-100: #b2ebf2;
            --turquoise-300: #4dd0e1;
            --turquoise-500: #00bcd4;
            --turquoise-600: #00acc1;
            --turquoise-700: #0097a7;
            --turquoise-900: #006064;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: linear-gradient(135deg, var(--turquoise-50) 0%, #fff 100%);
            color: #424242;
            line-height: 1.7;
            min-height: 100vh;
            position: relative;
            padding-bottom: 120px;
        }
        header { background: var(--turquoise-700); color: white; padding: 1.5rem 0; box-shadow: 0 4px 15px rgba(0,0,0,.1); position: sticky; top: 0; z-index: 100; }
        header h1 { text-align: center; font-size: 2.3rem; margin-bottom: 0.5rem; }
        main { max-width: 1100px; margin: 3rem auto; padding: 0 1.5rem; }
        h2 { text-align: center; font-size: 2.6rem; color: var(--turquoise-700); margin-bottom: 3rem; position: relative; }
        h2::after { content: ''; width: 110px; height: 5px; background: var(--turquoise-500); display: block; margin: 1rem auto; border-radius: 3px; }
        .contact-container { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 4rem; }
        @media (max-width: 868px) { .contact-container { grid-template-columns: 1fr; } }
        .contact-info, form { background: white; padding: 2.8rem; border-radius: 22px; box-shadow: 0 12px 35px rgba(0,188,212,.18); border: 1px solid var(--turquoise-100); }
        .contact-info h3, form h3 { color: var(--turquoise-700); font-size: 1.9rem; margin-bottom: 1.6rem; border-bottom: 3px solid var(--turquoise-300); padding-bottom: .7rem; display: flex; align-items: center; gap: 10px; }
        .contact-info p { margin-bottom: 1.2rem; font-size: 1.12rem; color: #333; }
        .contact-info i { color: var(--turquoise-500); font-size: 1.4rem; }
        label { display: block; margin-top: 1.4rem; font-weight: 600; color: var(--turquoise-900); }
        input, textarea { width: 100%; padding: 14px 16px; margin-top: 8px; border: 2px solid var(--turquoise-100); border-radius: 14px; font-family: inherit; transition: .3s; }
        input:focus, textarea:focus { outline: none; border-color: var(--turquoise-500); box-shadow: 0 0 0 5px rgba(0,188,212,.2); }
        button {
            margin-top: 2.5rem;
            padding: 16px 45px;
            background: var(--turquoise-600);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.15rem;
            font-weight: 600;
            cursor: pointer;
            transition: .4s;
            box-shadow: 0 8px 25px rgba(0,172,193,.4);
            font-family: 'Vazirmatn', sans-serif; /* فونت یکسان با بقیه صفحه */
        }
        button:hover:not(:disabled) { background: var(--turquoise-700); transform: translateY(-4px); }
        button:disabled { background: #ccc; cursor: not-allowed; transform: none; }
        .message { padding: 1rem 1.5rem; margin-top: 1rem; border-radius: 10px; font-weight: 500; display: none; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .map-container { margin-top: 4rem; border-radius: 22px; overflow: hidden; box-shadow: 0 12px 35px rgba(0,188,212,.2); }
        iframe { width: 100%; height: 520px; border: 0; }
        @media (max-width: 576px) { iframe { height: 400px; } }
        .fixed-back-home { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: var(--turquoise-600); color: white; padding: 14px 40px; border-radius: 50px; font-size: 1.1rem; font-weight: 600; text-decoration: none; box-shadow: 0 8px 25px rgba(0,172,193,.4); z-index: 999; transition: all .4s ease; display: flex; align-items: center; gap: 10px; }
        .fixed-back-home:hover { background: var(--turquoise-700); transform: translateX(-50%) translateY(-5px); box-shadow: 0 15px 35px rgba(0,172,193,.5); }
        footer { background: var(--turquoise-900); color: white; text-align: center; padding: 2.5rem; margin-top: 8rem; font-size: 1.05rem; }
    </style>
  <?php include 'header_styles.php'; ?>
</head>
<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">


















        <h2><i class="fas fa-envelope"></i> تماس با ما</h2>

        <div class="contact-container">
            <div class="contact-info">
                <h3><i class="fas fa-info-circle"></i> اطلاعات تماس</h3>
                <p><i class="fas fa-map-marker-alt"></i> آدرس: استان کرمان، زرند، شهرک زیتون، لاله ۳</p>
                <p><i class="fas fa-phone"></i> تلفن: ۰۳۴۳۳۴۰۱۵۲۰</p>
                <p><i class="fas fa-envelope"></i> ایمیل ۱: admin@tavanesh-sch.ir</p>
                <p><i class="fas fa-envelope"></i> ایمیل ۲: tavaneshhs@gmail.com</p>
                <p><i class="fas fa-user-tie"></i>---</p>
                <p><i class="fas fa-mobile-alt"></i> همراه موسس: ۰۹۱۳۸۴۴۱۰۷۵ خانم میرزایی</p>
                <p><i class="fas fa-mobile-alt"></i> همراه مدیر: ۰۹۱۳۲۴۱۲۶۹۶ خانم سعیدی</p>
                <p><i class="fas fa-clock"></i> ساعات پاسخگویی حضوری: شنبه تا چهارشنبه ۸:۰۰ الی ۱۴:۰۰</p>
            </div>

            <form id="contactForm">
                <h3><i class="fas fa-edit"></i> فرم تماس</h3>
                <label for="name">نام شما</label>
                <input type="text" id="name" name="name" placeholder="نام کامل خود را وارد کنید" required>

                <label for="email">ایمیل شما</label>
                <input type="email" id="email" name="email" placeholder="example@email.com" required>

                <label for="message">پیام شما</label>
                <textarea id="message" name="message" rows="6" placeholder="پیام خود را بنویسید..." required></textarea>

                <div id="formMessage" class="message"></div>

                <button type="submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> ارسال پیام
                </button>
            </form>
        </div>

        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d630.5!2d56.5600!3d30.8318!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x03A0x0!2zMzDCsDQ5JzU0LjciTiA1NsKwMzMnMzYuMiJF!5e0!3m2!1sfa!2sir!4v1720000000000!5m2!1sfa!2sir" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>


    <a href="index.php" class="fixed-back-home">
        <i class="fas fa-home"></i> بازگشت به خانه
    </a>

    <footer>
        <p>&copy; ۱۴۰۴ توانش - تمامی حقوق محفوظ است</p>
    </footer>

    <script>
        emailjs.init("KHlXupWmSzLo5W_9k");

        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const formMessage = document.getElementById('formMessage');
            const formData = {
                from_name: document.getElementById('name').value,
                from_email: document.getElementById('email').value,
                message: document.getElementById('message').value
            };

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ارسال...';
            formMessage.style.display = 'none';

            emailjs.send("service_nrz7qyd", "template_dpfik1k", formData)
                .then(function() {
                    formMessage.textContent = '✅ پیام شما با موفقیت دریافت شد. به زودی پاسخ می‌دهیم.';
                    formMessage.className = 'message success';
                    formMessage.style.display = 'block';
                    document.getElementById('contactForm').reset();
                }, function(error) {
                    formMessage.textContent = '❌ خطا در ارسال. لطفاً دوباره تلاش کنید.';
                    formMessage.className = 'message error';
                    formMessage.style.display = 'block';
                    console.error('EmailJS error:', error);
                })
                .finally(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> ارسال پیام';
                });
        });
    </script>





</main>
</div>
</body>
</html>