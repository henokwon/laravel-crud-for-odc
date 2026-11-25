# Administrative access and password changes

Set `ADMIN_USER_IDS` to existing trusted account IDs, separated by commas, in the
server environment and rebuild the Laravel configuration cache. Empty settings
deny administrative access. Public registration does not grant administration.
Only approved accounts may manage users, employees and reference data.

Employee API routes use first-party browser session authentication plus CSRF
protection. Anonymous and ordinary accounts cannot access administrative data.

The authenticated password-change endpoint permits self-service changes, or an
approved administrator resetting another user's password. Both require the
acting user's current password and confirmation of a new password with at least
eight characters. The form labels the current password as the operator's own.
Password-reset-by-email remains a separate flow.

Audit accounts and invalidate compromised credentials/sessions before deployment;
these source changes do not undo previous unauthorized changes.
