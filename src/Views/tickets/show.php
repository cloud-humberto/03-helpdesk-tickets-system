<?php
$pageTitle = "Ticket #{$ticket['id']} — {$ticket['title']}";
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="ticket-view-header">
    <a href="/tickets" class="btn-back">&larr; Back to Ticket Queue</a>
    
    <div class="ticket-status-controls">
        <?php if ($currentUser['role'] === 'admin'): ?>
            <!-- Technician Status Control -->
            <form action="/tickets/<?= $ticket['id'] ?>/status" method="POST" class="status-form">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <span class="label-status-change">Change Status:</span>
                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </form>
        <?php else: ?>
            <span class="badge-status status-<?= $ticket['status'] ?>">
                Status: <?= str_replace('_', ' ', ucfirst($ticket['status'])) ?>
            </span>
        <?php endif; ?>
    </div>
</div>

<div class="ticket-grid">
    <div class="ticket-main-col">
        <!-- Ticket Information Card -->
        <div class="card p-4 mb-4">
            <div class="ticket-heading-row">
                <span class="font-mono text-muted">#<?= str_pad((string)$ticket['id'], 4, '0', STR_PAD_LEFT) ?></span>
                <span class="badge-priority priority-<?= $ticket['priority'] ?>">Priority: <?= ucfirst($ticket['priority']) ?></span>
            </div>
            <h1 class="ticket-title mt-2"><?= htmlspecialchars($ticket['title']) ?></h1>

            <div class="ticket-author-info mt-2">
                <span>Submitted by <strong><?= htmlspecialchars($ticket['user_name']) ?></strong> (<?= htmlspecialchars($ticket['user_email']) ?>) on <?= date('M d, Y \a\t H:i', strtotime($ticket['created_at'])) ?></span>
            </div>

            <?php if (!empty($ticket['attachment_path'])): ?>
                <div class="attachment-box mt-3">
                    <span class="attach-icon">📎</span>
                    <span>Attached file: <strong><?= htmlspecialchars($ticket['attachment_name']) ?></strong></span>
                    <a href="/uploads/<?= htmlspecialchars($ticket['attachment_path']) ?>" target="_blank" class="btn btn-outline btn-sm">Download File</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Discussion Timeline -->
        <div class="timeline-container">
            <h3 class="mb-3">Activity & Message History</h3>

            <?php foreach ($messages as $msg): ?>
                <?php $isAdminMsg = ($msg['user_role'] === 'admin'); ?>
                <div class="timeline-message <?= $isAdminMsg ? 'msg-admin' : 'msg-client' ?>">
                    <div class="msg-header">
                        <div class="msg-user">
                            <span class="user-badge <?= $isAdminMsg ? 'badge-admin-tag' : 'badge-client-tag' ?>">
                                <?= $isAdminMsg ? '👨‍💻 Technical Support' : '👤 Customer' ?>
                            </span>
                            <strong><?= htmlspecialchars($msg['user_name']) ?></strong>
                        </div>
                        <span class="msg-time"><?= date('M d, Y H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                    <div class="msg-content">
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Reply Form -->
        <?php if ($ticket['status'] !== 'closed'): ?>
            <div class="card p-4 mt-4">
                <h4>Post Reply to Ticket</h4>
                <form action="/tickets/<?= $ticket['id'] ?>/reply" method="POST" class="mt-3">
                    <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                    <div class="form-group">
                        <textarea name="message" rows="4" class="form-control" required placeholder="Type your response, troubleshooting updates, or clarifications..."></textarea>
                    </div>
                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Submit Reply</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mt-4">
                This ticket has been marked as closed. If you require further assistance, please open a new support ticket.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
