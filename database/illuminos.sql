-- =========================================================
-- Illuminos — онлайн-кинотеатр (v3)
-- =========================================================
CREATE DATABASE IF NOT EXISTS illuminos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE illuminos;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    patronymic VARCHAR(50) DEFAULT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','subscriber','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('film','serial') NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    year SMALLINT,
    rating DECIMAL(3,1) DEFAULT 0,
    trailer_url VARCHAR(255) DEFAULT NULL,
    is_new TINYINT(1) DEFAULT 0,
    is_popular TINYINT(1) DEFAULT 0,
    genre_id INT,
    FOREIGN KEY (genre_id) REFERENCES genres(id)
);

-- Персоны: режиссёры и актёры в одной таблице
CREATE TABLE people (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    bio TEXT DEFAULT NULL
);

-- Связь контента и персон с ролью
CREATE TABLE content_people (
    content_id INT NOT NULL,
    person_id  INT NOT NULL,
    role ENUM('director','actor') NOT NULL,
    PRIMARY KEY (content_id, person_id, role),
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    FOREIGN KEY (person_id)  REFERENCES people(id)  ON DELETE CASCADE
);

CREATE TABLE seasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT NOT NULL,
    number INT NOT NULL,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

CREATE TABLE episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    number INT NOT NULL,
    title VARCHAR(150),
    duration_min INT DEFAULT 45,
    FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE
);

CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_fav (user_id, content_id),
    FOREIGN KEY (user_id)    REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

CREATE TABLE watch_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
);

CREATE TABLE tariffs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL,
    description VARCHAR(255)
);

-- Платежи. status='new' — ожидает оплаты через /checkout.php.
-- payment_method заполняется при подтверждении.
-- old_tariff_id != NULL — это доплата при повышении тарифа.
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tariff_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('new','paid','cancelled') DEFAULT 'new',
    payment_method VARCHAR(20) DEFAULT NULL,
    old_tariff_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id)
);

CREATE TABLE user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tariff_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id)
);

-- ---------- Жанры ----------
INSERT INTO genres (id, name) VALUES
(1,'Драма'),(2,'Комедия'),(3,'Ужасы'),(4,'Фантастика'),(5,'Боевик'),(6,'Мелодрама');

-- ---------- Тарифы ----------
INSERT INTO tariffs (id, name, price, duration_days, description) VALUES
(1,'Базовый',  299.00,30,'SD-качество, 1 устройство, реклама'),
(2,'Стандарт', 499.00,30,'Full HD, 2 устройства, без рекламы'),
(3,'Премиум',  899.00,30,'4K HDR, 4 устройства, без рекламы, ранний доступ');

-- ---------- Персоны ----------
INSERT INTO people (id, name) VALUES
(1,'А. Волков'),(2,'К. Орлова'),(3,'О. Петров'),(4,'М. Хан'),(5,'Р. Ким'),
(6,'Л. Мрачная'),(7,'Дж. Райт'),(8,'В. Стэн'),(9,'Г. Солнцев'),(10,'И. Нойман'),
(11,'А. Северов'),(12,'Е. Космос'),(13,'П. Волна'),
(14,'И. Смирнова'),(15,'П. Гранин'),(16,'М. Ли'),(17,'Д. Меньшов'),(18,'А. Радова'),
(19,'С. Бурунов'),(20,'Л. Гришаева'),(21,'Е. Королёва'),(22,'Т. Аксёнов'),
(23,'А. Соколов'),(24,'В. Тёрнер'),(25,'Н. Ефремова'),(26,'Г. Даль'),
(27,'Э. Кларк'),(28,'Х. Ндойе'),(29,'Ю. Тарасова'),(30,'М. Коул'),(31,'А. Берг'),
(32,'П. Мирова'),(33,'К. Львов'),(34,'С. Вельц'),(35,'А. Крамер'),
(36,'О. Ветрова'),(37,'Р. Волк'),(38,'И. Петрова'),(39,'К. Морозова'),
(40,'Д. Снегирёв'),(41,'Л. Морская'),(42,'Г. Волнов');

