<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - {{ config('frontend_content.school_name', 'School') }} App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
            color: #333;
        }
        h1, h2 {
            color: #2c3e50;
        }
        ul {
            padding-left: 20px;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Privacy Policy</h1>
    <p><strong>Effective Date:</strong> January 1, 2025</p>
    <p><strong>App Name:</strong> {{ config('frontend_content.school_name', 'School') }} App</p>
    <p><strong>Developer Contact Email:</strong> {{ config('frontend_content.contact.email', 'info@school.org') }}</p>

    <h2>1. Introduction</h2>
    <p>This Privacy Policy describes how <strong>{{ config('frontend_content.school_name', 'School') }} App</strong> ("we," "our," or "us") handles personal and non-personal information in connection with our mobile application, available on the Google Play Store.</p>
    <p>By using our app, you agree to the collection, use, and disclosure of information in accordance with this Privacy Policy. If you do not agree with this policy, please do not use the app.</p>

    <h2>2. Information We Do Not Collect</h2>
    <p>We do <strong>not collect</strong>, store, or share any personal or sensitive user data.</p>
    <p>We also do not use cookies or similar tracking technologies.</p>

    <h2>3. Information We May Collect Automatically</h2>
    <p>While we do not collect personal data, we may collect limited, non-personal information automatically to improve app functionality and performance, such as:</p>
    <ul>
        <li>App usage data (e.g., crash reports, user interactions)</li>
        <li>Device type and operating system version</li>
        <li>Anonymous analytics data</li>
    </ul>

    <h2>4. Third-Party Services</h2>
    <p>Our app may use third-party services for analytics, crash reporting, or advertisements. These services may collect information used to identify you. Examples include:</p>
    <ul>
        <li>Google Play Services</li>
        <li>Google Analytics for Firebase</li>
        <li>Firebase Crashlytics</li>
        <li>AdMob (if ads are used)</li>
    </ul>
    <p>Review their privacy policies:</p>
    <ul>
        <li><a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a></li>
        <li><a href="https://firebase.google.com/support/privacy" target="_blank">Firebase Privacy and Security</a></li>
    </ul>

    <h2>5. Children’s Privacy</h2>
    <p>Our app is suitable for general audiences, including children under the age of 13. We comply with COPPA and other applicable child privacy laws.</p>
    <p>We do not knowingly collect personal information from children under 13. If a parent or guardian becomes aware that their child has provided us personal data, please contact us and we will delete the information.</p>

    <h2>6. Data Security</h2>
    <p>We take appropriate measures to protect any data we handle. However, no method of transmission or electronic storage is 100% secure.</p>

    <h2>7. Data Retention</h2>
    <p>We retain non-personal information only as long as necessary to fulfill the purposes outlined in this policy or as required by law.</p>

    <h2>8. User Rights</h2>
    <p>As we do not collect personal data, there is no information for users to access, modify, or delete. If you have questions, contact us at the email address below.</p>

    <h2>9. Changes to This Privacy Policy</h2>
    <p>We may update our Privacy Policy from time to time. Any changes will be posted on this page with a new effective date.</p>

    <h2>10. Contact Us</h2>
    <p>If you have any questions about this policy, please contact us at:</p>
    <p><strong>Email:</strong> {{ config('frontend_content.contact.email', 'info@school.org') }}</p>
</body>
</html>
