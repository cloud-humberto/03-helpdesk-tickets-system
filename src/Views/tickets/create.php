<?php
$pageTitle = 'Open New Ticket — HelpDesk Pro';
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="form-container">
    <div class="card p-4">
        <div class="card-header-clean">
            <h2>Submit Support Request</h2>
            <p class="text-muted">Describe the incident or request thoroughly to facilitate rapid resolution.</p>
        </div>

        <form action="/tickets" method="POST" enctype="multipart/form-data" class="mt-4">
            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">

            <div class="form-group">
                <label for="title">Subject Summary</label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Gateway Timeout on Checkout API">
            </div>

            <div class="form-group">
                <label for="priority">Priority Severity</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low">Low (General inquiries, minor UI suggestions)</option>
                    <option value="medium" selected>Medium (Isolated issue with workable workaround)</option>
                    <option value="high">High (Major functionality impaired)</option>
                    <option value="urgent">Urgent (Production critical outage / blocker)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Detailed Description</label>
                <textarea id="description" name="description" rows="5" class="form-control" required placeholder="Provide step-by-step reproduction instructions, logs, error codes..."></textarea>
            </div>

            <div class="form-group">
                <label for="attachment">Attach Diagnostic File (Optional)</label>
                <input type="file" id="attachment" name="attachment" class="form-control form-control-file" accept=".jpg,.jpeg,.png,.pdf,.txt">
                <small class="text-muted">Allowed extensions: JPG, PNG, PDF, or TXT (Max 2MB). Validated by server-side MIME type.</small>
            </div>

            <div class="form-actions mt-4">
                <a href="/tickets" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit Ticket</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
