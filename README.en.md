# Bonus Points Expiry

*Читать по-русски: [README.md](README.md)*

A Shop-Script plugin that gives an expiry date to the bonus points of the
store's affiliate (bonus) program: unused points expire, and customers get an
email or SMS reminder in advance and come back to shop.

## Why you might need it

Some customers forget about the points they have collected, others save them
"for later". An expiry date and a reminder give them a reason to order now
rather than someday.

## Features

- Point lifetime in days, counted from the date the points were credited.
- Expired points are written off automatically on schedule: every Monday or
  on the 1st of every month. The write-off appears as a separate entry in the
  customer's bonus history.
- Fair calculation: only old points the customer never spent expire. Points
  credited later are not touched.
- Postponed start, so that points collected before the plugin was installed
  do not expire without warning.
- Any number of email and SMS reminders, each with its own text and schedule,
  showing the amount about to expire.
- Test sending of a notification to any address before turning it on.
- Supports both Webasyst UI 1.3 and UI 2.0.

The plugin's backend screens and the default notification texts are in
Russian. Notification texts are fully editable, so you can write them in any
language.

## How expiry is calculated

On the expiry day, the plugin checks how many points the customer had exactly
"lifetime" days ago and subtracts everything the customer has spent since
then. What is left are old points that were never used, and those expire. In
other words, a customer always spends the oldest points first.

**Example.** Lifetime is 90 days, expiry is monthly. On March 1, the plugin
looks at the balance as of December 1:

| Date | Event | Balance |
| --- | --- | ---: |
| November 15 | 500 points credited for an order | 500 |
| January 10 | 300 points credited for a new order | 800 |
| February 5 | 200 points spent on an order | 600 |
| **March 1** | **500 − 200 = 300 November points expire** | **300** |

Points do not expire on the very day their lifetime ends, but on the next
expiry day after it, so in practice they live a bit longer than the set
lifetime: up to a week with weekly expiry and up to a month with monthly
expiry.

## Requirements

- PHP 7.4 or later
- Shop-Script 8.18 or later
- Webasyst Installer 2.7 or later
- A cron job running the plugin's script

## Installation and setup

1. Install the plugin from the Webasyst Store.
2. In your hosting control panel, schedule the command with cron, preferably
   once an hour:

   ```
   php /path/to/webasyst/cli.php shop burningbonus
   ```

   The exact command with your installation path is shown in the plugin
   settings.
3. In **Marketing → Affiliate program → Сгорание бонусов**, set the points'
   lifetime and the expiry schedule.
4. If customers already have old points, postpone the start of expiry.
5. In **Marketing → Уведомления о сгорании бонусов**, create reminders and
   check them with a test message.

If the script does not run even once on an expiry day or on a notification's
sending day, that write-off or mailing is skipped. That is why we recommend
running it once an hour rather than once a day.

## Settings

| Setting | Description |
| --- | --- |
| Lifetime | How many days the points stay valid. |
| Expiry schedule | Weekly (every Monday), monthly (on the 1st) or never: the plugin neither writes off points nor sends reminders. |
| Postpone the start of expiry | Points do not expire before this date. |

## Notifications

Each notification has a channel (email or SMS), a sending day (a day of the
month or a day of the week), a time it is sent no earlier than, a subject and
a text. A notification is sent once on its day, and only to customers whose
points will expire on the next expiry day. Email goes to the customer's
primary email address, SMS to the primary phone number; customers without one
are skipped.

For monthly notifications, choose a day no later than the 28th: in a month
without the 29th, 30th or 31st, a notification set to that day is not sent.

Texts support HTML and Smarty. The main variables:

| Variable | Value |
| --- | --- |
| `{$burning_amount}` | Points about to expire |
| `{$customer->get('firstname')}` | Customer's first name |
| `{$customer->affiliateBonus()}` | Current point balance |
| `{$wa->shop->burningbonusPlugin->duration()}` | Point lifetime in days |
| `{$wa->shop->burningbonusPlugin->next_burn()\|wa_date}` | Next expiry date (`YYYY-MM-DD` without the modifier) |

## License

[Webasyst End User License Agreement](LICENSE) ([in Russian](LICENSE_ru))

## Author

Serge Rodovnichenko ([syrnik.com](https://syrnik.com))
