<?php 
include 'header.php'; 
$messages = $pdo->query("SELECT * FROM messages ORDER BY id DESC")->fetchAll();
?>

<div class="bg-white p-6 rounded-2xl shadow-sm border">
    <h2 class="text-xl font-bold text-slate-800 mb-4">Inbox Messages (<?php echo count($messages); ?>)</h2>
    <div class="space-y-4">
        <?php if(empty($messages)): ?>
            <p class="text-slate-400 text-sm">কোন মেসেজ নেই।</p>
        <?php endif; ?>
        <?php foreach($messages as $m): ?>
        <div class="p-4 border rounded-xl bg-slate-50 flex justify-between items-start">
            <div>
                <h4 class="font-bold text-slate-800"><?php echo $m['name']; ?> <span class="text-xs text-slate-400 font-normal ml-2"><?php echo $m['timestamp']; ?></span></h4>
                <p class="text-xs text-slate-500 font-mono mt-0.5">Phone: <?php echo $m['phone']; ?> | Email: <?php echo $m['email']; ?></p>
                <p class="text-sm text-slate-700 mt-2 bg-white p-3 rounded-lg border"><?php echo nl2br(htmlspecialchars($m['message'])); ?></p>
            </div>
            <button onclick="delMsg(<?php echo $m['id']; ?>)" class="text-red-500 hover:text-red-700 ml-4"><i class="ri-delete-bin-line text-lg"></i></button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    async function delMsg(id) {
        if(confirm("Delete this message?")) {
            await fetch('admin_api.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ action: 'delete_message', id })
            });
            location.reload();
        }
    }
</script>

<?php include 'footer.php'; ?>