-- ---------- Контент (22 шт.) ----------
INSERT INTO content (id, type, title, description, year, rating, trailer_url, is_new, is_popular, genre_id) VALUES
(1,'film','Небесный код','Программист обнаруживает в коде спутника сообщение от внеземной цивилизации.',2026,8.4,'https://www.youtube.com/embed/dQw4w9WgXcQ',1,1,4),
(2,'film','Тишина города','Детектив расследует исчезновение музыканта в ночном городе.',2025,7.8,'',1,1,1),
(3,'film','Смех сквозь стены','Комедия о соседях, случайно запустивших подпольное радио.',2024,7.1,'',0,1,2),
(4,'film','Северный свет','Драма о встрече полярника и художницы в закрытом посёлке.',2026,8.0,'',1,0,1),
(5,'film','Протокол «Ночь»','Киберпанк-боевик о группе хакеров, взломавших корпоративную сеть.',2025,7.5,'',1,0,5),
(6,'film','Дом у озера','Мистический хоррор о доме, который помнит всех жильцов.',2024,6.9,'',0,0,3),
(7,'serial','Хроники Лумы','Фантастический сериал о колонистах планеты с двумя солнцами.',2026,8.9,'https://www.youtube.com/embed/dQw4w9WgXcQ',1,1,4),
(8,'serial','Отдел 13','Процедурал о следователях, раскрывающих «невозможные» преступления.',2025,8.2,'',1,1,5),
(9,'serial','Кафе «Утро»','Ситком о жизни небольшого кафе и его постоянных посетителей.',2024,7.6,'',0,1,2),
(10,'serial','Шёпот сна','Психологический триллер о сомнологе, читающей чужие сны.',2026,8.6,'',1,0,3),
(11,'film','Последний сеанс','Старый кинотеатр становится последним приютом для людей, которым больше некуда идти.',2023,7.4,'',0,1,1),
(12,'film','Красный горизонт','Экипаж орбитальной станции теряет связь с Землёй и находит следы чужой экспедиции.',2024,7.9,'',0,1,4),
(13,'film','Ночной звонок','Женщина получает звонки из дома, в котором она больше не живёт.',2025,6.8,'',1,0,3),
(14,'film','Без правил','Бывший агент ввязывается в войну двух корпораций за новый вид оружия.',2026,7.7,'',1,0,5),
(15,'film','Письма с юга','Двое встречаются на железнодорожной станции и меняют всю свою жизнь.',2025,8.1,'',1,0,1),
(16,'film','Смешные люди','Сотрудники офиса решают стать стендап-комиками, чтобы не потерять работу.',2025,6.9,'',0,0,2),
(17,'film','Тёмный лес','Группа студентов отправляется в лес и находит там дом, которого нет на карте.',2024,7.0,'',0,0,3),
(18,'film','Звёздная пыль','Мальчик с Земли и девочка с Марса обмениваются письмами через спутник.',2026,8.3,'',1,1,4),
(19,'serial','Лаборатория Х','Секретный институт исследует аномалии — и каждая из них выходит из-под контроля.',2026,8.4,'',1,1,5),
(20,'serial','Тихий океан','Семья переезжает в рыбацкий посёлок и раскрывает тайну старого маяка.',2025,7.9,'',0,0,1),
(21,'serial','Повара','Ситком о команде маленького ресторана, которая ссорится и мирится каждый вечер.',2025,7.3,'',0,1,2),
(22,'serial','Сны разума','Триллер о нейрохирурге, который научился входить в сны своих пациентов.',2026,8.7,'',1,1,3);

-- ---------- Связи контент ↔ персона ----------
INSERT INTO content_people (content_id, person_id, role) VALUES
(1,1,'director'),(1,14,'actor'),(1,15,'actor'),(1,16,'actor'),
(2,2,'director'),(2,17,'actor'),(2,18,'actor'),
(3,3,'director'),(3,19,'actor'),(3,20,'actor'),
(4,4,'director'),(4,21,'actor'),(4,22,'actor'),
(5,5,'director'),(5,23,'actor'),(5,24,'actor'),
(6,6,'director'),(6,25,'actor'),(6,26,'actor'),
(7,7,'director'),(7,27,'actor'),(7,28,'actor'),(7,29,'actor'),
(8,8,'director'),(8,30,'actor'),(8,31,'actor'),
(9,9,'director'),(9,32,'actor'),(9,33,'actor'),
(10,10,'director'),(10,34,'actor'),(10,35,'actor'),
(11,2,'director'),(11,17,'actor'),(11,36,'actor'),
(12,1,'director'),(12,14,'actor'),(12,15,'actor'),
(13,6,'director'),(13,25,'actor'),(13,37,'actor'),
(14,5,'director'),(14,23,'actor'),(14,24,'actor'),
(15,4,'director'),(15,21,'actor'),(15,22,'actor'),
(16,3,'director'),(16,19,'actor'),(16,20,'actor'),(16,38,'actor'),
(17,11,'director'),(17,39,'actor'),(17,40,'actor'),
(18,12,'director'),(18,29,'actor'),(18,27,'actor'),
(19,8,'director'),(19,30,'actor'),(19,31,'actor'),
(20,13,'director'),(20,41,'actor'),(20,42,'actor'),
(21,9,'director'),(21,32,'actor'),(21,33,'actor'),
(22,10,'director'),(22,34,'actor'),(22,35,'actor');

-- ---------- Сезоны / эпизоды ----------
INSERT INTO seasons (id, content_id, number) VALUES
(1,7,1),(2,7,2),(3,8,1),(4,8,2),(5,9,1),(6,10,1),
(7,19,1),(8,20,1),(9,21,1),(10,22,1),(11,22,2);

INSERT INTO episodes (id, season_id, number, title, duration_min) VALUES
(1,1,1,'Прибытие',52),(2,1,2,'Двойной рассвет',49),(3,1,3,'Пыльные бури',55),
(4,2,1,'Новая орбита',58),(5,2,2,'Сигнал тишины',54),
(6,3,1,'Холодное дело',46),(7,3,2,'Отпечаток',44),(8,3,3,'Седьмая дверь',47),
(9,4,1,'Обратный отсчёт',49),(10,4,2,'Чёрный ящик',51),
(11,5,1,'Первый кофе',24),(12,5,2,'Сироп для всех',23),(13,5,3,'Закрытый вечер',26),
(14,6,1,'Фаза один',50),(15,6,2,'Город спит',48),(16,6,3,'Пробуждение',53),
(17,7,1,'Стеклянный коридор',47),(18,7,2,'Пятый образец',51),(19,7,3,'Карантин',53),
(20,8,1,'Приезд',45),(21,8,2,'Маяк',49),(22,8,3,'Шторм',52),(23,8,4,'Свет',55),
(24,9,1,'Открытие',24),(25,9,2,'Первый заказ',22),(26,9,3,'Критик',25),(27,9,4,'Новый шеф',26),
(28,10,1,'Первый сон',48),(29,10,2,'Кошмар',50),(30,10,3,'Пробуждение',52),
(31,11,1,'Возвращение',47),(32,11,2,'Двойник',49),(33,11,3,'Финальный сон',54);