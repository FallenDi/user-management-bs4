<?php


namespace webvimark\modules\UserManagement\components;

use webvimark\modules\UserManagement\UserManagementModule;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class Captcha
 * Класс для работы с капчей
 * Чтобы изменить криптографическую соль для капчи, отредактируйте свойство captchaCryptoSalt в UserManagmentModule.php
 * @package app\components
 */
class Captcha
{
    /**
     * @var string Хэш, полученный шифрованием с солью $salt. Сама соль присутствует в начале хэше
     */
    public $hash_with_salt = '';

    /**
     * @var string Хэш, полученный шифрованием с солью $salt. Соли в начале хэша нет
     */
    public $hash_without_salt = '';

    /**
     * @var string Полный путь до папки с изображениями капчи
     */
    public $path_captcha_dir = 'img/captcha/';

    /**
     * @var string Полный путь до файла созданного изображения
     */
    public $path_captcha_img = '';

    /**
     * @var int Время жизни файлов капчи в секундах (1 минута)
     */
    public $captcha_lifetime = 30;

    /**
     * @var int Ширина выходного изображения капчи
     */
    public $output_width = 280;

    /**
     * @var int Высота выходного изображения капчи
     */
    public $output_height = 100;


    function __construct() {
        // Очищаем старые файлы капчи перед генерацией новой
        $this->cleanOldCaptchaFiles();

        // 1. Генерируем код капчи
        // Получаем криптосоль
        $salt = Yii::$app->getModule('user-management')->captchaCryptoSalt;

        // 1.1. Устанавливаем символы, из которых будет составляться код капчи
        // $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';

        // Варианты символов без букв (о, O) и нулей; а также без букв l и I (эти символы в текущем
        // шрифте сливаются и сбивают с толку)
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ123456789abcdefghijkmnpqrstuvwxyz';
        // 1.2. Количество символов в капче
        $length = 4;
        // 1.3. Генерируем код
        $code = substr(str_shuffle($chars), 0, $length);

//        if (USE_SESSION) {
//            // 2a. Используем сессию
//            session_start();
//            $_SESSION['captcha'] =  crypt($code, '$1$itchief$7');
//            session_write_close();
//        } else {
//            // 2a. Используем куки (время действия 600 секунд)
//            $value = crypt($code, '$1$itchief$7');
//            $expires = time() + 600;
//            setcookie('captcha', $value, $expires, '/', 'test.ru', false, true);
//        }

        // 2.0 Генерируем хеш того кода, что изображен на картинке
        $this->hash_with_salt = crypt($code, $salt);
        $this->hash_without_salt = substr($this->hash_with_salt, strlen($salt));

        // 3. Генерируем изображение
        $captchaBgNumber = rand(1,3);
        // 3.1. Создаем новое изображение из файла
        $bg_image = imagecreatefrompng(Url::to('@user-management/files/captcha/captcha_bg_' . $captchaBgNumber . '.png'));
        // Получаем размеры исходного изображения
        $bg_width = imagesx($bg_image);
        $bg_height = imagesy($bg_image);
        // Создаем новое изображение нужного размера
        $image = imagecreatetruecolor($this->output_width, $this->output_height);

        // Выбираем случайную область для вырезки из фона
        $src_x = rand(0, $bg_width - $this->output_width);
        $src_y = rand(0, $bg_height - $this->output_height);

        // Копируем часть фонового изображения
        imagecopy(
            $image, $bg_image,
            0, 0, // Координаты в новом изображении
            $src_x, $src_y, // Координаты в исходном изображении
            $this->output_width, $this->output_height // Ширина и высота вырезаемой области
        );

        imagedestroy($bg_image); // Освобождаем память

        // 3.2 Устанавливаем размер шрифта в пунктах
        $size = 40;
        // 3.3. Создаём цвет, который будет использоваться в изображении
        if ($captchaBgNumber === 1) $color = imagecolorallocate($image, 45, 133, 137);
        if ($captchaBgNumber === 2) $color = imagecolorallocate($image, 58, 170, 207);
        if ($captchaBgNumber === 3) $color = imagecolorallocate($image, 213, 126, 173);
        $font = Url::to('@user-management/files/captcha/oswald.ttf');
        // 3.5 Задаём угол в градусах
        $angle = rand(-12, 12);
        // 3.6. Устанавливаем координаты точки для первого символа текста
        $x = rand(40, 100);
        $y = 64;

        // 3.7. Наносим текст на изображение
        for ($i = 0; $i < strlen($code); $i++) {
            imagefttext($image, $size, $angle, $x, $y, $color, $font, $code[$i]);
            $x+= rand(35, 50);
            $angle = rand(-12, 12);
        }

        // Генерируем уникальное имя файла
        $filename = uniqid('captcha_', true) . '.png';
        $this->path_captcha_img = $this->path_captcha_dir . $filename;

        // 3.8 Устанавливаем заголовки
//        header('Cache-Control: no-store, must-revalidate');
//        header('Expires: 0');
//        header('Content-Type: image/png');

        // 3.9. Выводим изображение
        imagepng($image, $this->path_captcha_img);

        // 3.10. Удаляем изображение
        imagedestroy($image);
    }

    /**
     * Удаляет старые файлы капчи
     */
    protected function cleanOldCaptchaFiles()
    {
        if (!file_exists($this->path_captcha_dir)) {
            return;
        }

        $files = glob($this->path_captcha_dir . 'captcha_*.png');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                // Удаляем файлы старше captcha_lifetime
                if ($now - filemtime($file) >= $this->captcha_lifetime) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Возвращает скрытый инпут с хешем текущего текста капчи. Разместите его в своей HTML-форме
     * @return string HTML код элемента
     */
    public function renderHiddenInput() {
        return "<input type='hidden' id='captcha-token' name='captcha-token' value='$this->hash_without_salt'>";
    }

    /**
     * Возвращает HTML код поля для ввода текста с капчи. Разместите его в своей HTML-форме
     * @return string HTML код элемента
     */
    public function renderVisInput() {
        return "<div class=\"form-label-group login-page__input-wrap field-loginform-username required\">
                    <input type=\"text\" id=\"captcha-input\" class=\"form-control\" name=\"captcha-input\"
                           autocomplete=\"off\" required=\"true\" aria-required=\"true\">
                    <label for=\"captcha-input\">Введите текст с картинки</label>
                </div>";
    }

    /**
     * Возвращает HTML код изображения с надписью капчи в блоке div. Разместите его в своей HTML-форме
     * @return string HTML код элемента
     */
    public function renderCaptchaImg() {
        return Html::img(
                    '@web/' . $this->getImagePath(),
                    ['class' => 'login-page__captcha', 'alt' => 'CAPTCHA']
                );
    }

    public function renderSubmitFormBtn($disabledClass = NULL) {
        return Html::submitButton(UserManagementModule::t('front', 'Войти'), ['disabled' => 'true',
                'class' => 'login-page__login-btn-disabled', 'data-disabled-class' => $disabledClass]);
    }

    /**
     * @return string Полный путь к созданному файлу - изображению с текстом капчи
     */
    public function getImagePath() {
        return $this->path_captcha_img;
    }
    
}