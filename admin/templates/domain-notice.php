<?php
// בדיקה אם הדומיין לא מוגדר ב-hosts
$store = $store ?? null;
if ($store && isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false && !isset($_COOKIE['domain_notice_dismissed'])) {
    $storeUrl = $store['slug'] . '.localhost:8000';
?>
<div id="domain-notice" class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i class="ri-information-line text-blue-400 text-xl"></i>
        </div>
        <div class="mr-3 flex-1">
            <h3 class="text-sm font-medium text-blue-800">צריך להגדיר את הדומיין של החנות</h3>
            <div class="mt-2 text-sm text-blue-700">
                <p class="mb-2">כדי לצפות בחנות שלך, הוסף את השורה הזו לקובץ <code class="bg-blue-100 px-1 rounded">/etc/hosts</code>:</p>
                <div class="bg-blue-100 p-2 rounded font-mono text-xs mb-2">
                    127.0.0.1 <?= htmlspecialchars($storeUrl) ?>
                </div>
                <p class="text-xs">פתח טרמינל והרץ: <code class="bg-blue-100 px-1 rounded">sudo nano /etc/hosts</code></p>
            </div>
        </div>
        <div class="flex-shrink-0">
            <button onclick="dismissDomainNotice()" class="text-blue-400 hover:text-blue-600">
                <i class="ri-close-line"></i>
            </button>
        </div>
    </div>
</div>

<script>
function dismissDomainNotice() {
    document.getElementById('domain-notice').style.display = 'none';
    document.cookie = 'domain_notice_dismissed=1; expires=' + new Date(Date.now() + 7*24*60*60*1000).toUTCString() + '; path=/';
}
</script>
<?php } ?> 