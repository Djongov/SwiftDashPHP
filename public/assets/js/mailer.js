const tinyMCEOptions = {
    selector: 'textarea.tinymce',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
    tinycomments_mode: 'embedded',
    tinycomments_author: 'Djongov'
}

// Let's decide whether we should run dark mode
const darkMode = localStorage.getItem('color-theme');

if (darkMode === 'dark') {
    tinyMCEOptions.skin = "oxide-dark";
    tinyMCEOptions.content_css = "dark";
}

tinymce.init(tinyMCEOptions);

const tinyMceForm = document.getElementById('tinymce');

if (tinyMceForm) {
    tinyMceForm.addEventListener('submit', () => {
        tinymce.triggerSave();
    });
}