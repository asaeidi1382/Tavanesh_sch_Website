<!-- Preload Vazirmatn Fonts -->
<link rel="preload" href="https://tavanesh-sch.ir/fonts/Vazirmatn-ExtraLight.woff2" as="font" type="font/woff2" crossorigin="">
<link rel="preload" href="https://tavanesh-sch.ir/fonts/Vazirmatn-Light.woff2" as="font" type="font/woff2" crossorigin="">
<link rel="preload" href="https://tavanesh-sch.ir/fonts/Vazirmatn-Regular.woff2" as="font" type="font/woff2" crossorigin="">
<link rel="preload" href="https://tavanesh-sch.ir/fonts/Vazirmatn-Medium.woff2" as="font" type="font/woff2" crossorigin="">
<link rel="preload" href="https://tavanesh-sch.ir/fonts/Vazirmatn-Bold.woff2" as="font" type="font/woff2" crossorigin="">
<link rel="preload" href="https://tavanesh-sch.ir/fonts/Vazirmatn-ExtraBold.woff2" as="font" type="font/woff2" crossorigin="">
<link rel="preload" href="https://tavanesh-sch.ir/fonts/Vazirmatn-Black.woff2" as="font" type="font/woff2" crossorigin="">
<style>
    @font-face { font-family: 'Vazirmatn'; src: url('/fonts/Vazirmatn-ExtraLight.woff2') format('woff2'); font-weight: 200; font-style: normal; font-display: swap; }
    @font-face { font-family: 'Vazirmatn'; src: url('/fonts/Vazirmatn-Light.woff2') format('woff2'); font-weight: 300; font-style: normal; font-display: swap; }
    @font-face { font-family: 'Vazirmatn'; src: url('/fonts/Vazirmatn-Regular.woff2') format('woff2'); font-weight: 400; font-style: normal; font-display: swap; }
    @font-face { font-family: 'Vazirmatn'; src: url('/fonts/Vazirmatn-Medium.woff2') format('woff2'); font-weight: 500; font-style: normal; font-display: swap; }
    @font-face { font-family: 'Vazirmatn'; src: url('/fonts/Vazirmatn-Bold.woff2') format('woff2'); font-weight: 700; font-style: normal; font-display: swap; }
    @font-face { font-family: 'Vazirmatn'; src: url('/fonts/Vazirmatn-ExtraBold.woff2') format('woff2'); font-weight: 800; font-style: normal; font-display: swap; }
    @font-face { font-family: 'Vazirmatn'; src: url('/fonts/Vazirmatn-Black.woff2') format('woff2'); font-weight: 900; font-style: normal; font-display: swap; }
    :root{ --turquoise: #19b8c2; --turquoise-dark: #0c8790; --turquoise-light: #e6f8fa; --turquoise-lighter: #f0fbfd; --text: #0f3d42; --shadow-sm: 0 4px 15px rgba(0,0,0,0.08); --shadow-md: 0 12px 30px rgba(0,0,0,0.12); --shadow-lg: 0 20px 50px rgba(0,0,0,0.18); --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); --white: #ffffff; }
    body{ background: linear-gradient(to bottom, #f5fbfd, #ffffff); color: var(--text); font-family: "Vazirmatn", sans-serif; line-height: 1.7; }
    .topbar{ background: var(--turquoise); color:#fff; position:sticky; top:0; z-index:1000; box-shadow: var(--shadow-md); }
    .topbar-inner{ max-width: 1400px; margin:0 auto; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; }
    .brand{ display:flex; align-items:center; gap:16px; }
    .logo{ width:72px; height:72px; border-radius:18px; overflow:hidden; background: rgba(255,255,255,0.2); box-shadow: 0 8px 25px rgba(0,0,0,0.2); transition: var(--transition); }
    .logo:hover{ transform: translateY(-4px); }
    .logo img{ width:100%; height:100%; object-fit:contain; }
    .site-title{ font-size:2.5rem; font-weight:800; }
    .page-subtitle{ font-size:1.1rem; opacity:0.95; }
    .topbar-left { display:flex; align-items:center; gap:12px; }
    .user-badge { background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.3); border-radius: 12px; padding: 5px 12px 5px 16px; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 10px; text-decoration: none; color: #fff; transition: var(--transition); }
    .user-badge:hover { background: rgba(255,255,255,.28); transform: translateY(-2px); }
    .user-img { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.5); display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .user-img img { width: 100%; height: 100%; object-fit: cover; }
    .hamburger{ display:none; cursor:pointer; width:50px; height:50px; border-radius:16px; background: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.4); align-items:center; justify-content:center; box-shadow: var(--shadow-sm); transition: var(--transition); }
    .hamburger:hover{ background: rgba(255,255,255,0.3); transform: translateY(-3px); }
    .hamburger .bar{ width:24px; height:3px; background:#fff; border-radius:3px; position:relative; }
    .hamburger .bar::before,.hamburger .bar::after{ content:""; position:absolute; width:24px; height:3px; background:#fff; border-radius:3px; }
    .hamburger .bar::before{ top:-8px; }
    .hamburger .bar::after{ top:8px; }
    aside.sidebar{ background: var(--turquoise); color:#fff; border-radius:20px; padding:20px; box-shadow: var(--shadow-lg); position:relative; overflow:hidden; }
    aside.sidebar::before{ content:""; position:absolute; inset:0; background: linear-gradient(135deg, rgba(255,255,255,0.15), transparent 70%); pointer-events:none; }
    .menu-title{ font-size:1.4rem; font-weight:800; text-align:center; margin-bottom:16px; }
    .menu > li { margin-bottom: 8px; }
    .menu a{ display:flex; justify-content:space-between; align-items:center; padding:14px 18px; margin:4px 0; border-radius:16px; background: rgba(255,255,255,0.15); font-weight:600; text-decoration:none; color:#fff; transition: var(--transition); box-shadow: 0 4px 12px rgba(0,0,0,0.15); cursor: pointer; }
    .menu a:hover{ background: rgba(255,255,255,0.25); transform: translateY(-4px); box-shadow: 0 12px 25px rgba(0,0,0,0.25); }
    .submenu{ display: none; background: rgba(255,255,255,0.1); border-radius: 12px; margin-top: 8px; overflow: hidden; }
    .submenu.open{ display: block; }
    .submenu a{ padding-right: 40px; font-weight: 500; background: transparent; box-shadow: none; }
    .submenu a:hover{ background: rgba(255,255,255,0.2); }
    .caret{ width:0; height:0; border-right:6px solid transparent; border-left:6px solid transparent; border-top:8px solid #fff; transition: transform 0.3s ease; }
    .caret.open{ transform: rotate(180deg); }
    .layout{ max-width: 1400px; margin:24px auto; padding:0 16px; display:grid; grid-template-columns:1fr; gap:28px; }
    main.content{ background: var(--turquoise-lighter); border-radius:20px; padding:24px; box-shadow: var(--shadow-lg); }
    @media(max-width:991px){
      .topbar-inner { padding: 10px 15px; display: flex; flex-direction: row; align-items: center; justify-content: space-between; }
      .hamburger{ display:flex; }
      .topbar-left { gap: 8px; }
      .brand { display: flex; flex-direction: row; align-items: center; text-align: right; gap: 8px; }
      .titles { text-align: right; }
      .site-title { font-size: 1.1rem; }
      .page-subtitle { font-size: 0.7rem; }
      .logo { width: 45px; height: 45px; }
      .user-badge { padding: 4px 8px 4px 12px; font-size: 0.7rem; gap: 5px; }
      .user-img { width: 28px; height: 28px; font-size: 14px; }
      aside.sidebar{ position:fixed; top:0; left:0; bottom:0; width:80vw; max-width:340px; transform:translateX(-101%); transition:var(--transition); z-index:1100; border-radius:0; height: 100vh; }
      aside.sidebar.open{ transform:translateX(0); }
    }
    @media(max-width:480px){ .site-title { font-size: 1rem; } .page-subtitle { font-size: 0.65rem; } .logo { width: 40px; height: 40px; border-radius: 10px; } .user-badge { padding: 3px 6px 3px 10px; font-size: 0.65rem; } .user-img { width: 24px; height: 24px; font-size: 12px; } .hamburger { width: 40px; height: 40px; border-radius: 12px; } .hamburger .bar, .hamburger .bar::before, .hamburger .bar::after { width: 18px; height: 2px; } .hamburger .bar::before { top: -6px; } .hamburger .bar::after { top: 6px; } }
    @media(min-width:992px){ .layout{ grid-template-columns: 310px 1fr; gap:26px; } }
</style>
