class Form {
    form = document.querySelector('form');
    input = document.querySelector('input.input-text');
    inputLine = this.input.parentNode;
    button = this.form.querySelector('button');
    article = document.querySelector(".track-info");

    constructor() {
        this.form.onsubmit = e => this.formSubmit(e);
        this.button.onsubmit = e => this.form.submit();
    }

    formSubmit(e) {
        e.preventDefault();
        if (!this.checkForm()) {
            this.inputLine.classList.add('error');
            return false;
        }
        this.inputLine.classList.remove('error');
        this.form.classList.add('is-loading');

        fetch(`index.php?track_number=${this.input.value}`,)
            .then(response => response.text())
            .then(data => this.showHtml(data));
        return false;
    }

    showHtml(data) {
        this.form.classList.remove('is-loading');
        this.article.innerHTML = data;
        this.initEvents();
    }

    checkForm() {
        let value = this.input.value;
        return value.match(/^[A-Z]{2}\d{9}[A-Z]{2}$/m);
    }

    initEvents() {
        this._details = document.querySelector('article>.details');
        if (!this._details) return false;
        this._btn = this._details.querySelector('button');
        console.log(this._btn);
        this._btn.addEventListener('click', e => {
            this._details.classList.toggle('is-show');
            this._btn.innerText = (this._btn.innerText === 'Детально' ? 'Скрыть' : 'Детально');
        });
    }
}

const form = new Form();