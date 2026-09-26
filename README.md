# 🎫 HelpDesk Pro — Enterprise Ticket Management System with RBAC & Secure Uploads

> **Portfolio Project for Junior PHP Developer Applications**  
> A robust support ticketing system built in **object-oriented PHP 8+**, featuring Role-Based Access Control (**RBAC**), ticket state transitions, interactive timeline discussions, and **secure diagnostic file attachments**.

---

## 🚀 Key Technical Competencies

- **Role-Based Access Control (RBAC)**:
  - **Technician / Administrator**: Full access to global ticket queues, ticket status progression (`Open` &rarr; `In Progress` &rarr; `Resolved` &rarr; `Closed`), and technical notes.
  - **Customer / Client**: Strictly isolated access allowing users to view and update only their own opened tickets.
- **Secure File Upload Pipeline**:
  - Whitelist file extension enforcement (`jpg`, `jpeg`, `png`, `pdf`, `txt`).
  - Strict server-side **MIME Type inspection** using `finfo_file(FILEINFO_MIME_TYPE)` (never trusts client headers).
  - Cryptographic 32-character random filename generation neutralizing Remote Code Execution (RCE) vectors.
  - Size limitation (2MB) and secure download streaming through PHP.
- **Support Ticket State Machine**:
  - `Open` &rarr; `In Progress` &rarr; `Resolved` &rarr; `Closed`.
  - Severity priorities: `Low`, `Medium`, `High`, `Urgent`.
- **Discussion Activity Timeline**:
  - Chronological message history with distinct author badges and timestamps.

---

## 📁 Project Structure

```text
03-helpdesk-tickets-system/
├── database/
│   └── helpdesk.sqlite        # SQLite database populated with demo seeds
├── public/
│   ├── css/
│   │   └── style.css          # Dark themed responsive interface
│   └── index.php              # Front Controller and secure attachment streamer
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php    # Login flow with 1-click test fill shortcuts
│   │   └── TicketController.php  # Ticket workflow, uploads, and timeline replies
│   ├── Core/
│   │   ├── Auth.php              # Session and RBAC role helpers
│   │   ├── Database.php          # PDO SQLite Singleton
│   │   └── Router.php            # Dynamic URI parameter router
│   ├── Models/
│   │   ├── Message.php           # Timeline discussion entries
│   │   ├── Ticket.php            # Ticket state, queries, and filters
│   │   └── User.php              # User accounts and roles
│   └── Views/
│       ├── auth/login.php        # Sign in view
│       ├── layouts/              # Header and footer
│       └── tickets/              # Queue, creation, and detail views
├── storage/
│   └── uploads/                  # Protected upload directory
└── README.md
```

---

## 🛠️ How to Run Locally

Launch PHP's built-in web server:

```bash
cd 03-helpdesk-tickets-system
php -S localhost:8002 -t public
```

Open in your browser:
👉 **[http://localhost:8002](http://localhost:8002)**

---

## 👥 Pre-Configured Demo Accounts

On the sign-in screen, click the quick-fill buttons to test both roles:

| Role | Email | Password | Scope & Testing Capabilities |
|---|---|---|---|
| 👑 **Technician / Admin** | `admin@company.com` | `password123` | View global ticket queue, change ticket status, and post official replies |
| 👤 **Client User** | `sarah@client.com` | `password123` | View personal tickets only, open new tickets with attachments, reply on active tickets |
