<?php
/** @var array $products */
?>
<div class="container">
    <section class="slider">
        <div class="slider-container">
            <div class="slider-track">
                <div class="slide"><img src="/assets/slider/slide1.png" alt="slide1"></div>
                <div class="slide"><img src="/assets/slider/slide2.png" alt="slide2"></div>
                <div class="slide"><img src="/assets/slider/slide3.png" alt="slide3"></div>
                <div class="slide"><img src="/assets/slider/slide4.png" alt="slide4"></div>
                <div class="slide"><img src="/assets/slider/slide5.png" alt="slide5"></div>
                <div class="slide"><img src="/assets/slider/slide6.png" alt="slide6"></div>
            </div>
            <div class="slider-arrows">
                <button class="slider-arrow prev" aria-label="Назад">&#10229;</button>
                <button class="slider-arrow next" aria-label="Вперед">&#10230;</button>
            </div>
            <div class="slider-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </section>

    <section class="service-icons">
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-steam.png" alt="Steam"><div>Steam</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-telegram.png" alt="Telegram"><div>Telegram</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-roblox.png" alt="Roblox"><div>Roblox</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-brawl-stars.png" alt="Brawl Stars"><div>Brawl Stars</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-pubg.png" alt="PUBG"><div>PUBG Mob.</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-app-store.png" alt="App Store"><div>App Store</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-chat-gpt.png" alt="Chat GPT"><div>ChatGPT</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-playstation.png" alt="PlayStation"><div>PlayStation</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-tiktok.png" alt="TikTok"><div>TikTok</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-mobile-legend.png" alt="Mobile Legend"><div>Mobile Leg.</div></a></div>
        <div class="icon-item"><a href="#"><img src="/assets/icon/icon-more.png" alt="More..."><div>еще 841</div></a></div>
    </section>

    <section class="steam-topup-container">
        <form action="#" name="steam-topup">
            <div class="steam-topup-inner">
                <div class="service-info">
                    <div class="steam-logo"><img src="/assets/icon/icon-steam.png" alt="Steam"></div>
                    <div class="service-text">
                        <div class="service-title-row">
                            <span class="service-title">Пополнение Steam</span>
                            <span class="badge">5%</span>
                        </div>
                        <button class="promo-btn" id="promo-btn">Ввести промокод <span>▾</span></button>
                        <input type="text" name="promocode" value="">
                    </div>
                </div>
                <div class="input-wrapper group-login">
                    <span class="input-icon">👤</span>
                    <input type="text" name="login" class="custom-input" placeholder="Логин Steam">
                    <span class="info-icon">i</span>
                </div>
            </div>
            <div class="steam-topup-inner">
                <div class="input-wrapper group-amount">
                    <span class="currency-badge">₽</span>
                    <div class="amount-field">
                        <label class="input-label">Сумма</label>
                        <input type="text" name="price" class="custom-input amount-input" value="500₽">
                    </div>
                    <div class="currency-switcher">
                        <button class="switch-btn active">$</button>
                        <button class="switch-btn">₸</button>
                        <button class="switch-btn">₽</button>
                    </div>
                </div>
                <input type="hidden" name="sku" value="STEAM-TOPUP-500">
                <button class="steam-pay-btn" id="steam-pay-btn">Оплатить <span id="price-btn">500₽</span></button>
            </div>
        </form>
    </section>

    <div class="categories-container">
        <h2 class="categories-title">Популярные товары</h2>
        <div class="tabs-wrapper">
            <button class="tab-btn active">
                <span class="tab-icon"><img src="/assets/icon/icon-donate.svg" alt="Donate"></span>
                <span class="tab-text">Донат</span>
            </button>
            <button class="tab-btn">
                <span class="tab-icon"><img src="/assets/icon/icon-subscribes.svg" alt="Subscribes"></span>
                <span class="tab-text">Подписки</span>
            </button>
            <button class="tab-btn">
                <span class="tab-icon"><img src="/assets/icon/icon-items.svg" alt="Items"></span>
                <span class="tab-text">Предметы</span>
            </button>
            <button class="tab-btn">
                <span class="tab-icon"><img src="/assets/icon/icon-accounts.svg" alt="Accounts"></span>
                <span class="tab-text">Аккаунты</span>
            </button>
            <button class="tab-btn">
                <span class="tab-icon"><img src="/assets/icon/icon-keys.svg" alt="Keys"></span>
                <span class="tab-text">Ключи</span>
            </button>
            <button class="tab-btn">
                <span class="tab-icon"><img src="/assets/icon/icon-game-valut.svg" alt="Game currency"></span>
                <span class="tab-text">Игровая валюта</span>
            </button>
            <button class="tab-btn">
                <span class="tab-icon"><img src="/assets/icon/icon-other.svg" alt="Other"></span>
                <span class="tab-text">Другое</span>
            </button>
        </div>
    </div>

    <section class="product-cards">
        <?php foreach ($products as $product): ?>
            <article class="product-card" data-sku="<?= htmlspecialchars($product['sku']) ?>">
                <div class="product-image"></div>
                <div class="product-body">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <div class="product-price">
                        <?= htmlspecialchars($product['price']) ?> <?php //echo htmlspecialchars($product['currency']) ?>₽
                        &nbsp;<span class="old-price">1990 ₽</span>
                    </div>
                    <button type="button" class="buy-btn" data-sku="<?= htmlspecialchars($product['sku']) ?>">Купить</button>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</div>
