<?php
/** @var array $order */
/** @var string|null $keyCode */
?>
<div class="container status-page">
    <h1>Заказ <?= htmlspecialchars($order['order_number']) ?></h1>
    <p>Статус: <strong><?= htmlspecialchars($order['status']) ?></strong></p>
    <p>Товар: <?= htmlspecialchars($order['product_sku']) ?></p>
    <p>Сумма: <?= htmlspecialchars($order['final_amount']) ?> <?= htmlspecialchars($order['currency']) ?></p>

    <?php if ($order['status'] === 'delivered' && $keyCode): ?>
        <section class="delivered-key">
            <h2>Ваш ключ</h2>
            <p><strong><?= htmlspecialchars($keyCode) ?></strong></p>
        </section>
    <?php elseif (in_array($order['status'], ['created', 'payment_failed'], true)): ?>
        <button type="button" class="pay-btn" id="paySuccessBtn" data-order="<?= htmlspecialchars($order['order_number']) ?>">Оплатить (успех)</button>
        <button type="button" class="pay-btn" id="payFailBtn" data-order="<?= htmlspecialchars($order['order_number']) ?>">Оплатить (неуспех)</button>
        <p id="payResult"></p>
    <?php elseif (in_array($order['status'], ['paid', 'delivering', 'delivery_failed'], true)): ?>
        <p>Выберите поставщика для получения ключа:</p>
        <button type="button" class="supplier-btn" id="supplierABtn"
                data-order="<?= htmlspecialchars($order['order_number']) ?>"
                data-sku="<?= htmlspecialchars($order['product_sku']) ?>"
                data-provider="A">Получить у Поставщика А</button>
        <button type="button" class="supplier-btn" id="supplierBBtn"
                data-order="<?= htmlspecialchars($order['order_number']) ?>"
                data-sku="<?= htmlspecialchars($order['product_sku']) ?>"
                data-provider="B">Получить у Поставщика B</button>
        <p id="supplierResult"></p>
    <?php else: ?>
        <p>Заказ находится в статусе «<?= htmlspecialchars($order['status']) ?>». Обновите страницу позже.</p>
    <?php endif; ?>
</div>
