# Laravel-Team-Lime

## E-mails

Registrations send e-mails (confirmation to the participant, notice to the owners, confirmation of a cancellation).
They are put in a queue, so a mail error never removes a registration.

1. `.env`: `QUEUE_CONNECTION=database` (the `jobs` table is created by `php artisan migrate`).
2. `.env`: `APP_URL` must be the real address of the site, it is used for the links inside the e-mails
   (they are built by the queue worker, not during a web request).
3. `.env`: `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME` are the sender of the e-mails.
4. `MAIL_MAILER=log` writes the e-mails in `storage/logs/laravel.log` (enough for development).
   To see them like in a real mailbox, use an SMTP test server (Mailtrap, MailHog) with `MAIL_MAILER=smtp`
   and `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`.
5. Run the worker in a separate terminal, otherwise the e-mails stay in the queue:

```bash
php artisan queue:work
```

Restart the worker after a change in the code of a mail or a listener.
Failed e-mails are listed with `php artisan queue:failed` and sent again with `php artisan queue:retry all`.
