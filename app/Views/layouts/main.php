<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Shop</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="top-header">
        <div class="container header-inner">
            <button type="button" class="catalog-btn" id="catalogBtn" aria-expanded="false" aria-controls="catalogMenu">
                <img src="/assets/icon/icon-catalog.svg" alt="" class="catalog-btn-icon">
                <span>Каталог</span>
            </button>

            <div class="header-search">
                <input type="search" class="header-search-input" placeholder="Игра, приложение или услуга..." aria-label="Поиск по каталогу">
                <button type="button" class="header-search-favorite-btn" aria-label="Избранное">
                    <img src="/assets/icon/icon-favorite.svg" alt="Favorite" class="search-favorite-icon">
                </button>
                <button type="button" class="header-search-btn" aria-label="Поиск">
                    <img src="/assets/icon/icon-search.svg" alt="Search" class="search-icon">
                </button>
            </div>

            <nav class="user-actions" aria-label="Войти">
                <button type="button" class="user-action-btn" title="Войти" aria-label="Войти">
                    <img src="/assets/icon/icon-user.svg" alt="Login" class="user-icon">
                </button>
            </nav>

            <nav class="catalog-menu" id="catalogMenu" aria-label="Каталог">
                <ul class="level-1">
                    <li class="active"><a href="#">Игры и игровые сервисы&nbsp;<span>&rsaquo;</span></a></li>
                    <li><a href="#">Игровые ценности&nbsp;<span>&rsaquo;</span></a></li>
                    <li><a href="#">Мобильные игры&nbsp;<span>&rsaquo;</span></a></li>
                    <li><a href="#">Сервисы и соцсети&nbsp;<span>&rsaquo;</span></a></li>
                    <li><a href="#">Программы&nbsp;<span>&rsaquo;</span></a></li>
                </ul>
                <ul class="grid-container level-2">
                    <li class="grid-item">
                        <ul>
                            <li><a href="#"><strong>Steam&nbsp;<span>&rsaquo;</span></strong></a></li>
                            <li><a href="#">Игры и DLC</a></li>
                            <li><a href="#">Пополнение баланса</a></li>
                            <li><a href="#">Подарочные карты</a></li>
                            <li><a href="#">Коллекционные карточки</a></li>
                            <li><a href="#">Смена региона</a></li>
                        </ul>
                    </li>
                    <li class="grid-item">
                        <ul>
                            <li><a href="#"><strong>PlayStation&nbsp;<span>&rsaquo;</span></strong></a></li>
                            <li><a href="#">Игры и DLC</a></li>
                            <li><a href="#">Пополнение баланса</a></li>
                            <li><a href="#">Новые аккаунты</a></li>
                            <li><a href="#">PS Plus</a></li>
                            <li><a href="#">EA Play</a></li>
                        </ul>
                    </li>
                    <li class="grid-item">
                        <ul>
                            <li><a href="#"><strong>Xbox&nbsp;<span>&rsaquo;</span></strong></a></li>
                            <li><a href="#">Игры и DLC</a></li>
                            <li><a href="#">Пополнение баланса</a></li>
                            <li><a href="#">Новые аккаунты</a></li>
                            <li><a href="#">Xbox Game Pass</a></li>
                            <li><a href="#">Услуги</a></li>
                        </ul>
                    </li>
                    <li class="grid-item">
                        <ul>
                            <li><a href="#"><strong>Nintendo&nbsp;<span>&rsaquo;</span></strong></a></li>
                            <li><a href="#">Игры и DLC</a></li>
                            <li><a href="#">Подарочные карты</a></li>
                            <li><a href="#">Новые аккаунты</a></li>
                            <li><a href="#">NS Online</a></li>
                        </ul>
                    </li>
                    <li class="grid-item">
                        <ul>
                            <li><a href="#"><strong>Battle.net&nbsp;<span>&rsaquo;</span></strong></a></li>
                            <li><a href="#">World of Warcraft</a></li>
                            <li><a href="#">Подарочные карты</a></li>
                            <li><a href="#">Прямое пополнение</a></li>
                            <li><a href="#">Новые аккаунты</a></li>
                            <li><a href="#">Смена региона</a></li>
                        </ul>
                    </li>
                    <li class="grid-item">
                        <ul>
                            <li><a href="#"><strong>Подборки&nbsp;<span>&rsaquo;</span></strong></a></li>
                            <li><a href="#">Скидки 90%</a></li>
                            <li><a href="#">Популярные издатели</a></li>
                            <li><a href="#">Лучшие серии игр</a></li>
                            <li><a href="#">Steam Deck</a></li>
                            <li><a href="#">Bundle-наборы</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <?= $content ?? '' ?>

    <script src="/js/app.js"></script>
</body>
</html>
