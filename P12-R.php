
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>محتواهای آموزشی — دبیرستان دخترانه توانش</title>
    <!-- Preload Vazirmatn Fonts -->








  <style>















    :root{
      --turquoise: #19b8c2;
      --turquoise-dark: #0c8790;
      --turquoise-light: #e6f8fa;
      --turquoise-lighter: #f0fbfd;
      --text: #0f3d42;
      --shadow-sm: 0 4px 15px rgba(0,0,0,0.08);
      --shadow-md: 0 12px 30px rgba(0,0,0,0.12);
      --shadow-lg: 0 20px 50px rgba(0,0,0,0.18);
      --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      --white: #ffffff;
      --turquoise-50: #e0f7fa;
      --turquoise-100: #b2ebf2;
      --turquoise-300: #4dd0e1;
      --turquoise-500: #00bcd4;
      --turquoise-600: #00acc1;
      --turquoise-700: #0097a7;
      --turquoise-900: #006064;

    }

    *{ box-sizing: border-box; margin:0; padding:0; }
    body{
      background: linear-gradient(to bottom, #f5fbfd, #ffffff);
      color: var(--text); font-family: "Vazirmatn", sans-serif; line-height: 1.7;
    }

    /* هدر */
    .topbar{
      background: var(--turquoise); color:#fff; position:sticky; top:0; z-index:1000;
      box-shadow: var(--shadow-md);
    }
    .topbar-inner{
      max-width: 1400px; margin:0 auto; padding:14px 20px;
      display:flex; align-items:center; justify-content:space-between;
    }
    .brand{ display:flex; align-items:center; gap:16px; }
    .logo{
      width:72px; height:72px; border-radius:18px; overflow:hidden;
      background: rgba(255,255,255,0.2); box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    .logo img{ width:100%; height:100%; object-fit:contain; }
    .site-title{ font-size:1.5rem; font-weight:800; }
    .page-subtitle{ font-size:1.1rem; opacity:0.95; }

    .layout{
      max-width: 1400px; margin:24px auto; padding:0 16px;
      display:grid; grid-template-columns:1fr; gap:28px;
    }


    .top-actions {
      padding: 16px;
      text-align: center;
      position: sticky;
      top: 0;
      z-index: 100;
    }


    /* سایدبار */
    aside.sidebar{
      background: var(--turquoise); color:#fff; border-radius:20px; padding:20px;
      box-shadow: var(--shadow-lg); position:relative; overflow:hidden;
    }
    aside.sidebar::before{
      content:""; position:absolute; inset:0;
      background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent 70%);
      pointer-events:none;
    }
    .submenu{ display: none; background: rgba(255,255,255,0.1); border-radius: 12px; margin-top: 8px; overflow: hidden; }
    .submenu.open{ display: block; }
    .submenu a{ padding-right: 40px; font-weight: 500; background: transparent; box-shadow: none; }
    .submenu a:hover{ background: rgba(255,255,255,0.2); }

    main.content{
      background: var(--turquoise-lighter); border-radius:20px; padding:24px;
      box-shadow: var(--shadow-lg);
    }

    /* سکشن محتوا */
    .learning-section h2{
      text-align: center; font-size: 2rem; font-weight: 800; margin-bottom: 32px;
      color: var(--turquoise-dark);
    }

    .grades-grid{
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 24px;
      margin-top: 20px;
    }

    .back-card{
      background: var(--turquoise-dark);
      border-radius: 20px;
      padding: 28px;
      text-align: center;
      box-shadow: var(--shadow-md);
      transition: var(--transition);
      border: 2px solid var(--turquoise-light);
    }
    .grade-card{
      background: #ffffff;
      border-radius: 20px;
      padding: 28px;
      text-align: center;
      box-shadow: var(--shadow-md);
      transition: var(--transition);
      border: 2px solid var(--turquoise-light);
    }
    .grade-card:hover{
      transform: translateY(-1px);
      border-color: white;
      box-shadow: var(--shadow-lg);
      background:  var(--turquoise-100)
    }
    .grade-card h3{
      font-size: 1 rem;
      font-weight: 700;
      color: var(--turquoise-dark);
      margin-bottom: 10px;
    }
    .grade-card p{
      color: var(--text);
      opacity: 0.85;
      font-size: 0.4rem;
    }
    .back-card a{
      display: inline-block;
      margin-top: 16px;
      padding: 10px 20px;
      background: var(--turquoise);
      color: white;
      text-decoration: none;
      border-radius: 12px;
      font-weight: 600;
      transition: var(--transition);
    }
    .grade-card a{
      display: inline-block;
      margin-top: 16px;
      padding: 10px 20px;
      background: var(--turquoise);
      color: white;
      text-decoration: none;
      border-radius: 12px;
      font-weight: 600;
      transition: var(--transition);
    }
    .grade-card a:hover{
      background: var(--turquoise-dark);
      transform: scale(1.05);
    }
    .back-card a:hover{
      background: Black;
      transform: scale(1.05);
    }

        .fixed-back-home { position: fixed; bottom: 10px; left: 50%; transform: translateX(-50%); background: var(--turquoise-600); color: white; padding: 10px 10px;
border-radius: 50px; font-size: 0.6rem; font-weight: 800; text-decoration: none;
box-shadow: 0 8px 25px rgba(0,172,193,.4);
z-index: 999; transition: all .4s ease;
display: flex; align-items: center; gap: 10px; }
        .fixed-back-home:hover { background: var(--turquoise-700); transform: translateX(-50%) translateY(-5px); box-shadow: 0 15px 35px rgba(0,172,193,.5); }

    @media(max-width:991px){
      .hamburger{ display:flex; }
      aside.sidebar{
        position:fixed; top:74px; left:0; bottom:0; width:80vw; max-width:340px;
        transform:translateX(-100%); transition:var(--transition); z-index:1100; border-radius:0;
      }
      aside.sidebar.open{ transform:translateX(0); }
    }
    @media(min-width:992px){
      .layout{ grid-template-columns: 310px 1fr; gap:36px; }
    }
  </style>
  <?php include 'header_styles.php'; ?>
</head>
<body>
<?php include 'topbar.php'; ?>
<div class="layout">
<?php include 'sidebar.php'; ?>
<main class="content">


















  <main class="content" class="content">
    <section class="learning-section">
      <h2>📚 ریاضی دوازدهم</h2>

      <div class="grades-grid">

        <div class="grade-card">
          <h3>مشتق - قسمت اول</h3>
		<div id="91253559605"><script type="text/JavaScript" src="https://www.aparat.com/embed/kvuue36?data[rnddiv]=91253559605&data[responsive]=yes&titleShow=true"></script></div>
        </div>

        <div class="grade-card">
          <h3>مشتق - قسمت دوم</h3>
		<div id="92237671887"><script type="text/JavaScript" src="https://www.aparat.com/embed/cdxwwau?data[rnddiv]=92237671887&data[responsive]=yes&titleShow=true"></script></div>
        </div>

        <div class="grade-card">
        <h3>مشتق - قسمت سوم</h3>
		<div id="66672168951"><script type="text/JavaScript" src="https://www.aparat.com/embed/ykmu24l?data[rnddiv]=66672168951&data[responsive]=yes&titleShow=true"></script></div>
        </div>

        <div class="grade-card">
          <h3>مشتق - قسمت چهارم</h3>
		<div id="42165694476"><script type="text/JavaScript" src="https://www.aparat.com/embed/pln150a?data[rnddiv]=42165694476&data[responsive]=yes&titleShow=true"></script></div>
        </div>

        <div class="grade-card">
          <h3>مشتق - قسمت پنجم</h3>
		<div id="70836419498"><script type="text/JavaScript" src="https://www.aparat.com/embed/vei88et?data[rnddiv]=70836419498&data[responsive]=yes&titleShow=true"></script></div>
        </div>

        <div class="grade-card">
          <h3>مشتق قسمت ششم</h3>
		<div id="45586473166"><script type="text/JavaScript" src="https://www.aparat.com/embed/kjmt7n1?data[rnddiv]=45586473166&data[responsive]=yes&titleShow=true"></script></div>
	</div>
        <div class="grade-card">
          <h3>مشتق قسمت هفتم</h3>
		<div id="41980641716"><script type="text/JavaScript" src="https://www.aparat.com/embed/wrbsu7v?data[rnddiv]=41980641716&data[responsive]=yes&titleShow=true"></script></div>
        </div>

        <div class="grade-card">
          <h3>مشتق قسمت هشتم</h3>
		<div id="63216525677"><script type="text/JavaScript" src="https://www.aparat.com/embed/yao62j1?data[rnddiv]=63216525677&data[responsive]=yes&titleShow=true"></script></div>
        </div>
        </div>
        </div>


<br>
<br>
<br>
<br>
      <h2>فصل چهارم و پنجم</h2>

 <div class="grades-grid">
        <div class="grade-card">
          <h3>آهنگ متوسط و لحظه ای -  قسمت اول</h3>
	<div id="54003746808"><script type="text/JavaScript" src="https://www.aparat.com/embed/yuh02s7?data[rnddiv]=54003746808&data[responsive]=yes&recom=self"></script></div>
        </div>


        <div class="grade-card">
          <h3>آهنگ متوسط و لحظه ای -  قسمت دوم</h3>
	<div id="85349397498"><script type="text/JavaScript" src="https://www.aparat.com/embed/woor733?data[rnddiv]=85349397498&data[responsive]=yes&recom=self"></script></div>
        </div>


        <div class="grade-card">
          <h3>آهنگ متوسط و لحظه ای -  قسمت سوم</h3>
	<div id="11232569517"><script type="text/JavaScript" src="https://www.aparat.com/embed/jma6y11?data[rnddiv]=11232569517&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>یکنوایی -  قسمت اول</h3>
	<div id="55150000471"><script type="text/JavaScript" src="https://www.aparat.com/embed/ceh6pze?data[rnddiv]=55150000471&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>یکنوایی -  قسمت دوم</h3>
	<div id="69527348213"><script type="text/JavaScript" src="https://www.aparat.com/embed/zvqy675?data[rnddiv]=69527348213&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>یکنوایی -  قسمت سوم</h3>
	<div id="90438877419"><script type="text/JavaScript" src="https://www.aparat.com/embed/gayl387?data[rnddiv]=90438877419&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>یکنوایی -  قسمت چهارم</h3>
	<div id="71685424455"><script type="text/JavaScript" src="https://www.aparat.com/embed/lknvcvd?data[rnddiv]=71685424455&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>یکنوایی -  قسمت پنجم</h3>
	<div id="83997372919"><script type="text/JavaScript" src="https://www.aparat.com/embed/uoj019r?data[rnddiv]=83997372919&data[responsive]=yes&recom=self"></script></div>
        </div>


        <div class="grade-card">
          <h3>اکسترمم نسبی قسمت اول</h3>
	<div id="16591022030"><script type="text/JavaScript" src="https://www.aparat.com/embed/rhtefr6?data[rnddiv]=16591022030&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>اکسترمم نسبی قسمت دوم</h3>
	<div id="11400996798"><script type="text/JavaScript" src="https://www.aparat.com/embed/tstmp52?data[rnddiv]=11400996798&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>اکسترمم نسبی قسمت سوم</h3>
	<div id="49931240439"><script type="text/JavaScript" src="https://www.aparat.com/embed/zvpp20t?data[rnddiv]=49931240439&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>اکسترمم نسبی قسمت چهارم</h3>
	<div id="35163885257"><script type="text/JavaScript" src="https://www.aparat.com/embed/bts1ui6?data[rnddiv]=35163885257&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>اکسترمم نسبی قسمت پنجم</h3>
	<div id="46271479983"><script type="text/JavaScript" src="https://www.aparat.com/embed/tlnwnur?data[rnddiv]=46271479983&data[responsive]=yes&recom=self"></script></div>
        </div>


        <div class="grade-card">
          <h3>اکسترمم مطلق قسمت اول</h3>
	<div id="70583734316"><script type="text/JavaScript" src="https://www.aparat.com/embed/foxn003?data[rnddiv]=70583734316&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>اکسترمم مطلق قسمت دوم</h3>
	<div id="14375000273"><script type="text/JavaScript" src="https://www.aparat.com/embed/key2g4g?data[rnddiv]=14375000273&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>اکسترمم مطلق قسمت سوم</h3>
	<div id="52021274144"><script type="text/JavaScript" src="https://www.aparat.com/embed/nnn308t?data[rnddiv]=52021274144&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>اکسترمم مطلق قسمت چهارم</h3>
	<div id="76324860526"><script type="text/JavaScript" src="https://www.aparat.com/embed/ule92af?data[rnddiv]=76324860526&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>اکسترمم مطلق قسمت پنجم</h3>
	<div id="98078506016"><script type="text/JavaScript" src="https://www.aparat.com/embed/gtd7ip7?data[rnddiv]=98078506016&data[responsive]=yes&recom=self"></script></div>
        </div>



        <div class="grade-card">
          <h3>مثالهایی از اکسترمم قسمت اول</h3>
	<div id="90373936682"><script type="text/JavaScript" src="https://www.aparat.com/embed/osb24dz?data[rnddiv]=90373936682&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>مثالهایی از اکسترمم قسمت دوم</h3>
	<div id="89105781468"><script type="text/JavaScript" src="https://www.aparat.com/embed/kmhrhqa?data[rnddiv]=89105781468&data[responsive]=yes&recom=self"></script></div>
        </div>


        <div class="grade-card">
          <h3>نقاط بحرانی قسمت اول</h3>
	<div id="63601080799"><script type="text/JavaScript" src="https://www.aparat.com/embed/xwzej88?data[rnddiv]=63601080799&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>نقاط بحرانی قسمت دوم</h3>
	<div id="21313717715"><script type="text/JavaScript" src="https://www.aparat.com/embed/imteb5e?data[rnddiv]=21313717715&data[responsive]=yes&recom=self"></script></div>
        </div>
        <div class="grade-card">
          <h3>نقاط بحرانی قسمت سوم</h3>
	<div id="92481021622"><script type="text/JavaScript" src="https://www.aparat.com/embed/slq6511?data[rnddiv]=92481021622&data[responsive]=yes&recom=self"></script></div>
        </div>

        <div class="grade-card">
          <h3>قضیه فرما</h3>
	<div id="39697960634"><script type="text/JavaScript" src="https://www.aparat.com/embed/tkjc6qq?data[rnddiv]=39697960634&data[responsive]=yes&recom=self"></script></div>
        </div>





  </div>


        </div>


<br>
<br>
<br>
<br>


    </section>
    <a href="LP12.php"class="fixed-back-home">
        <i class="fas fa-home"></i>بازگشت به فهرست قبل</a>

</div>





</main>
</div>
</body>
</html>