# Security Policy

## Supported Versions

| Version | Supported |
|---------|-----------|
| 1.x     | ✅ Active  |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

If you discover a security vulnerability in this package, please disclose it responsibly by emailing:

**<security@queryinspector.io>**

Include as much detail as possible:

- A description of the vulnerability and its potential impact
- Steps to reproduce the issue
- Any proof-of-concept code (if available)
- Your suggested fix (optional but appreciated)

### Response Timeline

- **Acknowledgement:** within 48 hours
- **Initial Assessment:** within 5 business days
- **Fix & Release:** typically within 14 days for critical issues

We will credit reporters who responsibly disclose vulnerabilities unless they prefer to remain anonymous.

## Security Considerations

### Dashboard Access

The built-in web dashboard should **never** be publicly accessible in production without proper authentication. Configure the `query-monitor.dashboard.gate` option or restrict access via your application's authentication middleware.

### Sensitive Data in Query Logs

Query bindings are stored in the `query_monitor_logs` table. If your application handles personally identifiable information (PII) or secrets in query parameters, review whether storing full bindings is appropriate. You can disable binding capture or limit retention via `retention_days`.

### Database Permissions

The `query_monitor_logs` table should only be accessible to trusted application roles. Do not expose it through your API without proper authorization.
