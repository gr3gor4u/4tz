<?php
/**
 * Задача №3 - Поиск файлов с использованием регулярных выражений
 * 
 * Скрипт находит все файлы в папке /datafiles, имена которых состоят 
 * из цифр и букв латинского алфавита, имеют расширение ixt и выводит 
 * их имена, упорядоченные по имени.
 * 
 * @author Your Name
 * @version 1.0
 */

/**
 * Класс для поиска файлов по заданным критериям
 */
final class FileFinder
{
    /** @var string Путь к директории для поиска */
    private $directory = './datafiles';
    
    /** @var string Регулярное выражение для проверки имени файла */
    private $pattern = '/^[a-zA-Z0-9]+\.ixt$/';

    /**
     * Конструктор класса
     * Проверяет существование директории
     *
     * @access public
     * @throws Exception
     */
    public function __construct()
    {
        if (!is_dir($this->directory)) {
            if (!mkdir($this->directory, 0755, true)) {
                throw new Exception("Не удается создать директорию: {$this->directory}");
            }
            $this->createTestFiles();
        }
    }

    /**
     * Создает тестовые файлы для демонстрации работы скрипта
     *
     * @access private
     * @return void
     */
    private function createTestFiles(): void
    {
        $testFiles = [
            'file1.ixt',
            'document2.ixt', 
            'test123.ixt',
            'data456.ixt',
            'report789.ixt',
            'invalid-file.txt',  
            'файл.ixt',          
            'file with spaces.ixt', 
            'file@special.ixt', 
            '123456.ixt',
            'abcDEF.ixt',
            'test.ixt'
        ];

        foreach ($testFiles as $filename) {
            $filepath = $this->directory . '/' . $filename;
            file_put_contents($filepath, "Тестовое содержимое файла {$filename}");
        }

        echo "Созданы тестовые файлы в директории {$this->directory}\n";
    }

    /**
     * Проверяет, соответствует ли имя файла заданным критериям
     *
     * @access private
     * @param string $filename Имя файла для проверки
     * @return bool true если файл соответствует критериям, false в противном случае
     */
    private function isValidFilename(string $filename): bool
    {
        return preg_match($this->pattern, $filename) === 1;
    }

    /**
     * Находит все файлы, соответствующие заданным критериям
     *
     * @access public
     * @return array
     * @throws Exception 
     */
    public function findFiles(): array
    {
        $files = [];
        
        if (!is_readable($this->directory)) {
            throw new Exception("Директория {$this->directory} недоступна для чтения");
        }

        $handle = opendir($this->directory);
        if ($handle === false) {
            throw new Exception("Не удается открыть директорию {$this->directory}");
        }

        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filepath = $this->directory . '/' . $file;
            
            if (is_file($filepath) && $this->isValidFilename($file)) {
                $files[] = $file;
            }
        }

        closedir($handle);

        sort($files, SORT_STRING);

        return $files;
    }

    /**
     * Выводит найденные файлы на экран
     *
     * @access public
     * @return void
     */
    public function displayFiles(): void
    {
        try {
            $files = $this->findFiles();
            
            if (empty($files)) {
                echo "Файлы, соответствующие критериям, не найдены.\n";
                return;
            }

            echo "Найдено файлов: " . count($files) . "\n\n";
            echo "Список файлов (упорядоченных по имени):\n";
            echo str_repeat('-', 50) . "\n";
            
            foreach ($files as $index => $filename) {
                echo sprintf("%2d. %s\n", $index + 1, $filename);
            }
            
            echo str_repeat('-', 50) . "\n";
            
        } catch (Exception $e) {
            echo "Ошибка при поиске файлов: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Показывает подробную информацию о регулярном выражении
     *
     * @access public
     * @return void
     */
    public function showPatternInfo(): void
    {
        echo "=== ИНФОРМАЦИЯ О РЕГУЛЯРНОМ ВЫРАЖЕНИИ ===\n";
        echo "Паттерн: {$this->pattern}\n";
        echo "Описание:\n";
        echo "- ^ - начало строки\n";
        echo "- [a-zA-Z0-9]+ - одна или более букв латинского алфавита или цифр\n";
        echo "- \\.ixt - точка и расширение 'ixt'\n";
        echo "- $ - конец строки\n\n";
        
        echo "Примеры валидных имен файлов:\n";
        echo "- file1.ixt\n";
        echo "- document123.ixt\n";
        echo "- abcDEF.ixt\n";
        echo "- 123456.ixt\n\n";
        
        echo "Примеры невалидных имен файлов:\n";
        echo "- file.txt (неправильное расширение)\n";
        echo "- файл.ixt (кириллица)\n";
        echo "- file-name.ixt (дефис)\n";
        echo "- file name.ixt (пробелы)\n";
        echo "- file@name.ixt (спецсимволы)\n";
    }
}

try {
    echo "=== Задача №3 - Поиск файлов с регулярными выражениями ===\n\n";
    
    $finder = new FileFinder();
    
    $finder->showPatternInfo();
    
    echo "\n";
    
    $finder->displayFiles();
    
} catch (Exception $e) {
    echo "Критическая ошибка: " . $e->getMessage() . "\n";
} 
