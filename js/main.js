class Form {
    form = document.querySelector('form');
    input = document.querySelector('input.input-text');
    inputLine = this.input.parentNode;
    button = this.form.querySelector('button');
    type = this.form.querySelector('input[name="type"]');
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
        if (this.type.value === 'php') {
            fetch(`index.php?track_number=${this.input.value}`,)
                .then(response => response.text())
                .then(data => this.showHtml(data));
        }
        return false;
    }

    showHtml(data) {
        this.form.classList.remove('is-loading');
        this.article.innerHTML = data;
    }

    checkForm() {
        let value = this.input.value;
        return value.match(/^[A-Z]{2}\d{9}[A-Z]{2}$/m);
    }
}

const form = new Form();