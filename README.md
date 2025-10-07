# Manga Website

A comprehensive manga management and tracking website built with PHP, MySQL, and JavaScript. Users can browse manga, track their reading progress, and administrators can manage pending submissions.

## Features

### User Features
- **User Registration & Login** with secure password hashing
- **Manga Browsing** with search functionality
- **Personal Bookmarks** - Track reading progress with status (Reading, Completed, Dropped, Plan to Read)
- **Rating System** - Rate manga from 1-10
- **Chapter Progress Tracking** - Keep track of chapters read
- **Real-time Notifications** - Get notified about manga approvals and updates
- **Responsive Design** - Mobile-friendly interface

### Admin Features
- **Manga Approval System** - Review and approve/disapprove submitted manga
- **Automatic Page Generation** - Creates dedicated pages for approved manga
- **Admin Dashboard** - Manage pending submissions
- **Notification System** - Notify admins of new submissions

### Manga Features
- **Multiple Types** - Support for Manga, Manhwa, and Manhua
- **Genre Classification** - Organize manga by genres
- **Rating Display** - Shows average user ratings with star system
- **Recommendations** - Suggests similar manga based on genres
- **Image Management** - Upload and display manga cover images

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Bootstrap 4.5, Custom CSS
- **Icons**: Material Icons, Lucide Icons
- **Security**: Password hashing (bcrypt), SQL injection prevention

## Installation

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 8.0 or higher
- Web server (Apache recommended)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone [your-repository-url]
   cd manga-website
   ```

2. **Database Setup**
   ```bash
   # Import the database schema
   mysql -u root -p < sql.sql
   ```

3. **Configure Database Connection**
   - Update database credentials in `php/db_connection.php`
   ```php
   $servername = "localhost";
   $username = "your_username";
   $password = "your_password";
   $dbname = "manga";
   ```

4. **Set Up File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 series/
   ```

5. **Run Migrations**
   ```bash
   php php/migrations/add_notifications_table.php
   php php/migrations/add_is_admin_column.php
   ```

6. **Create Admin User**
   - Register a regular account
   - Insert admin record in database:
   ```sql
   INSERT INTO admin (user_id) VALUES (your_user_id);
   UPDATE users SET is_admin = 1 WHERE id = your_user_id;
   ```

## Database Schema

### Core Tables
- **users** - User accounts and authentication
- **manga** - Manga information and metadata
- **admin** - Admin user privileges
- **user_list** - User reading progress and ratings
- **notifications** - Real-time notification system

### Key Relationships
- Users can have multiple manga in their list
- Admins receive notifications for pending manga
- Approved manga get dedicated pages automatically

## File Structure

```
manga-website/
├── CSS/                    # Stylesheets
├── JS/                     # JavaScript files
│   ├── notifications.js    # Notification system
│   ├── search.js          # Search functionality
│   └── user.js            # User management
├── php/                    # PHP backend
│   ├── *.php              # Core PHP files
├── series/                 # Auto-generated manga pages
├── uploads/               # User uploaded images
├── images/                # Site assets
└── sql.sql               # Database schema
```

## API Endpoints

### Notifications API (`php/notifications_api.php`)
- `GET ?action=get_count` - Get unread notification count
- `GET ?action=get_notifications` - Get user notifications
- `POST ?action=mark_read` - Mark notification as read
- `POST ?action=mark_all_read` - Mark all notifications as read

### Manga Management
- `php/add_manga.php` - Submit new manga
- `php/approve_manga.php` - Approve pending manga
- `php/disapprove_manga.php` - Reject pending manga
- `php/search_manga.php` - Search manga database

### User Management
- `php/process_login.php` - User authentication
- `php/process_register.php` - User registration
- `php/update_manga_status.php` - Update reading progress

## Security Features

- **Password Security**: Uses bcrypt hashing and checks against leaked password databases
- **SQL Injection Prevention**: Prepared statements throughout
- **Session Management**: Secure session handling
- **Input Validation**: Server-side validation for all user inputs
- **File Upload Security**: Validates file types and sizes

## Configuration

### Environment Setup
1. Ensure PHP extensions are enabled:
   - mysqli
   - session
   - file_get_contents (for password breach checking)

2. Set appropriate file permissions for upload directories

3. Configure your web server to handle PHP files

### Customization
- Modify `CSS/` files for styling changes
- Update database credentials in connection files
- Adjust notification polling interval in `JS/notifications.js`
- Customize manga page templates in `php/approve_manga.php`

## Usage

### For Users
1. Register an account or log in
2. Browse manga using search or category filters
3. Add manga to your list with status and rating
4. Track reading progress with chapter counts
5. Receive notifications for updates

### For Admins
1. Access admin panel through pending manga section
2. Review submitted manga for approval
3. Edit descriptions and approve/disapprove submissions
4. Monitor notification system for new submissions

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Troubleshooting

### Common Issues

**Database Connection Failed**
- Check database credentials in `php/db_connection.php`
- Ensure MySQL service is running
- Verify database exists

**File Upload Issues**
- Check folder permissions for `uploads/` directory
- Verify PHP upload limits in `php.ini`
- Ensure file extensions are allowed

**Notifications Not Working**
- Run the notifications migration script
- Check JavaScript console for errors
- Verify user session is active

**Manga Pages Not Generating**
- Check write permissions for `series/` directory
- Ensure manga approval process completes successfully
- Verify file path generation logic
