<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задача №4 - Анимированные кнопки с jQuery</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 30px 0;
        }
        
        .btn {
            padding: 15px 30px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            min-width: 120px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:nth-child(1) {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
        }
        
        .btn:nth-child(2) {
            background: linear-gradient(45deg, #4834d4, #686de0);
        }
        
        .btn:nth-child(3) {
            background: linear-gradient(45deg, #00b894, #00cec9);
        }
        
        h1 {
            color: #2d3436;
            margin-bottom: 20px;
        }
        
        .description {
            color: #636e72;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .status {
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Задача №4 - Анимированные кнопки</h1>
        <p class="description">
            Нажмите на любую кнопку, чтобы изменить порядок их расположения.<br>
            Кнопки будут циклически перемещаться в следующем порядке: 1-2-3 → 2-3-1 → 3-1-2 → 1-2-3
        </p>
        
        <div class="button-container" id="buttonContainer">
            <button class="btn" data-number="1">1</button>
            <button class="btn" data-number="2">2</button>
            <button class="btn" data-number="3">3</button>
        </div>
        
        <div class="status" id="status">
            Текущий порядок: 1, 2, 3
        </div>
    </div>

    <script>
        /**
         * Класс для управления анимацией кнопок
         * 
         * @class ButtonAnimator
         * @author Your Name
         * @version 1.0
         */
        class ButtonAnimator {
            /**
             * Конструктор класса
             * Инициализирует обработчики событий
             * 
             * @constructor
             */
            constructor() {
                this.currentOrder = [1, 2, 3];
                this.orders = [
                    [1, 2, 3],
                    [2, 3, 1],
                    [3, 1, 2]
                ];
                this.currentIndex = 0;
                
                this.init();
            }
            
            /**
             * Инициализирует обработчики событий для кнопок
             * 
             * @access private
             * @return void
             */
            init() {
                this.bindClickHandlers();
            }
            
            /**
             * Привязывает обработчики кликов к кнопкам
             * 
             * @access private
             * @return void
             */
            bindClickHandlers() {
                $('.btn').off('click').on('click', (e) => {
                    this.animateButtons();
                });
            }
            
            /**
             * Анимирует перемещение кнопок
             * 
             * @access private
             * @return void
             */
            animateButtons() {
                this.currentIndex = (this.currentIndex + 1) % this.orders.length;
                this.currentOrder = [...this.orders[this.currentIndex]];
                
                const buttons = $('.btn');
                const container = $('#buttonContainer');
                
                const tempButtons = [];
                buttons.each((index, button) => {
                    const $button = $(button);
                    const clone = $button.clone();
                    const position = $button.offset();
                    
                    clone.css({
                        position: 'absolute',
                        left: position.left,
                        top: position.top,
                        zIndex: 1000
                    });
                    
                    $('body').append(clone);
                    tempButtons.push(clone);
                });
                
                buttons.hide();
                
                const promises = [];
                const newPositions = [];
                
                buttons.each((index, button) => {
                    const $button = $(button);
                    const newIndex = this.currentOrder.indexOf(index + 1);
                    const newButton = buttons.eq(newIndex);
                    const newPosition = newButton.offset();
                    newPositions.push(newPosition);
                });
                
                tempButtons.forEach((tempButton, index) => {
                    const newPosition = newPositions[index];
                    const promise = new Promise((resolve) => {
                        tempButton.animate({
                            left: newPosition.left,
                            top: newPosition.top
                        }, 500, 'easeInOutQuart', () => {
                            tempButton.remove();
                            resolve();
                        });
                    });
                    promises.push(promise);
                });
                
                Promise.all(promises).then(() => {
                    this.reorderButtons();
                    buttons.show();
                    this.bindClickHandlers(); // Перепривязываем обработчики
                    this.updateStatus();
                });
            }
            
            /**
             * Переупорядочивает кнопки в DOM
             * 
             * @access private
             * @return void
             */
            reorderButtons() {
                const container = $('#buttonContainer');
                const buttons = $('.btn');
                
                const newOrder = [];
                this.currentOrder.forEach(number => {
                    const button = buttons.filter(`[data-number="${number}"]`);
                    newOrder.push(button);
                });
                
                container.empty();
                newOrder.forEach(button => {
                    container.append(button);
                });
            }
            
            /**
             * Обновляет статус отображения
             * 
             * @access private
             * @return void
             */
            updateStatus() {
                const statusText = `Текущий порядок: ${this.currentOrder.join(', ')}`;
                $('#status').text(statusText);
            }
        }
        
        /**
         * Инициализация приложения после загрузки DOM
         */
        $(document).ready(function() {
            $.easing.easeInOutQuart = function (x, t, b, c, d) {
                if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
                return -c/2 * ((t-=2)*t*t*t - 2) + b;
            };
            
            new ButtonAnimator();
        });
    </script>
</body>
</html> 