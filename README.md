# Digital Shop Demo

MVC digital goods shop implemented with pure PHP 8.4, no templating engines or frameworks.

**All stages** of the task have been completed.

## Requirements

- PHP 8.4
- MySQL 8.4

## Setup

1. Copy or clone the project to a web server root.
2. Import schema and seed data:

```bash
php database/migrate.php
```

3. Configure web server document root to `public/`.
4. Open the site in a browser.
5. Checking the purchase for the item **"Пополнение Steam 500 ₽".**

## How the key itself is issued exactly once

- **Database transactions** are used for all order/status transitions.
- The `gateway_events` table has a unique `event_id` column; duplicate webhooks are detected and ignored.
- **`SELECT ... FOR UPDATE`** locks the order row and the first available key row, so two concurrent delivery attempts cannot take the same key.
- The supplier request is stored with a unique `request_id`; repeated calls with the same request return the already stored code.
- The `keys.code` column is unique and `keys.order_id IS NULL` plus row locking prevents the same key from being issued to two orders.
- If no key is available, the order moves to `out_of_stock` (recoverable, not fatal). Admin retry later becomes idempotent.

## Reproduce race conditions

### 50 parallel paid webhooks for one order

1. Create an order:
   ```bash
   curl -X POST http://site-name/orders -H "Content-Type: application/json" -d "{\"sku\":\"STEAM-TOPUP-500\"}"
   ```
2. Use the returned `order_number` and run the test script:
   ```bash
   php tests/race_webhook.php ord_xxxxxxxx
   ```
3. The script sends 50 `POST /webhook/payment` requests concurrently, each with a unique `event_id`.
   It then follows the complete delivery flow:
   - reads the order from the database;
   - repeatedly calls `POST /supplier/issue` with the same `request_id` and provider `A`, handling transient provider errors/timeouts;
   - repeats the supplier request to verify idempotency;
   - checks the database state.

Expected result:

- The script ends with `OK: payment webhooks are idempotent and exactly one key was issued.`
- The order status is `delivered`.
- Exactly one key has status `reserved` or `issued` for that order.
- At least one supplier request was recorded.
- Repeating the same `request_id` returns the same code and does not consume another key.
- If the supplier returns `out_of_stock`, the script reports an error; add keys and retry manually from `/admin/pending`.

### Promocode limited to N uses

Create several orders with the same promocode concurrently with `tests/race_promocode.php`.
The server computes discount and limits usage atomically.

### Order recovery after key exhaustion

1. Empty the `keys` table or set all keys to 'issued'.
2. Pay for an order. It goes to `out_of_stock`.
3. Add keys back (status 'available').
4. Open `/admin/pending` and click “Выдать повторно”.

The order is delivered with a single key, without duplicates.

### Out-of-order webhook

Creation of an order detects already stored but unprocessed gateway events and processes them.
If a webhook arrives before the order, it is stored and processed after the order is created.
