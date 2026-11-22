// Фильтры каталога: при смене значения форма отправляется сама (кнопка «Показать» остаётся для клавиатуры без JS).
export class Filters {
    constructor(form) {
        form.addEventListener('change', (event) => {
            if (event.target.matches('input[type="radio"], select')) form.requestSubmit();
        });
    }
}
