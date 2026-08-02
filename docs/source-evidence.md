# Source Evidence

## Stack Evidence

- Unit 23 names `ClassController`, `BookingRequest`, `Student model`, and
  dashboard routes as the architecture direction.
- Unit 29 explicitly names `Laravel`, `MySQL`, `server-rendered pages`, `native
  JavaScript`, and `theme CSS folders`.
- Unit 50 names PHP version, MySQL, SSL, cron, email support, backups, and file
  permissions as hosting criteria.
- Unit 52 names public directory, `.env`, storage link, cache clear, and
  migrations as deployment concerns.

## Product Evidence

- Unit 04: public pages, class calendar, instructor dashboard, student signups,
  blog, and email notifications.
- Unit 34: studio setup wizard with owner name, studio logo, contact email,
  class categories, schedule defaults, and theme selection.
- Unit 35: owner login, dashboard metrics, weekly availability, and booking
  management.
- Unit 36: calendar view, class articles, student profiles, and automatic
  student creation from bookings.
- Unit 37: student notes, waiver templates, FAQ pages, and downloadable
  documents.
- Unit 38: public phrases, social links, email settings, theme selection, and
  image management.
- Unit 39: landing page, class list, booking form, blog, FAQ, map, and contact
  buttons.

## Runtime Notes

This project includes MySQL configuration and Docker Compose. Docker Desktop was
installed but its daemon was not running during creation, so local validation was
performed with SQLite after enabling PHP SQLite extensions. The Laravel schema
and Eloquent code are compatible with MySQL.
