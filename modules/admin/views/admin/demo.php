<div class="space-y-6">
  <div class="glass-panel rounded-2xl p-6">
    <h1 class="text-xl font-black text-slate-900 dark:text-white">داده نمونه</h1>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
      برای تست واقعی سیستم، یک سناریوی کامل و به‌هم‌پیوسته می‌سازد: انبار و کالا با موجودی اول دوره، فرمول و دستور تولید برنامه‌ریزی‌شده، دسته و محصول فروشگاه، دو سفارش (یکی پرداخت‌نشده، یکی پرداخت‌شده) و حساب‌ها با تراکنش و انتقال. همه ردیف‌ها «(نمونه)» دارند و شناسه‌شان ثبت می‌شود، پس پاک‌سازی دقیقاً همان‌ها را حذف می‌کند و به داده واقعی شما دست نمی‌زند.
    </p>
  </div>

  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

  <?php if ($reg !== null): ?>
    <div class="glass-panel rounded-2xl p-6">
      <h3 class="text-base font-bold text-emerald-500 dark:text-emerald-400 mb-4">داده نمونه بارگذاری شده است</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
        <div class="p-3 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800"><div class="font-black text-slate-900 dark:text-white text-sm"><?= count($reg['warehouses'] ?? []) + count($reg['inv_items'] ?? []) ?></div><div class="text-slate-500 dark:text-slate-400 mt-0.5">انبار و کالا</div></div>
        <div class="p-3 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800"><div class="font-black text-slate-900 dark:text-white text-sm"><?= count($reg['recipes'] ?? []) + count($reg['prod_orders'] ?? []) ?></div><div class="text-slate-500 dark:text-slate-400 mt-0.5">فرمول و دستور تولید</div></div>
        <div class="p-3 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800"><div class="font-black text-slate-900 dark:text-white text-sm"><?= count($reg['shop_categories'] ?? []) + count($reg['shop_products'] ?? []) + count($reg['shop_orders'] ?? []) ?></div><div class="text-slate-500 dark:text-slate-400 mt-0.5">دسته، محصول و سفارش</div></div>
        <div class="p-3 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800"><div class="font-black text-slate-900 dark:text-white text-sm"><?= count($reg['acc_accounts'] ?? []) + count($reg['acc_transactions'] ?? []) + count($reg['acc_transfers'] ?? []) ?></div><div class="text-slate-500 dark:text-slate-400 mt-0.5">حساب، تراکنش و انتقال</div></div>
      </div>
      <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-4">بارگذاری‌شده در: <?= e($reg['loaded_at'] ?? '—') ?> — پیشنهاد تست: دستور تولید را شروع و تکمیل کنید، سفارش پرداخت‌شده را از حسابداری ایمپورت کنید، بعد پاک‌سازی را بزنید و ببینید همه نمونه‌ها رفته‌اند.</p>
      <form method="post" action="/admin/demo/clean" onsubmit="return confirm('همه ردیف‌های نمونه حذف شوند؟ داده واقعی شما دست نمی‌خورد.')" class="mt-4">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-500 text-white text-xs font-bold hover:bg-rose-600">پاک‌سازی داده نمونه</button>
      </form>
    </div>
  <?php else: ?>
    <div class="glass-panel rounded-2xl p-6">
      <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">چه چیزی ساخته می‌شود؟</h3>
      <ul class="text-xs text-slate-600 dark:text-slate-300 leading-loose list-disc pr-5">
        <li><strong>انبار:</strong> «انبار مرکزی (نمونه)» با ۴ ماده اولیه (پروفیل، ریسه، درایور، درپوش) و موجودی اول دوره، به‌علاوه کالای خروجی تولید</li>
        <li><strong>تولید:</strong> فرمول «چراغ خطی ۱ متری» و دستور تولید ۱۰ عددی در وضعیت برنامه‌ریزی‌شده (شروع و تکمیلش با شماست؛ موجودی مواد کافی است)</li>
        <li><strong>فروشگاه:</strong> دسته «روشنایی (نمونه)» با ۳ محصول و موجودی، یک سفارش پرداخت‌نشده و یک سفارش پرداخت‌شده (موجودی فروشگاه مثل تسویه واقعی کم می‌شود)</li>
        <li><strong>حسابداری:</strong> «صندوق نمونه» و «بانک نمونه» با موجودی اول دوره، یک فروش، یک خرید مواد و یک انتقال بین‌شان</li>
      </ul>
      <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-4">اگر وسط تست روی ردیف نمونه داده واقعی سوار شود (مثلاً موجودی واقعی به انبار نمونه برود)، پاک‌سازی آن انبار یا حساب را نگه می‌دارد و دلیلش را می‌گوید؛ بقیه نمونه‌ها حذف می‌شوند.</p>
      <form method="post" action="/admin/demo/load" class="mt-4">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button type="submit" class="btn green">بارگذاری داده نمونه</button>
      </form>
    </div>
  <?php endif; ?>
</div>
