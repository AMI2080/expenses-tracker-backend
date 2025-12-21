<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User Verified</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 30px;
        }
        .header {
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4a90e2;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .user-details {
            background-color: #f8f9fa;
            border-left: 4px solid #4a90e2;
            padding: 20px;
            margin: 20px 0;
        }
        .user-details p {
            margin: 8px 0;
        }
        .user-details strong {
            color: #333;
            display: inline-block;
            min-width: 140px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4a90e2;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New User Verified</h1>
        </div>

        <div class="content">
            <p>Hello Admin,</p>

            <p>A new user has verified their email address and is now pending approval.</p>

            <div class="user-details">
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Registration Date:</strong> {{ $user->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>

            <p>Please review and approve this user to allow them access to the system.</p>

            <a href="{{ $actionUrl }}" class="button">Review Pending Users</a>
        </div>

        <div class="footer">
            <p>This is an automated notification from the Expenses Tracker system.</p>
        </div>
    </div>
</body>
</html>

