# Устранение ошибок 500 после установки

## Проблема

После установки через https://github.com/Gr0v4222/tgui_install появляются ошибки 500 при обращении к API:
- `/api/apicheck/database/?update=0`
- `/api/auth/singin/`

## Причины возникновения

### 1. Проблемы с подключением к базе данных
**Симптомы**: Ошибка 500, в логах `Database connection failed`

**Решение**:
```bash
# Проверьте, что MySQL запущен
sudo systemctl status mysql

# Проверьте подключение к базе данных
mysql -u tgui_user -p -h localhost
# Введите пароль из config.php

# Проверьте существование баз данных
mysql -u tgui_user -p -e "SHOW DATABASES LIKE 'tgui%';"
```

### 2. Недостаточные права пользователя базы данных
**Симптомы**: Ошибка при создании таблиц

**Решение**:
```sql
GRANT ALL PRIVILEGES ON tgui.* TO 'tgui_user'@'localhost';
GRANT ALL PRIVILEGES ON tgui_log.* TO 'tgui_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Отсутствует PHP MySQL расширение
**Симптомы**: `Class 'PDO' not found` или `Driver [mysql] not supported`

**Решение**:
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql php-pdo
sudo systemctl restart apache2

# Проверка
php -m | grep -i mysql
php -m | grep -i pdo
```

### 4. Неправильные права на файлы
**Симптомы**: Ошибки доступа к файлам

**Решение**:
```bash
cd /opt/tacacsgui
sudo chown -R www-data:www-data web/
sudo chmod 755 web/
sudo chmod 640 web/api/config.php
```

### 5. Composer зависимости не установлены
**Симптомы**: `Class not found` ошибки

**Решение**:
```bash
cd /opt/tacacsgui/web/api
composer install --no-dev
```

## Диагностика

### 1. Проверьте логи Apache
```bash
sudo tail -f /var/log/apache2/error.log
```

### 2. Проверьте логи PHP
```bash
sudo tail -f /var/log/php*.log
```

### 3. Включите отображение ошибок PHP (только для отладки!)
```bash
# В /etc/php/8.x/apache2/php.ini
display_errors = On
error_reporting = E_ALL

sudo systemctl restart apache2
```

**⚠️ Важно**: Отключите `display_errors` после устранения проблемы!

### 4. Проверьте config.php
```bash
cat /opt/tacacsgui/web/api/config.php
```

Убедитесь, что:
- Пароль базы данных указан правильно
- Имена баз данных: `tgui` и `tgui_log`
- Пользователь: `tgui_user`
- Хост: `localhost`

### 5. Тест подключения к базе данных
Создайте временный файл для теста:

```bash
cat > /tmp/test_db.php << 'EOF'
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tgui');
define('DB_USER', 'tgui_user');
define('DB_PASSWORD', 'ВАШ_ПАРОЛЬ_ИЗ_CONFIG'); // Замените на реальный пароль

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASSWORD
    );
    echo "✅ Подключение к базе данных успешно!\n";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Найдено таблиц: " . count($tables) . "\n";
    
} catch (PDOException $e) {
    echo "❌ Ошибка подключения: " . $e->getMessage() . "\n";
}
EOF

php /tmp/test_db.php
rm /tmp/test_db.php
```

## Исправления в коде

В последнем обновлении добавлена улучшенная обработка ошибок:

### 1. Проверка подключения к базе данных
Файл: `web/api/bootstrap/app.php`
- Добавлена проверка подключения к БД при старте
- При ошибке возвращается понятное сообщение в JSON

### 2. Обработка ошибок в API endpoints
Файлы:
- `web/api/app/Controllers/APIChecker/APICheckerCtrl.php`
- `web/api/app/Controllers/Auth/AuthController.php`

Добавлены try-catch блоки для:
- Выполнения системных команд
- Проверки существования таблиц
- Создания таблиц

## Повторная установка

Если ничего не помогло, попробуйте переустановить:

```bash
cd /opt
sudo rm -rf tacacsgui
git clone https://github.com/tacacsgui/tgui_install.git
cd tgui_install
sudo bash tgui_install.sh
```

## Получение помощи

Если проблема не решена, создайте issue на GitHub с:

1. **Вывод логов Apache**:
```bash
sudo tail -100 /var/log/apache2/error.log
```

2. **Версии ПО**:
```bash
php -v
mysql --version
apache2 -v
```

3. **Проверка PHP расширений**:
```bash
php -m | grep -E 'mysql|pdo|json'
```

4. **Содержимое config.php** (без пароля!):
```bash
grep -v PASSWORD /opt/tacacsgui/web/api/config.php
```
