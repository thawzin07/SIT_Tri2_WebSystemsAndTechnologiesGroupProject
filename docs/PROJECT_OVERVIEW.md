# Documentation

## Security Controls Implemented

- Password hashing with `password_hash` and verification with `password_verify`
- PDO prepared statements for all DB operations
- CSRF tokens on state-changing forms
- Session-based authentication and role checks
- Output escaping with `htmlspecialchars`
- Basic server-side validation and sanitization

## CRUD Matrix

- Users: Admin create/read/update/delete
- Membership plans: Admin create/read/update/delete
- Membership records: Member create (subscribe/renew), update (cancel), read history
- Trainers: Admin create/read/update/delete
- Classes: Admin create/read/update/delete
- Bookings: Member create/update(cancel)/read, Admin read/update/delete
- Gym locations: Admin create/read/update/delete
- Contact messages: Public create, Admin read/delete
