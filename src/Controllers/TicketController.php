<?php
declare(strict_types=1);

namespace HelpDesk\Controllers;

use HelpDesk\Core\Auth;
use HelpDesk\Models\Message;
use HelpDesk\Models\Ticket;

class TicketController
{
    private function requireAuth(): array
    {
        if (!Auth::check()) {
            $_SESSION['flash_error'] = 'Restricted area. Please sign in to continue.';
            header('Location: /login');
            exit;
        }
        return Auth::user();
    }

    public function index(): void
    {
        $user = $this->requireAuth();

        $userIdFilter = ($user['role'] === 'admin') ? null : $user['id'];
        $statusFilter = $_GET['status'] ?? null;
        $priorityFilter = $_GET['priority'] ?? null;

        $tickets = Ticket::all($userIdFilter, $statusFilter, $priorityFilter);
        $stats = Ticket::getStats($userIdFilter);

        $currentUser = $user;
        $csrfToken = Auth::csrfToken();

        require_once __DIR__ . '/../Views/tickets/index.php';
    }

    public function create(): void
    {
        $currentUser = $this->requireAuth();
        $csrfToken = Auth::csrfToken();
        require_once __DIR__ . '/../Views/tickets/create.php';
    }

    public function store(): void
    {
        $user = $this->requireAuth();

        $token = $_POST['_csrf_token'] ?? '';
        if (!Auth::validateCsrf($token)) {
            $_SESSION['flash_error'] = 'Invalid CSRF security token.';
            header('Location: /tickets/create');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';

        if (mb_strlen($title) < 5 || empty($description)) {
            $_SESSION['flash_error'] = 'A descriptive title and problem explanation are required.';
            header('Location: /tickets/create');
            exit;
        }

        $attachmentName = null;
        $attachmentPath = null;

        // Secure File Upload Processing
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if ($file['size'] > $maxSize) {
                $_SESSION['flash_error'] = 'The attached file exceeds the 2MB size limit.';
                header('Location: /tickets/create');
                exit;
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'txt'];
            $origName = basename($file['name']);
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExtensions, true)) {
                $_SESSION['flash_error'] = 'File extension not allowed. Permitted: JPG, PNG, PDF, TXT.';
                header('Location: /tickets/create');
                exit;
            }

            // Real MIME Type inspection (Magic Bytes)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf', 'text/plain'];
            if (!in_array($mimeType, $allowedMimes, true)) {
                $_SESSION['flash_error'] = 'File MIME type validation failed.';
                header('Location: /tickets/create');
                exit;
            }

            // Cryptographic random filename to prevent Remote Code Execution
            $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
            $uploadDir = __DIR__ . '/../../storage/uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $attachmentName = $origName;
                $attachmentPath = $newFileName;
            }
        }

        $ticketId = Ticket::create($user['id'], $title, $description, $priority, $attachmentName, $attachmentPath);

        // Record initial message in timeline
        Message::create($ticketId, $user['id'], $description);

        $_SESSION['flash_success'] = "Ticket #{$ticketId} opened successfully!";
        header("Location: /tickets/{$ticketId}");
        exit;
    }

    public function show(array $params): void
    {
        $user = $this->requireAuth();
        $id = (int) ($params['id'] ?? 0);

        $ticket = Ticket::findById($id);

        if (!$ticket) {
            http_response_code(404);
            die("Support ticket not found.");
        }

        // RBAC: Clients can only access their own tickets
        if ($user['role'] !== 'admin' && (int)$ticket['user_id'] !== (int)$user['id']) {
            http_response_code(403);
            die("Access Denied: You do not have permission to view this ticket.");
        }

        $messages = Message::getByTicket($id);
        $currentUser = $user;
        $csrfToken = Auth::csrfToken();

        require_once __DIR__ . '/../Views/tickets/show.php';
    }

    public function reply(array $params): void
    {
        $user = $this->requireAuth();
        $id = (int) ($params['id'] ?? 0);

        $ticket = Ticket::findById($id);
        if (!$ticket) {
            header('Location: /tickets');
            exit;
        }

        if ($user['role'] !== 'admin' && (int)$ticket['user_id'] !== (int)$user['id']) {
            header('Location: /tickets');
            exit;
        }

        $token = $_POST['_csrf_token'] ?? '';
        if (!Auth::validateCsrf($token)) {
            $_SESSION['flash_error'] = 'Invalid CSRF security token.';
            header("Location: /tickets/{$id}");
            exit;
        }

        $message = trim($_POST['message'] ?? '');
        if (!empty($message)) {
            Message::create($id, $user['id'], $message);
            $_SESSION['flash_success'] = 'Reply sent successfully!';
        }

        header("Location: /tickets/{$id}");
        exit;
    }

    public function updateStatus(array $params): void
    {
        $user = $this->requireAuth();
        if ($user['role'] !== 'admin') {
            header('Location: /tickets');
            exit;
        }

        $id = (int) ($params['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $validStatuses = ['open', 'in_progress', 'resolved', 'closed'];

        if (in_array($status, $validStatuses, true)) {
            Ticket::updateStatus($id, $status);
            $_SESSION['flash_success'] = "Ticket status updated to '{$status}'.";
        }

        header("Location: /tickets/{$id}");
        exit;
    }
}
