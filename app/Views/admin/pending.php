<?php
/** @var array $orders */
?>
<div class="container admin-page">
    <h1>Заказы, ожидающие выдачи</h1>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Заказ</th>
                <th>Товар</th>
                <th>Статус</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= htmlspecialchars($order['order_number']) ?></td>
                <td><?= htmlspecialchars($order['product_sku']) ?></td>
                <td><?= htmlspecialchars($order['status']) ?></td>
                <td><button type="button" class="retry-btn" data-order="<?= htmlspecialchars($order['order_number']) ?>">Выдать повторно</button></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
            <tr><td colspan="4">Нет заказов для обработки</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
