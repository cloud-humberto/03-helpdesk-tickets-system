<?php
$pageTitle = 'Tickets Dashboard — HelpDesk Pro';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-top">
    <div>
        <h1 class="heading">Support Tickets</h1>
        <p class="subheading">
            <?= $currentUser['role'] === 'admin' 
                ? 'Managing universal queue across all customer organizations.' 
                : 'Monitor and review progress on your submitted requests.' ?>
        </p>
    </div>

    <div>
        <a href="/tickets/create" class="btn btn-primary">
            <span>+</span> Open New Ticket
        </a>
    </div>
</div>

<!-- Stats Metric Row -->
<div class="stats-row">
    <div class="stat-card">
        <span class="stat-label">Total Tickets</span>
        <span class="stat-num"><?= $stats['total'] ?></span>
    </div>
    <div class="stat-card stat-open">
        <span class="stat-label">Open</span>
        <span class="stat-num"><?= $stats['open'] ?></span>
    </div>
    <div class="stat-card stat-progress">
        <span class="stat-label">In Progress</span>
        <span class="stat-num"><?= $stats['in_progress'] ?></span>
    </div>
    <div class="stat-card stat-solved">
        <span class="stat-label">Resolved</span>
        <span class="stat-num"><?= $stats['resolved'] ?></span>
    </div>
</div>

<!-- Filters Bar & Table -->
<div class="card mt-4">
    <div class="filters-bar">
        <form method="GET" action="/tickets" class="filters-form">
            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="open" <?= ($statusFilter === 'open') ? 'selected' : '' ?>>Open</option>
                <option value="in_progress" <?= ($statusFilter === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                <option value="resolved" <?= ($statusFilter === 'resolved') ? 'selected' : '' ?>>Resolved</option>
                <option value="closed" <?= ($statusFilter === 'closed') ? 'selected' : '' ?>>Closed</option>
            </select>

            <select name="priority" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                <option value="low" <?= ($priorityFilter === 'low') ? 'selected' : '' ?>>Low</option>
                <option value="medium" <?= ($priorityFilter === 'medium') ? 'selected' : '' ?>>Medium</option>
                <option value="high" <?= ($priorityFilter === 'high') ? 'selected' : '' ?>>High</option>
                <option value="urgent" <?= ($priorityFilter === 'urgent') ? 'selected' : '' ?>>Urgent</option>
            </select>

            <?php if ($statusFilter || $priorityFilter): ?>
                <a href="/tickets" class="btn btn-outline btn-sm">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($tickets)): ?>
        <div class="empty-box">
            <span class="empty-icon">📭</span>
            <h3>No tickets found</h3>
            <p>No support tickets match the selected filter criteria.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="tickets-table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Subject</th>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <th>Requester</th>
                        <?php endif; ?>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Last Activity</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td class="font-mono">#<?= str_pad((string)$t['id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <a href="/tickets/<?= $t['id'] ?>" class="ticket-link">
                                    <strong><?= htmlspecialchars($t['title']) ?></strong>
                                </a>
                                <?php if (!empty($t['attachment_path'])): ?>
                                    <span title="File attached" style="cursor:help;">📎</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($currentUser['role'] === 'admin'): ?>
                                <td><?= htmlspecialchars($t['user_name']) ?></td>
                            <?php endif; ?>
                            <td>
                                <span class="badge-priority priority-<?= $t['priority'] ?>">
                                    <?= ucfirst($t['priority']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge-status status-<?= $t['status'] ?>">
                                    <?= str_replace('_', ' ', ucfirst($t['status'])) ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?= date('M d, Y H:i', strtotime($t['updated_at'])) ?>
                            </td>
                            <td class="text-right">
                                <a href="/tickets/<?= $t['id'] ?>" class="btn btn-outline btn-sm">
                                    View Details &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
