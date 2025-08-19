// ------------------------------------------------------------------------------------------------------------------------------
// ------------------------- Функции для поддержки работы модуля аутентификации (webwimark) -------------------------------------
// ------------------------------------------------------------------------------------------------------------------------------

const loginButton = document.querySelector('button[type="submit"]');

let disabledClassBtn = '';
if ('disabledClass' in loginButton.dataset) {
    disabledClassBtn = loginButton.dataset.disabledClass;
}

const captchaInput = document.querySelector('#captcha-input');
const captchaToken = document.querySelector('#captcha-token');

/**
 * Проверяет правильность капчи, отправляя запрос на сервер
 */
function checkCaptcha() {
    // Если длина введенной строки не равна 4, то кнопка автоматически блокируется
    if (captchaInput.value.length !== 4) {
        loginButton.classList.add(disabledClassBtn);
        loginButton.disabled = true;
        return;
    }

    // Запрос к серверу на проверку правильности ввода капчи
    let url = location.protocol + '//' + location.host + '/user-management/auth/check-captcha';
    let promise = fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json;charset=utf-8' },
        body: JSON.stringify({
            'captchaInput': captchaInput.value,
            'captchaToken': captchaToken.value
        })
    });

    promise.then(
        (response) => response.text()
            .then((response_text) => {
                if (response_text === '1') {
                    loginButton.classList.remove(disabledClassBtn);
                    loginButton.disabled = false;
                } else {
                    loginButton.classList.add(disabledClassBtn);
                    loginButton.disabled = true;
                }
            }));
}

if (captchaInput) captchaInput.addEventListener('input', checkCaptcha);

