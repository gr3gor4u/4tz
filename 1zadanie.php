<?php
/**
 * Класс Init для работы с базой данных MySQL
 * 
 * Финальный класс, от которого нельзя сделать наследника.
 * Создает таблицу test, заполняет её случайными данными и предоставляет
 * метод для выборки данных по определенным критериям.
 * 
 * @final
 * @author Your Name
 * @version 1.0
 */
final class Init
{
    /** @var string Хост базы данных */
    private $host = '127.0.0.1';
    
    /** @var int Порт базы данных */
    private $port = 3306;
    
    /** @var string Имя пользователя базы данных */
    private $username = 'root';
    
    /** @var string Пароль базы данных */
    private $password = 'grisha2003';
    
    /** @var string Название базы данных */
    private $database = 'testdb';
    
    /** @var PDO|null Объект соединения с базой данных */
    private $pdo = null;

    /**
     * Конструктор класса
     * Создает таблицу test и заполняет ее случайными данными.
     *
     * @access public
     * @throws Exception Если не удается подключиться к базе данных
     */
    public function __construct()
    {
        try {
            $this->create();
            $this->fill();
        } catch (Exception $e) {
            throw new Exception("Ошибка инициализации: " . $e->getMessage());
        }
    }

    /**
     * Создает таблицу test с 5 полями: id, name, value, result, created_at.
     * Доступен только для методов класса.
     * 
     * @access private
     * @return void
     * @throws Exception Если не удается создать таблицу
     */
    private function create(): void
    {
        $this->connect();

        $sql = "CREATE TABLE IF NOT EXISTS test (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            value INT NOT NULL,
            result ENUM('normal', 'success', 'failed', 'pending') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if ($this->pdo->exec($sql) !== false) {
            echo "Таблица test успешно создана\n";
        } else {
            throw new Exception("Ошибка создания таблицы: " . implode(", ", $this->pdo->errorInfo()));
        }
    }

    /**
     * Заполняет таблицу test случайными данными.
     * Доступен только для методов класса.
     *
     * @access private
     * @return void
     * @throws Exception Если не удается заполнить таблицу
     */
    private function fill(): void
    {
        $this->connect();
        
        // Очищаем таблицу перед заполнением
        $this->pdo->exec("TRUNCATE TABLE test");
        
        $numRows = 10;
        $results = ['normal', 'success', 'failed', 'pending'];
        $names = ['Продукт A', 'Продукт B', 'Продукт C', 'Продукт D', 'Продукт E'];

        $stmt = $this->pdo->prepare("INSERT INTO test (name, value, result) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . implode(", ", $this->pdo->errorInfo()));
        }

        for ($i = 0; $i < $numRows; $i++) {
            $name = $names[array_rand($names)] . ' ' . ($i + 1);
            $value = rand(1, 1000);
            $result = $results[array_rand($results)];
            
            if (!$stmt->execute([$name, $value, $result])) {
                throw new Exception("Ошибка вставки данных: " . implode(", ", $stmt->errorInfo()));
            }
        }

        echo "Таблица test заполнена случайными данными\n";
    }

    /**
     * Устанавливает соединение с базой данных MySQL.
     *
     * @access private
     * @return void
     * @throws Exception Если не удается подключиться к базе данных
     */
    private function connect(): void
    {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
                $this->pdo = new PDO($dsn, $this->username, $this->password);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new Exception("Ошибка подключения к базе данных: " . $e->getMessage());
            }
        }
    }

    /**
     * Выбирает данные из таблицы test, где поле result имеет значение 'normal' или 'success'.
     * Доступен извне класса.
     *
     * @access public
     * @return array|null Массив с данными или null, если произошла ошибка
     * @throws Exception Если не удается выполнить запрос
     */
    public function get(): ?array
    {
        try {
            $this->connect();
            
            $sql = "SELECT * FROM test WHERE result IN ('normal', 'success') ORDER BY id";
            $stmt = $this->pdo->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Ошибка подготовки запроса: " . implode(", ", $this->pdo->errorInfo()));
            }

            if (!$stmt->execute()) {
                throw new Exception("Ошибка выполнения запроса: " . implode(", ", $stmt->errorInfo()));
            }

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
            
        } catch (Exception $e) {
            echo "Ошибка при получении данных: " . $e->getMessage() . "\n";
            return null;
        }
    }

    /**
     * Деструктор класса - закрывает соединение с базой данных
     */
    public function __destruct()
    {
        $this->pdo = null;
    }
}

try {
    echo "=== Задача №1 - Класс Init ===\n\n";
    
    $init = new Init();
    $data = $init->get();

    if ($data) {
        echo "Данные из таблицы test (result = 'normal' или 'success'):\n";
        echo "Найдено записей: " . count($data) . "\n\n";
        
        foreach ($data as $row) {
            echo "ID: {$row['id']}, Имя: {$row['name']}, Значение: {$row['value']}, Результат: {$row['result']}, Дата: {$row['created_at']}\n";
        }
    } else {
        echo "Данные не найдены или произошла ошибка.\n";
    }
    
} catch (Exception $e) {
    echo "Критическая ошибка: " . $e->getMessage() . "\n";
    echo "Убедитесь, что MySQL сервер запущен и доступен по адресу 127.0.0.1:3306\n";
    echo "Проверьте правильность логина и пароля для подключения к базе данных\n";
}