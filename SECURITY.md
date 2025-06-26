# Security Policy

## Supported Versions

We actively support the following versions of Esistenze WordPress Kit with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 2.0.x   | :white_check_mark: |
| 1.2.x   | :white_check_mark: |
| 1.1.x   | :x:                |
| < 1.1   | :x:                |

## Reporting a Vulnerability

We take security vulnerabilities seriously. If you discover a security vulnerability in Esistenze WordPress Kit, please report it to us responsibly.

### How to Report

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please send an email to [security@esistenze.com](mailto:security@esistenze.com) with the following information:

- A clear description of the vulnerability
- Steps to reproduce the issue
- Potential impact of the vulnerability
- Any suggested fixes or patches
- Your contact information

### What to Expect

- **Acknowledgment**: We will acknowledge receipt of your vulnerability report within 48 hours.
- **Investigation**: We will investigate the issue and determine its severity within 5 business days.
- **Updates**: We will keep you informed of our progress throughout the investigation.
- **Resolution**: We aim to resolve critical vulnerabilities within 30 days.
- **Disclosure**: We will coordinate with you on the disclosure timeline.

### Responsible Disclosure

We ask that you:

- Give us reasonable time to investigate and fix the issue before public disclosure
- Do not access or modify data that doesn't belong to you
- Do not perform actions that could harm our users or services
- Do not disclose the vulnerability to others until we have resolved it

### Recognition

We appreciate security researchers who help keep our users safe. With your permission, we will:

- Acknowledge your contribution in our security advisories
- Add your name to our security hall of fame
- Provide a monetary reward for significant vulnerabilities (case by case basis)

## Security Best Practices

### For Users

- Always keep the plugin updated to the latest version
- Use strong, unique passwords for WordPress admin accounts
- Enable two-factor authentication when possible
- Regularly backup your website
- Use HTTPS for your website
- Keep WordPress core and other plugins updated

### For Developers

- Follow WordPress security best practices
- Sanitize all user input
- Escape all output
- Use nonces for form submissions
- Implement proper capability checks
- Validate and sanitize AJAX requests
- Use prepared statements for database queries
- Avoid using `eval()` or similar functions
- Keep dependencies updated

## Security Features

Esistenze WordPress Kit includes several security features:

- **Capability-based access control**: Different user roles have appropriate access levels
- **Nonce verification**: All form submissions are protected against CSRF attacks
- **Data sanitization**: All user input is properly sanitized before processing
- **Output escaping**: All output is properly escaped to prevent XSS attacks
- **File access protection**: Index.php files prevent directory browsing
- **Secure AJAX**: All AJAX requests include proper security checks
- **Database security**: Uses WordPress database API with prepared statements

## Security Considerations

### Permissions

The plugin requires certain WordPress capabilities:

- `manage_options`: For administrators (full access)
- `edit_pages`: For editors (limited access)
- `edit_posts`: For authors (restricted access)

### Data Storage

- All plugin data is stored in WordPress database
- No sensitive data is stored in plain text
- User preferences are stored securely
- Analytics data is anonymized

### Third-party Integrations

- WhatsApp integration uses official WhatsApp API
- Email functionality uses WordPress mail functions
- No data is sent to external services without user consent

## Vulnerability Disclosure Timeline

1. **Day 0**: Vulnerability reported
2. **Day 1-2**: Acknowledgment sent to reporter
3. **Day 3-7**: Investigation and impact assessment
4. **Day 8-30**: Development and testing of fix
5. **Day 31**: Security update released
6. **Day 31+**: Public disclosure (coordinated with reporter)

## Security Updates

Security updates are released as soon as possible after a vulnerability is confirmed and fixed. These updates are:

- Marked clearly as security releases
- Documented in the changelog
- Announced on our website and social media
- Automatically available through WordPress update system

## Contact Information

For security-related questions or concerns:

- **Email**: [security@esistenze.com](mailto:security@esistenze.com)
- **Website**: [https://esistenze.com/security](https://esistenze.com/security)
- **Response Time**: Within 48 hours for security issues

Thank you for helping keep Esistenze WordPress Kit secure! 