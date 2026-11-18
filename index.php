<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>MoneyTracker Pro</title>
  <link rel="manifest" href="manifest.json" />
  <meta name="theme-color" content="#2563eb" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
  <link rel="apple-touch-icon" href="icon-192.png" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
  <style>
    :root {
      --theme-primary: #2563eb;
      --theme-primary-dark: #1d4ed8;
      --theme-primary-light: #eff6ff;
      --theme-primary-soft: #dbeafe;
      --theme-gradient-from: #2563eb;
      --theme-gradient-to: #3b82f6;
      --theme-ring: rgba(37, 99, 235, 0.25);
    }
    * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; -webkit-tap-highlight-color: transparent; box-sizing: border-box; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .bg-theme { background-color: var(--theme-primary); }
    .bg-theme-light { background-color: var(--theme-primary-light); }
    .bg-theme-soft { background-color: var(--theme-primary-soft); }
    .text-theme { color: var(--theme-primary); }
    .border-theme { border-color: var(--theme-primary); }
    .gradient-theme { background: linear-gradient(135deg, var(--theme-gradient-from) 0%, var(--theme-gradient-to) 100%); }
    .shadow-theme { box-shadow: 0 10px 25px -5px var(--theme-ring); }
    @keyframes slideUp { from { transform: translateY(100%); opacity: 0.5; } to { transform: translateY(0); opacity: 1; } }
    .animate-slide-up { animation: slideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .map-grid-bg {
      background-color: #e5e9ec;
      background-image: linear-gradient(to right, rgba(255,255,255,0.8) 2px, transparent 2px), linear-gradient(to bottom, rgba(255,255,255,0.8) 2px, transparent 2px);
      background-size: 40px 40px;
    }
  </style>
</head>
<body class="flex justify-center items-center h-dvh overflow-hidden bg-slate-200 sm:py-4">

  <!-- App Shell tanpa outer scroll -->
  <div id="app-shell" class="relative w-full max-w-[440px] bg-slate-50 sm:rounded-[36px] sm:shadow-2xl sm:border-[6px] sm:border-slate-800 h-dvh sm:h-[calc(100dvh-2rem)] sm:max-h-[860px] flex flex-col overflow-hidden">

    <!-- Header -->
    <header class="gradient-theme text-white px-5 pt-4 pb-5 rounded-b-[28px] shadow-md z-10 shrink-0">
      <div class="flex items-center justify-between mb-3.5">
        <button onclick="openBookPickerModal()" class="flex items-center gap-2 bg-white/15 hover:bg-white/25 px-3 py-1.5 rounded-2xl transition">
          <i data-lucide="book-open" class="w-4 h-4"></i>
          <span id="active-book-name" class="text-xs font-bold">Buku Harian</span>
          <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-80"></i>
        </button>

        <div class="flex items-center gap-1.5">
          <button onclick="openThemeQuickModal()" title="Tema" class="p-2 rounded-xl bg-white/15 hover:bg-white/25 transition"><i data-lucide="palette" class="w-4 h-4"></i></button>
          <button onclick="lockAppNow()" title="Kunci" class="p-2 rounded-xl bg-white/15 hover:bg-white/25 transition"><i data-lucide="lock" class="w-4 h-4"></i></button>
        </div>
      </div>

      <!-- Pemilih Bulan & Ringkasan -->
      <div class="bg-white/15 backdrop-blur-md rounded-2xl p-3 border border-white/20">
        <div class="flex items-center justify-between mb-2">
          <button onclick="changeMonth(-1)" class="p-1 rounded-lg hover:bg-white/20"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
          <div class="flex items-center gap-1.5 font-semibold text-sm">
            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
            <span id="current-month-label">November 2026</span>
          </div>
          <button onclick="changeMonth(1)" class="p-1 rounded-lg hover:bg-white/20"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
        </div>
        <div class="grid grid-cols-3 gap-2 text-center divide-x divide-white/20">
          <div><p class="text-[10px] uppercase text-white/80">Pengeluaran</p><p id="month_expend" class="text-xs sm:text-sm font-bold truncate">Rp 0</p></div>
          <div><p class="text-[10px] uppercase text-white/80">Pemasukan</p><p id="month_income" class="text-xs sm:text-sm font-bold truncate">Rp 0</p></div>
          <div><p class="text-[10px] uppercase text-white/80">Saldo</p><p id="month_balance" class="text-xs sm:text-sm font-bold truncate">Rp 0</p></div>
        </div>
      </div>
    </header>

    <!-- Main Content (satu-satunya area yang scroll) -->
    <main id="main-content" class="flex-1 overflow-y-auto no-scrollbar px-4 pt-3.5 pb-24 space-y-3.5">

      <!-- TAB 1: BERANDA / BUKU -->
      <section id="tab-home" class="space-y-3.5">
        <!-- Widget Hari Ini + Tren Mingguan -->
        <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between mb-2.5">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-theme animate-pulse"></span>
              <h2 class="text-xs font-bold text-slate-700 uppercase">Ringkasan Hari Ini</h2>
            </div>
            <div class="flex bg-slate-100 p-0.5 rounded-lg text-[10px] font-semibold">
              <button id="btn-widget-compact" onclick="setWidgetStyle('compact')" class="px-2 py-1 rounded-md bg-white text-slate-800 shadow-xs">Today</button>
              <button id="btn-widget-medium" onclick="setWidgetStyle('medium')" class="px-2 py-1 rounded-md text-slate-500">Weekly</button>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-2.5">
            <div class="bg-rose-50/80 border border-rose-100 rounded-xl p-2.5 flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0"><i data-lucide="arrow-up-right" class="w-4 h-4"></i></div>
              <div class="min-w-0"><p class="text-[10px] text-rose-600/80 font-medium">Keluar</p><p id="today_expend" class="text-xs font-extrabold text-rose-600 truncate">Rp 0</p></div>
            </div>
            <div class="bg-emerald-50/80 border border-emerald-100 rounded-xl p-2.5 flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0"><i data-lucide="arrow-down-left" class="w-4 h-4"></i></div>
              <div class="min-w-0"><p class="text-[10px] text-emerald-600/80 font-medium">Masuk</p><p id="today_income" class="text-xs font-extrabold text-emerald-600 truncate">Rp 0</p></div>
            </div>
          </div>
          <div id="widget-weekly-bars" class="hidden mt-3 pt-2.5 border-t border-slate-100">
            <div class="flex justify-between text-[10px] text-slate-500 mb-1.5"><span>Tren 7 Hari</span><span id="weekly-avg-label" class="text-theme font-semibold">Rata-rata: Rp 0</span></div>
            <div id="mini-weekly-chart" class="grid grid-cols-7 gap-1.5 items-end h-14"></div>
          </div>
        </div>

        <!-- Filter & Pencarian (#tag / nama / nominal) -->
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-slate-200/70 text-[11px] font-semibold">
            <button onclick="setTxFilter('all')" id="filter-all" class="px-2.5 py-1 rounded-lg bg-theme text-white">Semua</button>
            <button onclick="setTxFilter('expense')" id="filter-expense" class="px-2.5 py-1 rounded-lg text-slate-600">Keluar</button>
            <button onclick="setTxFilter('income')" id="filter-income" class="px-2.5 py-1 rounded-lg text-slate-600">Masuk</button>
            <button onclick="setTxFilter('transfer')" id="filter-transfer" class="px-2.5 py-1 rounded-lg text-slate-600">Transfer</button>
          </div>
          <div class="relative flex-1 max-w-[130px]">
            <input type="text" id="search-tx-input" oninput="renderTransactionList()" placeholder="Cari / #tag..." class="w-full pl-7 pr-2 py-1.5 text-xs bg-white border border-slate-200/80 rounded-xl focus:outline-none focus:border-theme" />
            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2"></i>
          </div>
        </div>

        <!-- Daftar Transaksi + Sub-toggle Kalender -->
        <div id="transaction-list-container" class="space-y-3"></div>
      </section>

      <!-- TAB 2: ANALISIS (PENGELUARAN / PEMASUKAN / TREN + KATEGORI/ANGGOTA/AKUN) -->
      <section id="tab-charts" class="hidden space-y-3.5">
        <div class="bg-white p-1 rounded-2xl border border-slate-100 grid grid-cols-3 gap-1 text-xs font-bold">
          <button id="chart-tab-expense" onclick="setChartType('expense')" class="py-2 rounded-xl bg-theme text-white">Pengeluaran</button>
          <button id="chart-tab-income" onclick="setChartType('income')" class="py-2 rounded-xl text-slate-600">Pemasukan</button>
          <button id="chart-tab-trend" onclick="setChartType('trend')" class="py-2 rounded-xl text-slate-600">Tren</button>
        </div>

        <div id="chart-groupby-row" class="bg-white p-1 rounded-xl border border-slate-100 grid grid-cols-3 gap-1 text-[11px] font-semibold">
          <button id="gb-category" onclick="setChartGroupBy('category')" class="py-1.5 rounded-lg bg-theme-light text-theme">Oleh Kategori</button>
          <button id="gb-member" onclick="setChartGroupBy('member')" class="py-1.5 rounded-lg text-slate-500">Oleh Anggota</button>
          <button id="gb-account" onclick="setChartGroupBy('account')" class="py-1.5 rounded-lg text-slate-500">Oleh Akun</button>
        </div>

        <div id="donut-card" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-bold text-slate-800">Proporsi</h3>
            <span id="chart-total-badge" class="text-xs font-bold px-2.5 py-1 rounded-full bg-theme-light text-theme">Rp 0</span>
          </div>
          <div class="relative h-48 flex items-center justify-center">
            <canvas id="categoryDonutChart"></canvas>
            <div id="empty-chart-msg" class="hidden text-center text-slate-400 text-xs"><p>Belum ada data pada bulan ini</p></div>
          </div>
          <div id="category-breakdown-list" class="mt-3 space-y-2 border-t border-slate-100 pt-3"></div>
        </div>

        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
          <h3 class="text-sm font-bold text-slate-800 mb-2">Tren Arus Kas 7 Hari</h3>
          <div class="h-44"><canvas id="weeklyBarChart"></canvas></div>
        </div>
      </section>

      <!-- TAB 3: DOMPET & PORTOFOLIO INVESTASI (SAHAM, EMAS, KRIPTO, REKSADANA) -->
      <section id="tab-wallet" class="hidden space-y-3.5">
        <div class="bg-white p-1 rounded-2xl border border-slate-100 grid grid-cols-2 gap-1 text-xs font-bold">
          <button id="wtab-accounts" onclick="setWalletSub('accounts')" class="py-2 rounded-xl bg-theme text-white">Akun Dompet</button>
          <button id="wtab-invest" onclick="setWalletSub('invest')" class="py-2 rounded-xl text-slate-600">Investasi & Emas</button>
        </div>

        <!-- Sub-view Akun Dompet -->
        <div id="wallet-sub-accounts" class="space-y-3.5">
          <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 text-center">
            <p class="text-[11px] font-semibold text-slate-400 uppercase">Aset Bersih</p>
            <p id="wallet-net" class="text-2xl font-extrabold text-theme mt-0.5">Rp 0</p>
            <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-100">
              <div><p class="text-[10px] text-slate-400">Total Aset</p><p id="wallet-assets" class="text-xs font-bold text-emerald-600">Rp 0</p></div>
              <div><p class="text-[10px] text-slate-400">Total Hutang</p><p id="wallet-debt" class="text-xs font-bold text-rose-600">Rp 0</p></div>
            </div>
          </div>
          <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold text-slate-600 uppercase">Daftar Akun</h3>
            <button onclick="openNewAccountModal()" class="px-3 py-1 rounded-full bg-theme text-white text-[11px] font-bold">+ Akun Baru</button>
          </div>
          <div id="wallet-list" class="space-y-2.5"></div>
        </div>

        <!-- Sub-view Portofolio Investasi -->
        <div id="wallet-sub-invest" class="hidden space-y-3.5">
          <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-1">
              <p class="text-[11px] font-semibold text-slate-400 uppercase">Nilai Portofolio Saat Ini</p>
              <button onclick="refreshLivePrices()" id="btn-live-price" class="px-2.5 py-1 rounded-full bg-theme-light text-theme text-[10px] font-bold flex items-center gap-1">
                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Harga Realtime
              </button>
            </div>
            <p id="inv-total-value" class="text-2xl font-extrabold text-theme">Rp 0</p>
            <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-100 text-center">
              <div><p class="text-[10px] text-slate-400">Total Diinvestasikan</p><p id="inv-total-cost" class="text-xs font-bold text-slate-700">Rp 0</p></div>
              <div><p class="text-[10px] text-slate-400">Untung / Rugi</p><p id="inv-total-pl" class="text-xs font-bold">Rp 0</p></div>
            </div>
          </div>

          <div class="flex items-center justify-between gap-2">
            <div class="flex gap-1 overflow-x-auto no-scrollbar text-[10px] font-bold">
              <button onclick="setInvFilter('all')" id="if-all" class="px-2.5 py-1 rounded-full bg-theme text-white shrink-0">Semua</button>
              <button onclick="setInvFilter('stock')" id="if-stock" class="px-2.5 py-1 rounded-full bg-white text-slate-600 shrink-0">Saham</button>
              <button onclick="setInvFilter('gold')" id="if-gold" class="px-2.5 py-1 rounded-full bg-white text-slate-600 shrink-0">Emas</button>
              <button onclick="setInvFilter('crypto')" id="if-crypto" class="px-2.5 py-1 rounded-full bg-white text-slate-600 shrink-0">Kripto</button>
              <button onclick="setInvFilter('mutual_fund')" id="if-mutual_fund" class="px-2.5 py-1 rounded-full bg-white text-slate-600 shrink-0">Reksadana</button>
              <button onclick="setInvFilter('other')" id="if-other" class="px-2.5 py-1 rounded-full bg-white text-slate-600 shrink-0">Lainnya</button>
            </div>
            <button onclick="openInvestmentModal()" class="px-3 py-1 rounded-full bg-theme text-white text-[11px] font-bold shrink-0">+ Aset</button>
          </div>

          <div id="investment-list" class="space-y-2.5"></div>
        </div>
      </section>

      <!-- TAB 4: FITUR LAINNYA (KALENDER, ANGGARAN, TUJUAN TABUNGAN, TAG, SETELAN) -->
      <section id="tab-more" class="hidden space-y-3.5">
        <div class="grid grid-cols-3 gap-2">
          <button onclick="showMoreSub('calendar')" class="bg-white p-3 rounded-2xl border border-slate-100 flex flex-col items-center gap-1 text-[11px] font-bold text-slate-700"><i data-lucide="calendar-days" class="w-5 h-5 text-theme"></i>Kalender</button>
          <button onclick="showMoreSub('budgets')" class="bg-white p-3 rounded-2xl border border-slate-100 flex flex-col items-center gap-1 text-[11px] font-bold text-slate-700"><i data-lucide="pie-chart" class="w-5 h-5 text-amber-500"></i>Anggaran</button>
          <button onclick="showMoreSub('goals')" class="bg-white p-3 rounded-2xl border border-slate-100 flex flex-col items-center gap-1 text-[11px] font-bold text-slate-700"><i data-lucide="piggy-bank" class="w-5 h-5 text-emerald-500"></i>Tabungan</button>
          <button onclick="showMoreSub('tags')" class="bg-white p-3 rounded-2xl border border-slate-100 flex flex-col items-center gap-1 text-[11px] font-bold text-slate-700"><i data-lucide="hash" class="w-5 h-5 text-pink-500"></i>Kelola Tag</button>
          <button onclick="showMoreSub('settings')" class="bg-white p-3 rounded-2xl border border-slate-100 flex flex-col items-center gap-1 text-[11px] font-bold text-slate-700"><i data-lucide="settings" class="w-5 h-5 text-slate-600"></i>Setelan</button>
          <button onclick="exportDataJSON()" class="bg-white p-3 rounded-2xl border border-slate-100 flex flex-col items-center gap-1 text-[11px] font-bold text-slate-700"><i data-lucide="download" class="w-5 h-5 text-sky-500"></i>Backup</button>
        </div>

        <!-- Sub-panel: Kalender -->
        <div id="more-sub-calendar" class="space-y-3">
          <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100">
            <div class="grid grid-cols-7 text-center text-[10px] font-semibold text-slate-400 mb-1.5"><span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span></div>
            <div id="calendar-grid" class="grid grid-cols-7 gap-1 text-center"></div>
          </div>
          <div id="calendar-selected-details" class="space-y-2"></div>
        </div>

        <!-- Sub-panel: Anggaran -->
        <div id="more-sub-budgets" class="hidden space-y-2.5"></div>

        <!-- Sub-panel: Tujuan Tabungan -->
        <div id="more-sub-goals" class="hidden space-y-2.5"></div>

        <!-- Sub-panel: Tag -->
        <div id="more-sub-tags" class="hidden space-y-2.5"></div>

        <!-- Sub-panel: Setelan -->
        <div id="more-sub-settings" class="hidden space-y-3">
          <div class="bg-white rounded-2xl p-4 border border-slate-100">
            <h3 class="text-xs font-bold text-slate-800 mb-2">Tema Warna</h3>
            <div class="grid grid-cols-3 gap-2" id="theme-selector-grid"></div>
          </div>
          <div class="bg-white rounded-2xl p-4 border border-slate-100 flex items-center justify-between">
            <div><p class="text-xs font-bold text-slate-700">Mata Uang</p><p class="text-[10px] text-slate-400">Format tampilan nominal</p></div>
            <select id="currency-select" onchange="setCurrency(this.value)" class="bg-slate-100 text-xs font-semibold rounded-xl px-3 py-2">
              <option value="IDR">IDR (Rp)</option><option value="USD">USD ($)</option><option value="SGD">SGD (S$)</option><option value="EUR">EUR (€)</option><option value="JPY">JPY (¥)</option>
            </select>
          </div>
          <div class="bg-white rounded-2xl p-4 border border-slate-100 space-y-2.5">
            <div class="flex items-center justify-between">
              <div><p class="text-xs font-bold text-slate-700">Kunci PIN & Biometrik</p><p class="text-[10px] text-slate-400">PIN Default: 1234</p></div>
              <input type="checkbox" id="toggle-security-lock" onchange="handleToggleSecurity(this)" class="w-4 h-4 accent-blue-600" />
            </div>
            <div id="security-actions-row" class="hidden flex gap-2 pt-2 border-t border-slate-100">
              <button onclick="openSetPinModal()" class="flex-1 py-2 rounded-xl bg-theme-light text-theme text-xs font-semibold">Ubah PIN</button>
              <button onclick="lockAppNow()" class="flex-1 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold">Kunci Sekarang</button>
            </div>
          </div>
          <button onclick="clearAllFromZero()" class="w-full py-2.5 rounded-xl border border-rose-200 text-rose-600 bg-white text-xs font-bold">Kosongkan Semua Transaksi & Reset Saldo ke 0</button>
        </div>
      </section>
    </main>

    <!-- Bottom Navigation (5 Slot: Beranda, Grafik, +, Dompet, Lainnya) -->
    <nav class="absolute bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-2 pt-2 pb-3 z-20 flex items-center justify-around">
      <button onclick="switchTab('home')" id="nav-home" class="nav-item flex flex-col items-center gap-1 w-16 text-theme">
        <i data-lucide="book-open" class="w-5 h-5"></i><span class="text-[10px] font-bold">Buku</span>
      </button>
      <button onclick="switchTab('charts')" id="nav-charts" class="nav-item flex flex-col items-center gap-1 w-16 text-slate-400">
        <i data-lucide="pie-chart" class="w-5 h-5"></i><span class="text-[10px] font-medium">Analisis</span>
      </button>
      <div class="relative -top-5">
        <button onclick="openTransactionModal()" class="w-14 h-14 rounded-full gradient-theme text-white shadow-theme flex items-center justify-center ring-4 ring-white active:scale-95 transition">
          <i data-lucide="plus" class="w-7 h-7 stroke-[2.5]"></i>
        </button>
      </div>
      <button onclick="switchTab('wallet')" id="nav-wallet" class="nav-item flex flex-col items-center gap-1 w-16 text-slate-400">
        <i data-lucide="wallet" class="w-5 h-5"></i><span class="text-[10px] font-medium">Dompet</span>
      </button>
      <button onclick="switchTab('more')" id="nav-more" class="nav-item flex flex-col items-center gap-1 w-16 text-slate-400">
        <i data-lucide="layout-grid" class="w-5 h-5"></i><span class="text-[10px] font-medium">Lainnya</span>
      </button>
    </nav>

    <!-- Modal Catat / Edit Transaksi (Pengeluaran / Pemasukan / Transfer + Akun + Anggota + Tag + Peta + Kalkulator) -->
    <div id="transaction-modal" class="hidden absolute inset-0 z-40 bg-slate-900/50 backdrop-blur-xs flex flex-col justify-end">
      <div class="bg-white rounded-t-[30px] w-full max-h-[94%] flex flex-col shadow-2xl animate-slide-up overflow-hidden">
        <div class="px-4 pt-3.5 pb-2.5 border-b border-slate-100 flex items-center justify-between">
          <button onclick="closeTransactionModal()" class="p-1.5 rounded-full bg-slate-100 text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
          <div class="flex bg-slate-100 p-1 rounded-xl text-[11px] font-bold">
            <button id="modal-type-expense" onclick="setModalTxType('expense')" class="px-3 py-1 rounded-lg bg-rose-500 text-white">Keluar</button>
            <button id="modal-type-income" onclick="setModalTxType('income')" class="px-3 py-1 rounded-lg text-slate-600">Masuk</button>
            <button id="modal-type-transfer" onclick="setModalTxType('transfer')" class="px-3 py-1 rounded-lg text-slate-600">Transfer</button>
          </div>
          <button id="btn-delete-tx" onclick="deleteEditingTransaction()" class="hidden p-1.5 rounded-full bg-rose-50 text-rose-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
          <div id="modal-header-spacer" class="w-7"></div>
        </div>

        <div class="p-3.5 overflow-y-auto no-scrollbar flex-1 space-y-2.5">
          <div id="modal-category-wrap"><div id="modal-category-grid" class="grid grid-cols-4 gap-2"></div></div>

          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <label class="text-[10px] text-slate-400 font-semibold block mb-0.5" id="lbl-acc-from">Akun</label>
              <select id="tx-account-select" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold"></select>
            </div>
            <div id="tx-transfer-to-wrap" class="hidden">
              <label class="text-[10px] text-slate-400 font-semibold block mb-0.5">Ke Akun</label>
              <select id="tx-transfer-to-select" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold"></select>
            </div>
            <div id="tx-member-wrap">
              <label class="text-[10px] text-slate-400 font-semibold block mb-0.5">Anggota</label>
              <select id="tx-member-select" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold"></select>
            </div>
          </div>

          <div id="tx-tags-picker" class="flex flex-wrap gap-1.5"></div>

          <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 space-y-2">
            <input type="text" id="tx-note-input" placeholder="Tambahkan catatan transaksi..." class="w-full bg-transparent text-xs text-slate-700 focus:outline-none" />
            <div class="h-px bg-slate-200/70"></div>
            <div class="flex items-center justify-between gap-2">
              <input type="datetime-local" id="tx-datetime-input" class="bg-transparent text-[11px] font-medium text-slate-700 focus:outline-none" />
              <button type="button" onclick="openMapPickerModal()" class="flex items-center gap-1 px-2 py-1 rounded-lg bg-white border border-slate-200 text-[11px] text-slate-700 max-w-[145px]">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i>
                <span id="tx-location-label" class="truncate">Lokasi</span>
              </button>
            </div>
          </div>
        </div>

        <div class="bg-slate-900 text-white p-3.5 rounded-t-3xl">
          <div class="flex items-center justify-between mb-2 px-1">
            <span class="text-xs text-slate-400" id="calc-expression-preview">Nominal</span>
            <div><span class="text-xs text-slate-400 mr-1" id="calc-currency-symbol">Rp</span><span id="calc-display" class="text-xl font-extrabold">0</span></div>
          </div>
          <div class="grid grid-cols-4 gap-1.5 text-sm font-bold">
            <button type="button" onclick="calcPress('7')" class="py-2.5 rounded-xl bg-slate-800">7</button>
            <button type="button" onclick="calcPress('8')" class="py-2.5 rounded-xl bg-slate-800">8</button>
            <button type="button" onclick="calcPress('9')" class="py-2.5 rounded-xl bg-slate-800">9</button>
            <button type="button" onclick="calcBackspace()" class="py-2.5 rounded-xl bg-slate-700 text-amber-400">⌫</button>
            <button type="button" onclick="calcPress('4')" class="py-2.5 rounded-xl bg-slate-800">4</button>
            <button type="button" onclick="calcPress('5')" class="py-2.5 rounded-xl bg-slate-800">5</button>
            <button type="button" onclick="calcPress('6')" class="py-2.5 rounded-xl bg-slate-800">6</button>
            <button type="button" onclick="calcPress('+')" class="py-2.5 rounded-xl bg-slate-700 text-sky-400">+</button>
            <button type="button" onclick="calcPress('1')" class="py-2.5 rounded-xl bg-slate-800">1</button>
            <button type="button" onclick="calcPress('2')" class="py-2.5 rounded-xl bg-slate-800">2</button>
            <button type="button" onclick="calcPress('3')" class="py-2.5 rounded-xl bg-slate-800">3</button>
            <button type="button" onclick="calcPress('-')" class="py-2.5 rounded-xl bg-slate-700 text-sky-400">−</button>
            <button type="button" onclick="calcClear()" class="py-2.5 rounded-xl bg-slate-700 text-rose-400">C</button>
            <button type="button" onclick="calcPress('0')" class="py-2.5 rounded-xl bg-slate-800">0</button>
            <button type="button" onclick="calcPress('000')" class="py-2.5 rounded-xl bg-slate-800">000</button>
            <button type="button" onclick="saveTransaction()" class="py-2.5 rounded-xl gradient-theme text-white">Simpan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Peta Lokasi -->
    <div id="map-picker-modal" class="hidden absolute inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl">
        <div class="p-3.5 border-b border-slate-100 flex justify-between items-center"><h4 class="text-sm font-bold">Pilih Lokasi</h4><button onclick="closeMapPickerModal()" class="p-1 rounded-full bg-slate-100">✕</button></div>
        <div id="interactive-map-box" onclick="handleMapClick(event)" class="relative h-44 map-grid-bg cursor-crosshair">
          <div id="map-pin-marker" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-full flex flex-col items-center">
            <div class="bg-slate-900 text-white text-[10px] px-2 py-0.5 rounded-lg mb-1" id="map-bubble-text">Tangerang, Banten</div>
            <i data-lucide="map-pin" class="w-6 h-6 text-rose-600 fill-rose-500"></i>
          </div>
        </div>
        <div class="p-3.5 space-y-2.5">
          <input type="text" id="map-place-name" value="Tangerang, Banten" class="w-full px-3 py-2 text-xs bg-slate-100 rounded-xl border border-slate-200" />
          <p class="text-[11px] text-slate-400 font-mono" id="map-coords-text">-6.1783, 106.6319</p>
          <div class="flex gap-2">
            <button onclick="clearTransactionLocation()" class="flex-1 py-2 rounded-xl bg-slate-100 text-xs font-semibold">Hapus</button>
            <button onclick="confirmMapLocation()" class="flex-1 py-2 rounded-xl gradient-theme text-white text-xs font-semibold">Pakai Lokasi</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tema Cepat & Generic Dialog -->
    <div id="theme-quick-modal" class="hidden absolute inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-xs p-5 shadow-2xl">
        <div class="flex justify-between items-center mb-3"><h4 class="text-sm font-bold">Pilih Tema</h4><button onclick="closeThemeQuickModal()" class="p-1 rounded-full bg-slate-100">✕</button></div>
        <div id="quick-theme-grid" class="grid grid-cols-3 gap-2"></div>
      </div>
    </div>

    <div id="generic-modal" class="hidden absolute inset-0 z-50 bg-slate-900/50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-xs p-5 shadow-2xl max-h-[80vh] overflow-y-auto" id="generic-modal-body"></div>
    </div>

    <!-- Modal PIN -->
    <div id="set-pin-modal" class="hidden absolute inset-0 z-50 bg-slate-900/60 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl w-full max-w-xs p-5 text-center space-y-3">
        <h4 class="text-sm font-bold">Atur PIN 4-Digit</h4>
        <input type="password" id="new-pin-input" maxlength="4" inputmode="numeric" placeholder="••••" class="w-32 mx-auto text-center tracking-[0.5em] text-xl font-bold py-2 bg-slate-100 rounded-2xl border border-slate-200" />
        <div class="flex gap-2">
          <button onclick="document.getElementById('set-pin-modal').classList.add('hidden')" class="flex-1 py-2 rounded-xl bg-slate-100 text-xs font-semibold">Batal</button>
          <button onclick="saveNewPin()" class="flex-1 py-2 rounded-xl gradient-theme text-white text-xs font-semibold">Simpan</button>
        </div>
      </div>
    </div>

    <!-- Layar Kunci PIN -->
    <div id="lock-screen-overlay" class="hidden absolute inset-0 z-50 gradient-theme text-white flex flex-col items-center justify-between p-8 select-none">
      <div class="text-center mt-6">
        <div class="w-14 h-14 rounded-3xl bg-white/20 flex items-center justify-center mx-auto mb-3"><i data-lucide="lock" class="w-7 h-7"></i></div>
        <h2 class="text-lg font-bold">Aplikasi Terkunci</h2>
        <div class="flex justify-center gap-3 mt-5" id="pin-dots-container">
          <span class="w-3.5 h-3.5 rounded-full border-2 border-white/70"></span><span class="w-3.5 h-3.5 rounded-full border-2 border-white/70"></span>
          <span class="w-3.5 h-3.5 rounded-full border-2 border-white/70"></span><span class="w-3.5 h-3.5 rounded-full border-2 border-white/70"></span>
        </div>
      </div>
      <div class="w-full max-w-[240px] grid grid-cols-3 gap-3 text-lg font-bold">
        <button onclick="enterLockPin('1')" class="h-12 rounded-2xl bg-white/15">1</button><button onclick="enterLockPin('2')" class="h-12 rounded-2xl bg-white/15">2</button><button onclick="enterLockPin('3')" class="h-12 rounded-2xl bg-white/15">3</button>
        <button onclick="enterLockPin('4')" class="h-12 rounded-2xl bg-white/15">4</button><button onclick="enterLockPin('5')" class="h-12 rounded-2xl bg-white/15">5</button><button onclick="enterLockPin('6')" class="h-12 rounded-2xl bg-white/15">6</button>
        <button onclick="enterLockPin('7')" class="h-12 rounded-2xl bg-white/15">7</button><button onclick="enterLockPin('8')" class="h-12 rounded-2xl bg-white/15">8</button><button onclick="enterLockPin('9')" class="h-12 rounded-2xl bg-white/15">9</button>
        <button onclick="simulateFingerprintUnlock()" class="h-12 rounded-2xl bg-white/25 flex items-center justify-center"><i data-lucide="fingerprint" class="w-6 h-6"></i></button>
        <button onclick="enterLockPin('0')" class="h-12 rounded-2xl bg-white/15">0</button>
        <button onclick="backspaceLockPin()" class="h-12 rounded-2xl bg-white/15">⌫</button>
      </div>
      <p class="text-[11px] text-white/80">PIN Default: <b>1234</b></p>
    </div>

    <div id="toast-banner" class="hidden absolute top-14 left-1/2 -translate-x-1/2 z-50 bg-slate-900/95 text-white text-xs px-4 py-2 rounded-full shadow-xl whitespace-nowrap"><span id="toast-message"></span></div>
  </div>

  <script>
    const APP_THEMES = {
      theme_blue: { id:'theme_blue', name:'Biru', primary:'#2563eb', dark:'#1d4ed8', light:'#eff6ff', soft:'#dbeafe', from:'#2563eb', to:'#3b82f6', ring:'rgba(37,99,235,0.28)' },
      theme_green: { id:'theme_green', name:'Hijau', primary:'#059669', dark:'#047857', light:'#ecfdf5', soft:'#d1fae5', from:'#059669', to:'#10b981', ring:'rgba(5,150,105,0.28)' },
      theme_orange: { id:'theme_orange', name:'Oranye', primary:'#ea580c', dark:'#c2410c', light:'#fff7ed', soft:'#ffedd5', from:'#ea580c', to:'#f97316', ring:'rgba(234,88,12,0.28)' },
      theme_pink: { id:'theme_pink', name:'Pink', primary:'#db2777', dark:'#be185d', light:'#fdf2f8', soft:'#fce7f3', from:'#db2777', to:'#ec4899', ring:'rgba(219,39,119,0.28)' },
      theme_purple: { id:'theme_purple', name:'Ungu', primary:'#7c3aed', dark:'#6d28d9', light:'#f5f3ff', soft:'#ede9fe', from:'#7c3aed', to:'#8b5cf6', ring:'rgba(124,58,237,0.28)' },
      theme_red: { id:'theme_red', name:'Merah', primary:'#dc2626', dark:'#b91c1c', light:'#fef2f2', soft:'#fee2e2', from:'#dc2626', to:'#ef4444', ring:'rgba(220,38,38,0.28)' }
    };
    const CURRENCIES = { IDR:{symbol:'Rp',locale:'id-ID',decimals:0}, USD:{symbol:'$',locale:'en-US',decimals:2}, SGD:{symbol:'S$',locale:'en-SG',decimals:2}, EUR:{symbol:'€',locale:'de-DE',decimals:2}, JPY:{symbol:'¥',locale:'ja-JP',decimals:0} };
    const ACCOUNT_TYPES = { 1:'Tunai', 2: 'Kartu Debit', 3:'Kartu Deposit', 4:'Kartu Kredit', 5:'Akun Virtual', 6:'Berinvestasi', 7:'Piutang', 8:'Hutang' };
    const CATEGORIES = {
      expense: [
        { id:'food', name:'Makanan', icon:'utensils', color:'#f97316', bg:'#ffedd5' },
        { id:'transport', name:'Transportasi', icon:'car', color:'#3b82f6', bg:'#dbeafe' },
        { id:'shopping', name:'Belanja', icon:'shopping-bag', color:'#ec4899', bg:'#fce7f3' },
        { id:'bills', name:'Tagihan', icon:'receipt', color:'#eab308', bg:'#fef9c3' },
        { id:'entertainment', name:'Hiburan', icon:'gamepad-2', color:'#8b5cf6', bg:'#ede9fe' },
        { id:'health', name:'Kesehatan', icon:'heart-pulse', color:'#ef4444', bg:'#fee2e2' },
        { id:'education', name:'Pendidikan', icon:'graduation-cap', color:'#06b6d4', bg:'#cffafe' },
        { id:'other_exp', name:'Lainnya', icon:'more-horizontal', color:'#64748b', bg:'#f1f5f9' }
      ],
      income: [
        { id:'salary', name:'Gaji', icon:'briefcase', color:'#10b981', bg:'#d1fae5' },
        { id:'bonus', name:'Bonus', icon:'gift', color:'#f59e0b', bg:'#fef3c7' },
        { id:'investment', name:'Investasi', icon:'trending-up', color:'#3b82f6', bg:'#dbeafe' },
        { id:'freelance', name:'Freelance', icon:'laptop', color:'#8b5cf6', bg:'#ede9fe' },
        { id:'refund', name:'Cashback', icon:'rotate-ccw', color:'#06b6d4', bg:'#cffafe' },
        { id:'other_inc', name:'Lainnya', icon:'coins', color:'#64748b', bg:'#f1f5f9' }
      ],
      transfer: [{ id:'transfer', name:'Transfer Akun', icon:'arrow-left-right', color:'#0ea5e9', bg:'#e0f2fe' }]
    };

    let appState = { activeBookId:1, theme:'theme_blue', lang:'id', currency:'IDR', widgetStyle:'compact', securityEnabled:false, pinCode:'1234', books:[], accounts:[], members:[], tags:[], budgets:[], goals:[], investments:[], transactions:[] };
    let currentTab='home', selectedYear=new Date().getFullYear(), selectedMonth=new Date().getMonth(), txFilter='all', chartReportType='expense', chartGroupBy='category', selectedCalendarDate=toLocalDateStr(new Date());
    let editingTxId=null, modalTxType='expense', modalCategoryId='food', modalTagIds=[], calcExpression='', tempLocation=null, currentLockPinInput='';
    let donutChartInstance=null, weeklyBarChartInstance=null;

    function toLocalDateStr(d){ return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
    function formatMoney(amt){ const c=CURRENCIES[appState.currency]||CURRENCIES.IDR; return `${amt<0?'-':''}${c.symbol} ${Math.abs(Number(amt)||0).toLocaleString(c.locale,{minimumFractionDigits:c.decimals,maximumFractionDigits:c.decimals})}`; }
    function getCategoryInfo(type,id){ const list=CATEGORIES[type]||CATEGORIES.expense; return list.find(c=>c.id===id)||list[0]; }
    function showToast(m){ const b=document.getElementById('toast-banner'); document.getElementById('toast-message').textContent=m; b.classList.remove('hidden'); clearTimeout(window._tt); window._tt=setTimeout(()=>b.classList.add('hidden'),2400); }

    async function apiCall(action, payload=null){
      const opts=payload!==null?{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}:{method:'GET'};
      const res=await fetch(`api.php?action=${action}`, opts);
      const data=await res.json();
      if(!res.ok||!data.ok) throw new Error(data.error||'Server error');
      return data;
    }

    async function loadState(){
      const d = await apiCall('bootstrap');
      appState = { ...appState, ...d.settings, books:d.books, accounts:d.accounts, members:d.members, tags:d.tags, budgets:d.budgets, goals:d.goals, investments:d.investments||[], transactions:d.transactions };
      const activeBook = appState.books.find(b=>b.id==appState.activeBookId) || appState.books[0];
      if(activeBook){ appState.activeBookId = Number(activeBook.id); document.getElementById('active-book-name').textContent = activeBook.name; }
    }

    function saveSettings(){
      apiCall('save_settings', { activeBookId:appState.activeBookId, theme:appState.theme, lang:appState.lang, currency:appState.currency, widgetStyle:appState.widgetStyle, securityEnabled:appState.securityEnabled, pinCode:appState.pinCode }).catch(console.error);
    }

    function applyTheme(k){
      const t=APP_THEMES[k]||APP_THEMES.theme_blue; appState.theme=t.id;
      const r=document.documentElement;
      r.style.setProperty('--theme-primary',t.primary); r.style.setProperty('--theme-primary-dark',t.dark);
      r.style.setProperty('--theme-primary-light',t.light); r.style.setProperty('--theme-primary-soft',t.soft);
      r.style.setProperty('--theme-gradient-from',t.from); r.style.setProperty('--theme-gradient-to',t.to); r.style.setProperty('--theme-ring',t.ring);
      saveSettings(); renderThemeSelectors();
    }

    function renderThemeSelectors(){
      ['theme-selector-grid','quick-theme-grid'].forEach(id=>{
        const el=document.getElementById(id); if(!el) return;
        el.innerHTML=Object.values(APP_THEMES).map(t=>`
          <button onclick="applyTheme('${t.id}');closeThemeQuickModal();" class="p-2 rounded-2xl border-2 flex flex-col items-center gap-1 ${appState.theme===t.id?'border-slate-800 bg-slate-50':'border-slate-100'}">
            <span class="w-6 h-6 rounded-full" style="background:linear-gradient(135deg,${t.from},${t.to})"></span>
            <span class="text-[10px] font-semibold">${t.name}</span>
          </button>`).join('');
      });
    }

    function getBookMonthTransactions(){
      const prefix=`${selectedYear}-${String(selectedMonth+1).padStart(2,'0')}`;
      return appState.transactions.filter(t=>t.bookId==appState.activeBookId && t.date.startsWith(prefix));
    }

    function switchTab(tab){
      currentTab=tab;
      ['home','charts','wallet','more'].forEach(t=>{
        document.getElementById(`tab-${t}`).classList.toggle('hidden',t!==tab);
        const btn=document.getElementById(`nav-${t}`);
        btn.classList.toggle('text-theme',t===tab); btn.classList.toggle('text-slate-400',t!==tab);
      });
      if(tab==='charts') renderCharts();
      if(tab==='wallet') renderWallet();
      if(tab==='more') renderCalendarView();
      lucide.createIcons();
    }

    function changeMonth(delta){
      selectedMonth+=delta; if(selectedMonth<0){selectedMonth=11;selectedYear--;} else if(selectedMonth>11){selectedMonth=0;selectedYear++;}
      refreshAllViews();
    }

    function setWidgetStyle(s){
      appState.widgetStyle=s; saveSettings();
      document.getElementById('widget-weekly-bars').classList.toggle('hidden',s!=='medium');
      document.getElementById('btn-widget-medium').className=s==='medium'?'px-2 py-1 rounded-md bg-white text-slate-800 shadow-xs':'px-2 py-1 rounded-md text-slate-500';
      document.getElementById('btn-widget-compact').className=s==='compact'?'px-2 py-1 rounded-md bg-white text-slate-800 shadow-xs':'px-2 py-1 rounded-md text-slate-500';
    }

    function setTxFilter(f){
      txFilter=f;
      ['all','expense','income','transfer'].forEach(x=>{
        document.getElementById(`filter-${x}`).className=x===f?'px-2.5 py-1 rounded-lg bg-theme text-white':'px-2.5 py-1 rounded-lg text-slate-600';
      });
      renderTransactionList();
    }

    function renderSummariesAndWidget(){
      const mNames=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
      document.getElementById('current-month-label').textContent=`${mNames[selectedMonth]} ${selectedYear}`;
      const list=getBookMonthTransactions();
      let exp=0, inc=0;
      list.forEach(t=>{ if(t.type==='expense') exp+=t.amount; else if(t.type==='income') inc+=t.amount; });
      document.getElementById('month_expend').textContent=formatMoney(exp);
      document.getElementById('month_income').textContent=formatMoney(inc);
      document.getElementById('month_balance').textContent=formatMoney(inc-exp);

      const todayStr=toLocalDateStr(new Date());
      let tExp=0, tInc=0;
      appState.transactions.filter(t=>t.bookId==appState.activeBookId && t.date===todayStr).forEach(t=>{ if(t.type==='expense') tExp+=t.amount; else if(t.type==='income') tInc+=t.amount; });
      document.getElementById('today_expend').textContent=formatMoney(tExp);
      document.getElementById('today_income').textContent=formatMoney(tInc);

      // Mini weekly chart
      const c=document.getElementById('mini-weekly-chart');
      const dLbl=['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
      let days=[], total7=0;
      for(let i=6;i>=0;i--){
        const d=new Date(); d.setDate(d.getDate()-i); const ds=toLocalDateStr(d);
        const amt=appState.transactions.filter(t=>t.bookId==appState.activeBookId&&t.date===ds&&t.type==='expense').reduce((s,x)=>s+x.amount,0);
        total7+=amt; days.push({lbl:dLbl[d.getDay()],amt,today:i===0});
      }
      const max=Math.max(...days.map(x=>x.amt),1);
      document.getElementById('weekly-avg-label').textContent=`Rata-rata: ${formatMoney(Math.round(total7/7))}`;
      c.innerHTML=days.map(d=>`<div class="flex flex-col items-center gap-1 h-full justify-end"><div class="w-full bg-slate-100 rounded-md h-9 flex items-end overflow-hidden"><div class="w-full rounded-md ${d.today?'bg-theme':'bg-theme-soft'}" style="height:${Math.max(12,Math.round(d.amt/max*100))}%"></div></div><span class="text-[9px] font-semibold ${d.today?'text-theme':'text-slate-400'}">${d.lbl}</span></div>`).join('');
    }

    function renderTransactionList(){
      const container=document.getElementById('transaction-list-container');
      const q=(document.getElementById('search-tx-input').value||'').toLowerCase().trim();
      let list=getBookMonthTransactions();
      if(txFilter!=='all') list=list.filter(t=>t.type===txFilter);
      if(q){
        if(q.startsWith('#')){
          const tk=q.slice(1);
          list=list.filter(t=>(t.tags||[]).some(tg=>tg.name.toLowerCase().includes(tk)));
        } else {
          list=list.filter(t=>{
            const cat=getCategoryInfo(t.type,t.categoryId);
            return (t.note||'').toLowerCase().includes(q)||cat.name.toLowerCase().includes(q)||String(t.amount).includes(q);
          });
        }
      }
      list.sort((a,b)=>`${b.date} ${b.time}`.localeCompare(`${a.date} ${a.time}`));
      if(!list.length){ container.innerHTML=`<div class="bg-white rounded-2xl p-6 text-center text-xs text-slate-400 border border-slate-100">Belum ada transaksi. Ketuk <b>+</b> untuk mencatat.</div>`; return; }

      const grouped={};
      list.forEach(t=>{ if(!grouped[t.date]) grouped[t.date]=[]; grouped[t.date].push(t); });
      container.innerHTML=Object.entries(grouped).map(([dt,items])=>{
        let dExp=0,dInc=0; items.forEach(i=>{if(i.type==='expense')dExp+=i.amount; else if(i.type==='income')dInc+=i.amount;});
        const rows=items.map(tx=>{
          const cat=getCategoryInfo(tx.type,tx.categoryId);
          const acc=appState.accounts.find(a=>a.id==tx.accountId);
          const toAcc=tx.transferToId?appState.accounts.find(a=>a.id==tx.transferToId):null;
          const mem=appState.members.find(m=>m.id==tx.memberId);
          const tagsHtml=(tx.tags||[]).map(tg=>`<span class="px-1.5 py-0.5 rounded-full text-[9px] text-white font-bold" style="background:${tg.color}">#${tg.name}</span>`).join('');
          const amtColor=tx.type==='expense'?'text-rose-600':tx.type==='income'?'text-emerald-600':'text-sky-600';
          const sign=tx.type==='expense'?'-':tx.type==='income'?'+':'⇄ ';
          return `
            <div onclick="openTransactionModal('${tx.id}')" class="flex items-center justify-between p-3 hover:bg-slate-50 cursor-pointer">
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:${cat.bg};color:${cat.color}"><i data-lucide="${cat.icon}" class="w-4 h-4"></i></div>
                <div class="min-w-0">
                  <div class="flex items-center gap-1.5"><p class="text-xs font-bold text-slate-800 truncate">${cat.name}</p><span class="text-[10px] text-slate-400">• ${tx.time}</span></div>
                  <p class="text-[11px] text-slate-500 truncate">${tx.note || (toAcc?`${acc?.name} → ${toAcc?.name}`:acc?.name||'')}</p>
                  <div class="flex flex-wrap items-center gap-1 mt-0.5">${acc?`<span class="text-[9px] text-slate-400">${acc.name}${toAcc?' → '+toAcc.name:''}</span>`:''}${mem?`<span class="text-[9px] text-slate-400">• ${mem.name}</span>`:''}${tagsHtml}</div>
                </div>
              </div>
              <p class="text-xs font-extrabold ${amtColor} shrink-0 ml-2">${sign}${formatMoney(tx.amount)}</p>
            </div>`;
        }).join('');
        return `<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden"><div class="px-3.5 py-1.5 bg-slate-50 border-b border-slate-100 flex justify-between text-[11px] font-bold text-slate-600"><span>${dt}</span><span>${dInc?`+${formatMoney(dInc)} `:''}${dExp?`-${formatMoney(dExp)}`:''}</span></div><div class="divide-y divide-slate-100">${rows}</div></div>`;
      }).join('');
      lucide.createIcons();
    }

    // Modal Transaksi
    function openTransactionModal(txId=null){
      editingTxId=txId;
      document.getElementById('btn-delete-tx').classList.toggle('hidden',!txId);
      document.getElementById('modal-header-spacer').classList.toggle('hidden',!!txId);
      document.getElementById('calc-currency-symbol').textContent=(CURRENCIES[appState.currency]||CURRENCIES.IDR).symbol;

      const accSel=document.getElementById('tx-account-select');
      const toSel=document.getElementById('tx-transfer-to-select');
      accSel.innerHTML=appState.accounts.map(a=>`<option value="${a.id}">${a.name}</option>`).join('');
      toSel.innerHTML=accSel.innerHTML;
      if(appState.accounts.length>1) toSel.selectedIndex=1;
      document.getElementById('tx-member-select').innerHTML=appState.members.map(m=>`<option value="${m.id}">${m.name}</option>`).join('');

      if(txId){
        const tx=appState.transactions.find(t=>t.id===txId);
        modalTxType=tx.type; modalCategoryId=tx.categoryId; modalTagIds=(tx.tags||[]).map(t=>t.id);
        calcExpression=String(Math.round(tx.amount));
        document.getElementById('tx-note-input').value=tx.note||'';
        document.getElementById('tx-datetime-input').value=`${tx.date}T${tx.time}`;
        accSel.value=tx.accountId; if(tx.transferToId) toSel.value=tx.transferToId;
        document.getElementById('tx-member-select').value=tx.memberId;
        tempLocation=tx.location;
      } else {
        modalTxType='expense'; modalCategoryId='food'; modalTagIds=[]; calcExpression='';
        document.getElementById('tx-note-input').value='';
        const now=new Date(); document.getElementById('tx-datetime-input').value=`${toLocalDateStr(now)}T${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
        tempLocation=null;
      }
      document.getElementById('tx-location-label').textContent=tempLocation?tempLocation.name:'Lokasi';
      setModalTxType(modalTxType, modalCategoryId);
      renderModalTags();
      updateCalcDisplay();
      document.getElementById('transaction-modal').classList.remove('hidden');
      lucide.createIcons();
    }

    function closeTransactionModal(){ document.getElementById('transaction-modal').classList.add('hidden'); }
    function setModalTxType(t, keepCat=null){
      modalTxType=t;
      document.getElementById('modal-type-expense').className=t==='expense'?'px-3 py-1 rounded-lg bg-rose-500 text-white':'px-3 py-1 rounded-lg text-slate-600';
      document.getElementById('modal-type-income').className=t==='income'?'px-3 py-1 rounded-lg bg-emerald-500 text-white':'px-3 py-1 rounded-lg text-slate-600';
      document.getElementById('modal-type-transfer').className=t==='transfer'?'px-3 py-1 rounded-lg bg-sky-500 text-white':'px-3 py-1 rounded-lg text-slate-600';
      document.getElementById('modal-category-wrap').classList.toggle('hidden',t==='transfer');
      document.getElementById('tx-transfer-to-wrap').classList.toggle('hidden',t!=='transfer');
      document.getElementById('tx-member-wrap').classList.toggle('hidden',t==='transfer');
      document.getElementById('lbl-acc-from').textContent=t==='transfer'?'Dari Akun':'Akun';
      const list=CATEGORIES[t]; modalCategoryId=(keepCat&&list.some(c=>c.id===keepCat))?keepCat:list[0].id;
      document.getElementById('modal-category-grid').innerHTML=list.map(c=>`
        <button type="button" onclick="modalCategoryId='${c.id}';setModalTxType('${t}','${c.id}')" class="flex flex-col items-center gap-1 p-1.5 rounded-2xl border-2 ${c.id===modalCategoryId?'border-theme bg-theme-light/60':'border-transparent'}">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:${c.bg};color:${c.color}"><i data-lucide="${c.icon}" class="w-4 h-4"></i></div>
          <span class="text-[10px] font-semibold truncate w-full text-center">${c.name}</span>
        </button>`).join('');
      lucide.createIcons();
    }

    function renderModalTags(){
      document.getElementById('tx-tags-picker').innerHTML=appState.tags.map(tg=>{
        const sel=modalTagIds.includes(Number(tg.id));
        return `<button type="button" onclick="toggleModalTag(${tg.id})" class="px-2.5 py-1 rounded-full text-[10px] font-bold border ${sel?'text-white':'bg-slate-50 text-slate-600 border-slate-200'}" style="${sel?`background:${tg.color};border-color:${tg.color}`:''}">#${tg.name}</button>`;
      }).join('');
    }
    function toggleModalTag(id){ id=Number(id); modalTagIds=modalTagIds.includes(id)?modalTagIds.filter(x=>x!==id):[...modalTagIds,id]; renderModalTags(); }

    function calcPress(v){ if(['+','-'].includes(v)){ if(!calcExpression)return; calcExpression=calcExpression.replace(/[+-]$/,'')+v; } else { if(calcExpression==='0')calcExpression=''; calcExpression+=v; } updateCalcDisplay(); }
    function calcBackspace(){ calcExpression=calcExpression.slice(0,-1); updateCalcDisplay(); }
    function calcClear(){ calcExpression=''; updateCalcDisplay(); }
    function evalCalc(){ try{ const c=calcExpression.replace(/[^0-9+-]/g,'').replace(/[+-]+$/,''); if(!c)return 0; const t=c.split(/([+-])/); let tot=parseFloat(t[0])||0; for(let i=1;i<t.length;i+=2){const n=parseFloat(t[i+1])||0; tot=t[i]==='+'?tot+n:tot-n;} return Math.max(0,Math.round(tot)); }catch{return 0;} }
    function updateCalcDisplay(){ document.getElementById('calc-display').textContent=evalCalc().toLocaleString('id-ID'); }

    async function saveTransaction(){
      const amount=evalCalc(); if(amount<=0) return showToast('Masukkan nominal lebih dari 0');
      const [date,time]=(document.getElementById('tx-datetime-input').value||'').split('T');
      const payload={
        id: editingTxId || ('tx_'+Date.now()),
        bookId: appState.activeBookId,
        type: modalTxType,
        categoryId: modalTxType==='transfer'?'transfer':modalCategoryId,
        accountId: Number(document.getElementById('tx-account-select').value),
        transferToId: modalTxType==='transfer'?Number(document.getElementById('tx-transfer-to-select').value):null,
        memberId: Number(document.getElementById('tx-member-select').value||1),
        amount, note: document.getElementById('tx-note-input').value.trim(),
        date: date||toLocalDateStr(new Date()), time: time||'12:00',
        location: tempLocation, tagIds: modalTagIds
      };
      await apiCall('save_transaction', payload);
      await loadState();
      closeTransactionModal(); refreshAllViews(); showToast('Transaksi tersimpan ke MySQL');
    }

    async function deleteEditingTransaction(){
      if(!editingTxId) return;
      await apiCall('delete_transaction',{id:editingTxId});
      await loadState(); closeTransactionModal(); refreshAllViews(); showToast('Transaksi dihapus');
    }

    // Charts
    function setChartType(t){
      chartReportType=t;
      ['expense','income','trend'].forEach(x=>{ document.getElementById(`chart-tab-${x}`).className=x===t?'py-2 rounded-xl bg-theme text-white':'py-2 rounded-xl text-slate-600'; });
      document.getElementById('chart-groupby-row').classList.toggle('hidden',t==='trend');
      document.getElementById('donut-card').classList.toggle('hidden',t==='trend');
      renderCharts();
    }
    function setChartGroupBy(g){
      chartGroupBy=g;
      ['category','member','account'].forEach(x=>{ document.getElementById(`gb-${x}`).className=x===g?'py-1.5 rounded-lg bg-theme-light text-theme':'py-1.5 rounded-lg text-slate-500'; });
      renderCharts();
    }
    function renderCharts(){
      if(chartReportType!=='trend'){
        const txs=getBookMonthTransactions().filter(t=>t.type===chartReportType);
        const total=txs.reduce((s,t)=>s+t.amount,0);
        document.getElementById('chart-total-badge').textContent=formatMoney(total);
        const agg={};
        txs.forEach(t=>{
          let key=t.categoryId, label=getCategoryInfo(t.type,t.categoryId).name, color=getCategoryInfo(t.type,t.categoryId).color;
          if(chartGroupBy==='member'){ const m=appState.members.find(x=>x.id==t.memberId); key=t.memberId; label=m?m.name:'Anggota'; color='#3b82f6'; }
          if(chartGroupBy==='account'){ const a=appState.accounts.find(x=>x.id==t.accountId); key=t.accountId; label=a?a.name:'Akun'; color='#10b981'; }
          if(!agg[key]) agg[key]={label,color,amount:0};
          agg[key].amount+=t.amount;
        });
        const sorted=Object.values(agg).sort((a,b)=>b.amount-a.amount);
        const palette=['#2563eb','#f97316','#10b981','#ec4899','#8b5cf6','#eab308','#06b6d4'];
        sorted.forEach((s,i)=>{ if(chartGroupBy!=='category') s.color=palette[i%palette.length]; });
        const canvas=document.getElementById('categoryDonutChart');
        if(donutChartInstance){ donutChartInstance.destroy(); donutChartInstance=null; }
        document.getElementById('empty-chart-msg').classList.toggle('hidden',sorted.length>0);
        canvas.classList.toggle('hidden',sorted.length===0);
        if(sorted.length){
          donutChartInstance=new Chart(canvas,{type:'doughnut',data:{labels:sorted.map(x=>x.label),datasets:[{data:sorted.map(x=>x.amount),backgroundColor:sorted.map(x=>x.color),borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:false}}}});
        }
        document.getElementById('category-breakdown-list').innerHTML=sorted.map(s=>{
          const pct=total?(s.amount/total*100).toFixed(1):0;
          return `<div class="flex justify-between items-center text-xs"><div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full" style="background:${s.color}"></span><span class="font-bold">${s.label}</span></div><span class="font-bold">${formatMoney(s.amount)} (${pct}%)</span></div>`;
        }).join('');
      }
      // Weekly Bar
      const wc=document.getElementById('weeklyBarChart');
      if(weeklyBarChartInstance) weeklyBarChartInstance.destroy();
      const lbls=[],eD=[],iD=[];
      for(let i=6;i>=0;i--){ const d=new Date(); d.setDate(d.getDate()-i); const ds=toLocalDateStr(d); lbls.push(`${d.getDate()}/${d.getMonth()+1}`); eD.push(appState.transactions.filter(t=>t.bookId==appState.activeBookId&&t.date===ds&&t.type==='expense').reduce((s,x)=>s+x.amount,0)); iD.push(appState.transactions.filter(t=>t.bookId==appState.activeBookId&&t.date===ds&&t.type==='income').reduce((s,x)=>s+x.amount,0)); }
      weeklyBarChartInstance=new Chart(wc,{type:'bar',data:{labels:lbls,datasets:[{label:'Keluar',data:eD,backgroundColor:'#f43f5e',borderRadius:6},{label:'Masuk',data:iD,backgroundColor:'#10b981',borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{display:false},x:{grid:{display:false}}}}});
    }

    let walletTypeOrder = JSON.parse(localStorage.getItem('wallet_type_order') || '[1,2,3,4,5,6,7]');
    let walletSortable = null;

    function renderWallet() {
      let assets = 0, debt = 0;
      appState.accounts.forEach(a => {
        if (a.balance >= 0) assets += a.balance;
        else debt += Math.abs(a.balance);
      });
      document.getElementById('wallet-assets').textContent = formatMoney(assets);
      document.getElementById('wallet-debt').textContent = formatMoney(debt);
      document.getElementById('wallet-net').textContent = formatMoney(assets - debt);

      const groups = {};
      appState.accounts.forEach(a => {
        const t = Number(a.account_type || 1);
        if (!groups[t]) groups[t] = [];
        groups[t].push(a);
      });

      const typesPresent = Object.keys(groups).map(Number).sort((a, b) => {
        const ia = walletTypeOrder.indexOf(a);
        const ib = walletTypeOrder.indexOf(b);
        return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
      });

      const listEl = document.getElementById('wallet-list');
      listEl.innerHTML = typesPresent.map(typeId => {
        const accs = groups[typeId];
        const catTotal = accs.reduce((s, a) => s + a.balance, 0);
        const typeName = ACCOUNT_TYPES[typeId] || 'Lainnya';

        const itemsHtml = accs.map(a => `
          <div onclick="openAccountLogs(${a.id})" class="flex items-center justify-between py-2.5 px-3 hover:bg-slate-50 rounded-xl cursor-pointer transition">
            <div>
              <p class="text-xs font-bold text-slate-800">${a.name}</p>
              <p class="text-[10px] text-slate-400">${a.currency_code}${a.note ? ' • ' + a.note : ''}</p>
            </div>
            <p class="text-xs font-extrabold ${a.balance < 0 ? 'text-rose-600' : 'text-slate-800'}">${formatMoney(a.balance)}</p>
          </div>
        `).join('');

        return `
          <div data-type-id="${typeId}" class="wallet-cat-card bg-white rounded-2xl border border-slate-100 shadow-2xs overflow-hidden">
            <div class="wallet-drag-handle px-3.5 py-2.5 bg-slate-50/90 border-b border-slate-100 flex items-center justify-between cursor-grab active:cursor-grabbing select-none touch-none">
              <div class="flex items-center gap-2">
                <i data-lucide="grip-vertical" class="w-4 h-4 text-slate-400"></i>
                <span class="text-xs font-extrabold text-slate-700">${typeName}</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-200/70 text-slate-600 font-bold">${accs.length}</span>
              </div>
              <span class="text-xs font-extrabold ${catTotal < 0 ? 'text-rose-600' : 'text-theme'}">${formatMoney(catTotal)}</span>
            </div>
            <div class="p-1.5 divide-y divide-slate-100">
              ${itemsHtml}
            </div>
          </div>
        `;
      }).join('');

      lucide.createIcons();

      if (walletSortable) walletSortable.destroy();
      walletSortable = Sortable.create(listEl, {
        animation: 220,
        easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
        handle: '.wallet-drag-handle',
        ghostClass: 'opacity-40',
        chosenClass: 'shadow-xl',
        onEnd: () => {
          const newOrder = [...listEl.querySelectorAll('.wallet-cat-card')].map(el => Number(el.dataset.typeId));
          const rest = [1, 2, 3, 4, 5, 6, 7].filter(t => !newOrder.includes(t));
          walletTypeOrder = [...newOrder, ...rest];
          localStorage.setItem('wallet_type_order', JSON.stringify(walletTypeOrder));
          apiCall('save_account_order', { order: walletTypeOrder }).catch(console.error);
        }
      });
    }

    function onWalletDragStart(e, typeId) {
      draggedType = typeId;
      e.dataTransfer.effectAllowed = 'move';
      e.currentTarget.classList.add('opacity-60');
    }

    function onWalletDragOver(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
    }

    async function onWalletDrop(e, targetTypeId) {
      e.preventDefault();
      if (draggedType === null || draggedType === targetTypeId) return;

      const allTypes = [...new Set([...walletTypeOrder, 1, 2, 3, 4, 5, 6, 7])];
      const fromIdx = allTypes.indexOf(draggedType);
      const toIdx = allTypes.indexOf(targetTypeId);
      if (fromIdx === -1 || toIdx === -1) return;

      allTypes.splice(fromIdx, 1);
      allTypes.splice(toIdx, 0, draggedType);
      walletTypeOrder = allTypes;
      localStorage.setItem('wallet_type_order', JSON.stringify(walletTypeOrder));

      draggedType = null;
      renderWallet();
      apiCall('save_account_order', { order: walletTypeOrder }).catch(console.error);
      showToast('Urutan kategori dompet disimpan');
    }
    async function openAccountLogs(id) {
      const acc = appState.accounts.find(a => a.id == id);
      const d = await apiCall(`account_logs&id=${id}`);
      document.getElementById('generic-modal-body').innerHTML = `
        <div class="flex justify-between items-center mb-3">
          <h4 class="text-sm font-bold">${acc.name}</h4>
          <div class="flex items-center gap-1.5">
            <button onclick="deleteAccount(${acc.id})" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 text-[11px] font-bold">Hapus</button>
            <button onclick="closeGenericModal()" class="p-1 rounded-full bg-slate-100">✕</button>
          </div>
        </div>
        <div class="space-y-2">
          ${d.logs.map(l => `<div class="flex justify-between text-xs border-b border-slate-100 pb-1.5"><span>${l.content}</span><b class="${l.change_amount >= 0 ? 'text-emerald-600' : 'text-rose-600'}">${formatMoney(l.change_amount)}</b></div>`).join('') || '<p class="text-xs text-slate-400">Belum ada riwayat saldo.</p>'}
        </div>`;
      document.getElementById('generic-modal').classList.remove('hidden');
    }

    async function deleteAccount(id) {
      if (!confirm('Hapus akun ini dari daftar dompet?')) return;
      await apiCall('delete_account', { id });
      closeGenericModal();
      await loadState();
      renderWallet();
      showToast('Akun dihapus');
    }
    function openNewAccountModal(){
      document.getElementById('generic-modal-body').innerHTML=`
        <div class="flex justify-between items-center mb-3"><h4 class="text-sm font-bold">Akun Baru</h4><button onclick="closeGenericModal()" class="p-1 rounded-full bg-slate-100">✕</button></div>
        <div class="space-y-2.5">
          <input id="na-name" placeholder="Nama Akun" class="w-full px-3 py-2 text-xs bg-slate-100 rounded-xl" />
          <select id="na-type" class="w-full px-3 py-2 text-xs bg-slate-100 rounded-xl"><option value="1">Tunai</option><option value="2">Kartu Debit</option><option value="3">Kartu Deposit</option><option value="4">Kartu Kredit</option><option value="5">Akun Virtual</option><option value="6">Berinvestasi</option></select>
          <input id="na-bal" type="number" value="0" placeholder="Saldo Awal" class="w-full px-3 py-2 text-xs bg-slate-100 rounded-xl" />
          <button onclick="submitNewAccount()" class="w-full py-2.5 rounded-xl gradient-theme text-white text-xs font-bold">Simpan</button>
        </div>`;
      document.getElementById('generic-modal').classList.remove('hidden');
    }
    async function submitNewAccount(){
      await apiCall('save_account',{name:document.getElementById('na-name').value,account_type:document.getElementById('na-type').value,balance:document.getElementById('na-bal').value});
      closeGenericModal(); await loadState(); renderWallet();
    }

    // More Panels (Kalender, Anggaran, Tujuan Tabungan, Tag, Setelan)
    function showMoreSub(sub){
      ['calendar','budgets','goals','tags','settings'].forEach(s=>document.getElementById(`more-sub-${s}`).classList.toggle('hidden',s!==sub));
      if(sub==='calendar') renderCalendarView();
      if(sub==='budgets') renderBudgets();
      if(sub==='goals') renderGoals();
      if(sub==='tags') renderTagsManager();
    }
    function renderBudgets(){
      const spent=getBookMonthTransactions().filter(t=>t.type==='expense').reduce((s,t)=>s+t.amount,0);
      const list=appState.budgets.filter(b=>b.book_id==appState.activeBookId);
      document.getElementById('more-sub-budgets').innerHTML=`
        <div class="flex justify-between items-center"><h3 class="text-xs font-bold uppercase text-slate-600">Anggaran</h3><button onclick="openNewBudgetModal()" class="px-3 py-1 rounded-full bg-theme text-white text-[11px] font-bold">+ Buat</button></div>
        ${list.map(b=>{
          const catSpent=b.category_id?getBookMonthTransactions().filter(t=>t.type==='expense'&&t.categoryId===b.category_id).reduce((s,t)=>s+t.amount,0):spent;
          const pct=Math.min(100,(catSpent/Number(b.amount||1)*100)).toFixed(1);
          return `<div class="bg-white p-3.5 rounded-2xl border border-slate-100"><div class="flex justify-between text-xs font-bold mb-1.5"><span>${b.name}</span><span>${formatMoney(catSpent)} / ${formatMoney(b.amount)}</span></div><div class="h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-theme" style="width:${pct}%"></div></div></div>`;
        }).join('')||'<p class="text-xs text-slate-400">Belum ada anggaran.</p>'}`;
    }
    function openNewBudgetModal(){
      document.getElementById('generic-modal-body').innerHTML=`<div class="flex justify-between mb-3"><h4 class="text-sm font-bold">Anggaran Baru</h4><button onclick="closeGenericModal()">✕</button></div><div class="space-y-2.5"><input id="nb-name" placeholder="Nama Anggaran" class="w-full px-3 py-2 text-xs bg-slate-100 rounded-xl"/><input id="nb-amt" type="number" placeholder="Batas Nominal" class="w-full px-3 py-2 text-xs bg-slate-100 rounded-xl"/><button onclick="submitBudget()" class="w-full py-2.5 rounded-xl gradient-theme text-white text-xs font-bold">Simpan</button></div>`;
      document.getElementById('generic-modal').classList.remove('hidden');
    }
    async function submitBudget(){ await apiCall('save_budget',{bookId:appState.activeBookId,name:document.getElementById('nb-name').value,amount:document.getElementById('nb-amt').value}); closeGenericModal(); await loadState(); renderBudgets(); }

    function renderGoals(){
      const list=appState.goals.filter(g=>g.book_id==appState.activeBookId);
      document.getElementById('more-sub-goals').innerHTML=`
        <div class="flex justify-between items-center"><h3 class="text-xs font-bold uppercase text-slate-600">Tujuan Tabungan</h3><button onclick="openNewGoalModal()" class="px-3 py-1 rounded-full bg-theme text-white text-[11px] font-bold">+ Buat</button></div>
        ${list.map(g=>{
          const pct=Math.min(100,(Number(g.saved_amount)/Number(g.target_amount||1)*100)).toFixed(1);
          return `<div class="bg-white p-3.5 rounded-2xl border border-slate-100"><div class="flex justify-between items-center text-xs font-bold mb-1"><span>${g.name} (${pct}%)</span><button onclick="depositGoal(${g.id})" class="px-2.5 py-1 rounded-full bg-emerald-500 text-white text-[10px]">+ Setor</button></div><p class="text-[11px] text-slate-400 mb-1.5">${formatMoney(g.saved_amount)} / ${formatMoney(g.target_amount)}</p><div class="h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-emerald-500" style="width:${pct}%"></div></div></div>`;
        }).join('')||'<p class="text-xs text-slate-400">Belum ada tujuan tabungan.</p>'}`;
    }
    function openNewGoalModal(){
      document.getElementById('generic-modal-body').innerHTML=`<div class="flex justify-between mb-3"><h4 class="text-sm font-bold">Tujuan Tabungan</h4><button onclick="closeGenericModal()">✕</button></div><div class="space-y-2.5"><input id="ng-name" placeholder="Nama Tujuan" class="w-full px-3 py-2 text-xs bg-slate-100 rounded-xl"/><input id="ng-amt" type="number" placeholder="Target Nominal" class="w-full px-3 py-2 text-xs bg-slate-100 rounded-xl"/><button onclick="submitGoal()" class="w-full py-2.5 rounded-xl gradient-theme text-white text-xs font-bold">Simpan</button></div>`;
      document.getElementById('generic-modal').classList.remove('hidden');
    }
    async function submitGoal(){ await apiCall('save_goal',{bookId:appState.activeBookId,name:document.getElementById('ng-name').value,target_amount:document.getElementById('ng-amt').value}); closeGenericModal(); await loadState(); renderGoals(); }
    async function depositGoal(id){ const v=prompt('Jumlah setoran (Rp):','100000'); if(!v||isNaN(Number(v))) return; await apiCall('deposit_goal',{goal_id:id,amount:Number(v)}); await loadState(); renderGoals(); }

    function renderTagsManager(){
      document.getElementById('more-sub-tags').innerHTML=`
        <div class="flex justify-between items-center"><h3 class="text-xs font-bold uppercase text-slate-600">Kelola Tag</h3><button onclick="openNewTagModal()" class="px-3 py-1 rounded-full bg-theme text-white text-[11px] font-bold">+ Tag</button></div>
        <div class="bg-white p-3.5 rounded-2xl border border-slate-100 flex flex-wrap gap-2">
          ${appState.tags.map(t=>`<span onclick="document.getElementById('search-tx-input').value='#${t.name}';switchTab('home');renderTransactionList();" class="px-3 py-1 rounded-full text-xs text-white font-bold cursor-pointer" style="background:${t.color}">#${t.name}</span>`).join('')}
        </div>`;
    }
    function openNewTagModal(){
      document.getElementById('generic-modal-body').innerHTML=`<div class="flex justify-between mb-3"><h4 class="text-sm font-bold">Tag Baru</h4><button onclick="closeGenericModal()">✕</button></div><div class="space-y-2.5"><input id="nt-name" placeholder="Nama Tag" class="w-full px-3 py-2 text-xs bg-slate-100 rounded-xl"/><input id="nt-col" type="color" value="#2563eb" class="w-full h-9 rounded-xl"/><button onclick="submitTag()" class="w-full py-2.5 rounded-xl gradient-theme text-white text-xs font-bold">Simpan</button></div>`;
      document.getElementById('generic-modal').classList.remove('hidden');
    }
    async function submitTag(){ await apiCall('save_tag',{bookId:appState.activeBookId,name:document.getElementById('nt-name').value,color:document.getElementById('nt-col').value}); closeGenericModal(); await loadState(); renderTagsManager(); }

    function openBookPickerModal(){
      document.getElementById('generic-modal-body').innerHTML=`
        <div class="flex justify-between items-center mb-3"><h4 class="text-sm font-bold">Buku Saya</h4><button onclick="closeGenericModal()">✕</button></div>
        <div class="space-y-2 mb-3">${appState.books.map(b=>`<div onclick="appState.activeBookId=${b.id};document.getElementById('active-book-name').textContent='${b.name}';saveSettings();closeGenericModal();refreshAllViews();" class="p-3 rounded-xl border ${b.id==appState.activeBookId?'border-theme bg-theme-light':'border-slate-100'} cursor-pointer text-xs font-bold">${b.name}</div>`).join('')}</div>
        <div class="flex gap-2"><input id="new-book-name" placeholder="Nama buku baru..." class="flex-1 px-3 py-2 text-xs bg-slate-100 rounded-xl"/><button onclick="submitBook()" class="px-3 py-2 rounded-xl gradient-theme text-white text-xs font-bold">+ Tambah</button></div>`;
      document.getElementById('generic-modal').classList.remove('hidden');
    }
    async function submitBook(){ const n=document.getElementById('new-book-name').value.trim(); if(!n)return; await apiCall('save_book',{name:n}); await loadState(); openBookPickerModal(); }
    function closeGenericModal(){ document.getElementById('generic-modal').classList.add('hidden'); }

    // Calendar
function fmtCalShort(n) {
      if (n >= 1000000) {
        return (n / 1000000).toFixed(1).replace('.0', '').replace('.', ',') + 'jt';
      }
      if (n >= 1000) {
        return (n / 1000).toFixed(1).replace('.0', '').replace('.', ',') + 'rb';
      }
      return String(Math.round(n));
    }

    function renderCalendarView() {
      const grid = document.getElementById('calendar-grid');
      const firstDay = new Date(selectedYear, selectedMonth, 1).getDay();
      const days = new Date(selectedYear, selectedMonth + 1, 0).getDate();
      const monthTxs = getBookMonthTransactions();
      let html = '';
      for (let i = 0; i < firstDay; i++) html += '<div></div>';

      for (let d = 1; d <= days; d++) {
        const ds = `${selectedYear}-${String(selectedMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        const items = monthTxs.filter(t => t.date === ds);
        const totExp = items.filter(x => x.type === 'expense').reduce((s, x) => s + x.amount, 0);
        const totInc = items.filter(x => x.type === 'income').reduce((s, x) => s + x.amount, 0);
        const isSel = selectedCalendarDate === ds;

        html += `
          <button onclick="selectedCalendarDate='${ds}';renderCalendarView();" class="py-1.5 px-0.5 rounded-xl flex flex-col items-center justify-start min-h-[52px] ${isSel ? 'bg-theme text-white font-bold' : 'hover:bg-slate-100'}">
            <span class="text-xs">${d}</span>
            <div class="flex gap-0.5 mt-0.5 h-1">
              ${totExp > 0 ? `<span class="w-1 h-1 rounded-full ${isSel ? 'bg-rose-200' : 'bg-rose-500'}"></span>` : ''}
              ${totInc > 0 ? `<span class="w-1 h-1 rounded-full ${isSel ? 'bg-emerald-200' : 'bg-emerald-500'}"></span>` : ''}
            </div>
            <div class="mt-0.5 text-[8px] leading-tight font-bold w-full truncate">
              ${totInc > 0 ? `<div class="${isSel ? 'text-emerald-200' : 'text-emerald-600'}">+${fmtCalShort(totInc)}</div>` : ''}
              ${totExp > 0 ? `<div class="${isSel ? 'text-rose-200' : 'text-rose-600'}">-${fmtCalShort(totExp)}</div>` : ''}
            </div>
          </button>`;
      }
      grid.innerHTML = html;

      const sel = monthTxs.filter(t => t.date === selectedCalendarDate);
      document.getElementById('calendar-selected-details').innerHTML = sel.length
        ? sel.map(t => `<div class="bg-white p-3 rounded-2xl border border-slate-100 flex justify-between text-xs"><span>${getCategoryInfo(t.type, t.categoryId).name} — ${t.note || ''}</span><b class="${t.type === 'expense' ? 'text-rose-600' : 'text-emerald-600'}">${formatMoney(t.amount)}</b></div>`).join('')
        : `<p class="text-center text-xs text-slate-400 py-3">Tidak ada transaksi pada ${selectedCalendarDate}</p>`;
    }

    // Security & Misc
    function setCurrency(c){ appState.currency=c; saveSettings(); refreshAllViews(); }
    function handleToggleSecurity(cb){ appState.securityEnabled=cb.checked; saveSettings(); document.getElementById('security-actions-row').classList.toggle('hidden',!cb.checked); }
    function openSetPinModal(){ document.getElementById('set-pin-modal').classList.remove('hidden'); }
    function saveNewPin(){ const v=document.getElementById('new-pin-input').value.trim(); if(!/^\d{4}$/.test(v)) return showToast('PIN harus 4 digit'); appState.pinCode=v; saveSettings(); document.getElementById('set-pin-modal').classList.add('hidden'); showToast('PIN diperbarui'); }
    function lockAppNow(){ currentLockPinInput=''; updateDots(); document.getElementById('lock-screen-overlay').classList.remove('hidden'); }
    function enterLockPin(d){ if(currentLockPinInput.length>=4)return; currentLockPinInput+=d; updateDots(); if(currentLockPinInput.length===4){ setTimeout(()=>{ if(currentLockPinInput===appState.pinCode) document.getElementById('lock-screen-overlay').classList.add('hidden'); else{ showToast('PIN salah'); currentLockPinInput=''; updateDots(); } },150); } }
    function backspaceLockPin(){ currentLockPinInput=currentLockPinInput.slice(0,-1); updateDots(); }
    function updateDots(){ const dots=document.getElementById('pin-dots-container').children; for(let i=0;i<4;i++) dots[i].classList.toggle('bg-white',i<currentLockPinInput.length); }
    function simulateFingerprintUnlock(){ document.getElementById('lock-screen-overlay').classList.add('hidden'); showToast('Sidik jari terverifikasi'); }
    function openThemeQuickModal(){ renderThemeSelectors(); document.getElementById('theme-quick-modal').classList.remove('hidden'); }
    function closeThemeQuickModal(){ document.getElementById('theme-quick-modal').classList.add('hidden'); }
    function openMapPickerModal(){ document.getElementById('map-picker-modal').classList.remove('hidden'); }
    function closeMapPickerModal(){ document.getElementById('map-picker-modal').classList.add('hidden'); }
    function handleMapClick(e){ const r=e.currentTarget.getBoundingClientRect(), x=((e.clientX-r.left)/r.width*100), y=((e.clientY-r.top)/r.height*100); document.getElementById('map-pin-marker').style.left=`${x}%`; document.getElementById('map-pin-marker').style.top=`${y}%`; document.getElementById('map-coords-text').textContent=`${(-6.15-y/100*0.18).toFixed(4)}, ${(106.6+x/100*0.25).toFixed(4)}`; }
    function confirmMapLocation(){ tempLocation={name:document.getElementById('map-place-name').value,coords:document.getElementById('map-coords-text').textContent}; document.getElementById('tx-location-label').textContent=tempLocation.name; closeMapPickerModal(); }
    function clearTransactionLocation(){ tempLocation=null; document.getElementById('tx-location-label').textContent='Lokasi'; closeMapPickerModal(); }
    function exportDataJSON(){ const b=new Blob([JSON.stringify(appState,null,2)],{type:'application/json'}); const a=document.createElement('a'); a.href=URL.createObjectURL(b); a.download=`moneytracker_${toLocalDateStr(new Date())}.json`; a.click(); }
    async function clearAllFromZero(){ if(!confirm('Kosongkan semua transaksi & jadikan saldo semua akun 0?')) return; await apiCall('clear_all',{}); await loadState(); refreshAllViews(); showToast('Semua data direset ke 0'); }


    let walletSub='accounts', invFilter='all';
    const INV_TYPE_META = {
      stock: { label:'Saham', icon:'trending-up', color:'#2563eb', bg:'#dbeafe', defaultUnit:'lembar' },
      gold: { label:'Emas', icon:'gem', color:'#d97706', bg:'#fef3c7', defaultUnit:'gram' },
      crypto: { label:'Kripto', icon:'bitcoin', color:'#7c3aed', bg:'#ede9fe', defaultUnit:'koin' },
      mutual_fund: { label:'Reksadana', icon:'layers', color:'#059669', bg:'#d1fae5', defaultUnit:'unit' },
      other: { label:'Lainnya', icon:'briefcase', color:'#64748b', bg:'#f1f5f9', defaultUnit:'unit' }
    };

    function setWalletSub(sub){
      walletSub=sub;
      document.getElementById('wtab-accounts').className=sub==='accounts'?'py-2 rounded-xl bg-theme text-white':'py-2 rounded-xl text-slate-600';
      document.getElementById('wtab-invest').className=sub==='invest'?'py-2 rounded-xl bg-theme text-white':'py-2 rounded-xl text-slate-600';
      document.getElementById('wallet-sub-accounts').classList.toggle('hidden',sub!=='accounts');
      document.getElementById('wallet-sub-invest').classList.toggle('hidden',sub!=='invest');
      if(sub==='invest') renderInvestments();
      lucide.createIcons();
    }

    function setInvFilter(f){
      invFilter=f;
      ['all','stock','gold','crypto','mutual_fund','other'].forEach(x=>{
        document.getElementById(`if-${x}`).className=x===f?'px-2.5 py-1 rounded-full bg-theme text-white shrink-0':'px-2.5 py-1 rounded-full bg-white text-slate-600 shrink-0';
      });
      renderInvestments();
    }

    function renderInvestments(){
      const all=appState.investments.filter(i=>i.book_id==appState.activeBookId);
      let totalCost=0, totalVal=0;
      all.forEach(i=>{ totalCost+=i.invested_amount; totalVal+=(i.quantity*i.current_price_per_unit); });
      const pl=totalVal-totalCost, plPct=totalCost>0?(pl/totalCost*100).toFixed(2):'0.00';

      document.getElementById('inv-total-value').textContent=formatMoney(totalVal);
      document.getElementById('inv-total-cost').textContent=formatMoney(totalCost);
      const plEl=document.getElementById('inv-total-pl');
      plEl.textContent=`${pl>=0?'+':''}${formatMoney(pl)} (${pl>=0?'+':''}${plPct}%)`;
      plEl.className=`text-xs font-bold ${pl>=0?'text-emerald-600':'text-rose-600'}`;

      const list=invFilter==='all'?all:all.filter(i=>i.asset_type===invFilter);
      const wrap=document.getElementById('investment-list');
      if(!list.length){ wrap.innerHTML=`<div class="bg-white p-6 rounded-2xl text-center text-xs text-slate-400 border border-slate-100">Belum ada aset investasi. Ketuk <b>+ Aset</b> untuk menambahkan saham, emas, atau lainnya.</div>`; return; }

      wrap.innerHTML=list.map(i=>{
        const m=INV_TYPE_META[i.asset_type]||INV_TYPE_META.other;
        const curVal=i.quantity*i.current_price_per_unit;
        const itemPl=curVal-i.invested_amount;
        const itemPct=i.invested_amount>0?(itemPl/i.invested_amount*100).toFixed(2):'0.00';
        return `
          <div onclick="openInvestmentModal(${i.id})" class="bg-white rounded-2xl p-3.5 border border-slate-100 space-y-2 cursor-pointer hover:bg-slate-50">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:${m.bg};color:${m.color}"><i data-lucide="${m.icon}" class="w-4 h-4"></i></div>
                <div>
                  <p class="text-xs font-bold text-slate-800">${i.name} ${i.ticker?`<span class="text-[10px] text-slate-400">(${i.ticker})</span>`:''}</p>
                  <p class="text-[10px] text-slate-500">${m.label} • <b>${Number(i.quantity).toLocaleString('id-ID',{maximumFractionDigits:4})} ${i.unit_label}</b></p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-xs font-extrabold text-slate-800">${formatMoney(curVal)}</p>
                <p class="text-[10px] font-bold ${itemPl>=0?'text-emerald-600':'text-rose-600'}">${itemPl>=0?'+':''}${formatMoney(itemPl)} (${itemPl>=0?'+':''}${itemPct}%)</p>
              </div>
            </div>
            <div class="flex justify-between text-[10px] text-slate-400 pt-1.5 border-t border-slate-100">
              <span>Modal: <b>${formatMoney(i.invested_amount)}</b></span>
              <span>Harga/unit: <b>${formatMoney(i.current_price_per_unit)}/${i.unit_label}</b></span>
            </div>
          </div>`;
      }).join('');
      lucide.createIcons();
    }

    async function refreshLivePrices(){
      const btn=document.getElementById('btn-live-price');
      btn.innerHTML='⏳ Memperbarui...';
      try{
        const res=await apiCall('fetch_live_prices');
        await loadState();
        renderInvestments();
        showToast(`Harga realtime diperbarui (${res.updated} aset)`);
      }catch(e){
        showToast('Gagal mengambil harga realtime dari server');
      }finally{
        btn.innerHTML='<i data-lucide="refresh-cw" class="w-3 h-3"></i> Harga Realtime';
        lucide.createIcons();
      }
    }

    function openInvestmentModal(editId=null){
      const inv=editId?appState.investments.find(x=>x.id==editId):null;
      document.getElementById('generic-modal-body').innerHTML=`
        <div class="flex justify-between items-center mb-3">
          <h4 class="text-sm font-bold">${inv?'Edit Investasi':'Tambah Investasi'}</h4>
          <button onclick="closeGenericModal()" class="p-1 rounded-full bg-slate-100">✕</button>
        </div>
        <div class="space-y-2.5 text-xs">
          <select id="ni-type" onchange="syncInvUnitPlaceholder()" class="w-full px-3 py-2 bg-slate-100 rounded-xl font-semibold">
            <option value="stock" ${inv?.asset_type==='stock'?'selected':''}>Saham (cth: BBCA.JK, AAPL)</option>
            <option value="gold" ${inv?.asset_type==='gold'?'selected':''}>Emas (Gram)</option>
            <option value="crypto" ${inv?.asset_type==='crypto'?'selected':''}>Kripto (cth: BTC, ETH)</option>
            <option value="mutual_fund" ${inv?.asset_type==='mutual_fund'?'selected':''}>Reksadana</option>
            <option value="other" ${inv?.asset_type==='other'?'selected':''}>Investasi Lainnya</option>
          </select>
          <input id="ni-name" value="${inv?.name||''}" placeholder="Nama Aset (cth: Bank BCA / Emas Antam)" class="w-full px-3 py-2 bg-slate-100 rounded-xl" />
          <input id="ni-ticker" value="${inv?.ticker||''}" placeholder="Kode Ticker (cth: BBCA.JK, XAU, BTC)" class="w-full px-3 py-2 bg-slate-100 rounded-xl" />
          <div class="grid grid-cols-2 gap-2">
            <input id="ni-qty" type="number" step="any" value="${inv?.quantity||''}" placeholder="Jumlah (lembar/gram)" class="px-3 py-2 bg-slate-100 rounded-xl" />
            <input id="ni-unit" value="${inv?.unit_label||'lembar'}" placeholder="Satuan" class="px-3 py-2 bg-slate-100 rounded-xl" />
          </div>
          <input id="ni-invested" type="number" value="${inv?.invested_amount||''}" placeholder="Total Modal Diinvestasikan (Rp)" class="w-full px-3 py-2 bg-slate-100 rounded-xl" />
          <input id="ni-curprice" type="number" value="${inv?.current_price_per_unit||''}" placeholder="Harga Saat Ini per Unit (opsional)" class="w-full px-3 py-2 bg-slate-100 rounded-xl" />
          <div class="flex gap-2 pt-1">
            ${inv?`<button onclick="deleteInvestment(${inv.id})" class="px-4 py-2.5 rounded-xl bg-rose-50 text-rose-600 font-bold">Hapus</button>`:''}
            <button onclick="submitInvestment(${inv?.id||'null'})" class="flex-1 py-2.5 rounded-xl gradient-theme text-white font-bold">Simpan</button>
          </div>
        </div>`;
      document.getElementById('generic-modal').classList.remove('hidden');
    }

    function syncInvUnitPlaceholder(){
      const t=document.getElementById('ni-type').value;
      document.getElementById('ni-unit').value=(INV_TYPE_META[t]||INV_TYPE_META.other).defaultUnit;
      if(t==='gold' && !document.getElementById('ni-ticker').value) document.getElementById('ni-ticker').value='XAU';
    }

    async function submitInvestment(id){
      await apiCall('save_investment',{
        id, bookId:appState.activeBookId,
        asset_type:document.getElementById('ni-type').value,
        name:document.getElementById('ni-name').value.trim(),
        ticker:document.getElementById('ni-ticker').value.trim(),
        quantity:Number(document.getElementById('ni-qty').value||0),
        unit_label:document.getElementById('ni-unit').value.trim()||'unit',
        invested_amount:Number(document.getElementById('ni-invested').value||0),
        current_price_per_unit:Number(document.getElementById('ni-curprice').value||0)
      });
      closeGenericModal(); await loadState(); renderInvestments(); showToast('Investasi tersimpan');
    }

    async function deleteInvestment(id){
      await apiCall('delete_investment',{id});
      closeGenericModal(); await loadState(); renderInvestments(); showToast('Aset dihapus');
    }

    function refreshAllViews(){ renderSummariesAndWidget(); renderTransactionList(); if(currentTab==='charts') renderCharts(); if(currentTab==='wallet') renderWallet(); }

    window.addEventListener('DOMContentLoaded', async () => {
      await loadState();
      document.getElementById('currency-select').value=appState.currency;
      document.getElementById('toggle-security-lock').checked=appState.securityEnabled;
      document.getElementById('security-actions-row').classList.toggle('hidden',!appState.securityEnabled);
      applyTheme(appState.theme);
      setWidgetStyle(appState.widgetStyle);
      refreshAllViews();
      if(appState.securityEnabled) lockAppNow();
      lucide.createIcons();
    });
  </script>
  <script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js');
  }
</script>
</body>
</html>
