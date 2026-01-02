// assets/js/image-upload.js - Drag & Drop завантаження картинок

class ImageUploader {
    constructor(textareaSelector, dropzoneSelector) {
        this.textarea = document.querySelector(textareaSelector);
        this.dropzone = document.querySelector(dropzoneSelector);
        
        if (!this.textarea || !this.dropzone) {
            console.warn('ImageUploader: textarea or dropzone not found');
            return;
        }
        
        this.init();
    }
    
    init() {
        // Drag & Drop events
        this.dropzone.addEventListener('dragover', (e) => this.handleDragOver(e));
        this.dropzone.addEventListener('dragleave', (e) => this.handleDragLeave(e));
        this.dropzone.addEventListener('drop', (e) => this.handleDrop(e));
        
        // Paste event (Ctrl+V для вставки з буфера)
        this.textarea.addEventListener('paste', (e) => this.handlePaste(e));
        
        // Click to upload
        const uploadBtn = this.dropzone.querySelector('.upload-btn');
        if (uploadBtn) {
            uploadBtn.addEventListener('click', () => this.openFileDialog());
        }
    }
    
    handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        this.dropzone.classList.add('dragover');
    }
    
    handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        this.dropzone.classList.remove('dragover');
    }
    
    handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        this.dropzone.classList.remove('dragover');
        
        const files = Array.from(e.dataTransfer.files);
        const imageFiles = files.filter(file => file.type.startsWith('image/'));
        
        if (imageFiles.length > 0) {
            this.uploadFiles(imageFiles);
        } else {
            this.showError('Будь ласка, завантажуйте тільки зображення');
        }
    }
    
    handlePaste(e) {
        const items = Array.from(e.clipboardData.items);
        const imageItems = items.filter(item => item.type.startsWith('image/'));
        
        if (imageItems.length > 0) {
            e.preventDefault();
            const files = imageItems.map(item => item.getAsFile());
            this.uploadFiles(files);
        }
    }
    
    openFileDialog() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.multiple = true;
        
        input.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                this.uploadFiles(files);
            }
        });
        
        input.click();
    }
    
    async uploadFiles(files) {
        this.showProgress();
        
        for (const file of files) {
            try {
                const result = await this.uploadFile(file);
                // Використовуємо формат Neasden замість Markdown
                this.insertMarkdown(result.neasden || result.markdown);
            } catch (error) {
                this.showError(`Помилка завантаження ${file.name}: ${error.message}`);
            }
        }
        
        this.hideProgress();
    }
    
    async uploadFile(file) {
        const formData = new FormData();
        formData.append('image', file);
        
        const response = await fetch('/admin/upload_image.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'Upload failed');
        }
        
        return await response.json();
    }
    
    insertMarkdown(markdown) {
        const start = this.textarea.selectionStart;
        const end = this.textarea.selectionEnd;
        const text = this.textarea.value;
        
        // Вставляємо markdown в позицію курсора
        this.textarea.value = text.substring(0, start) + markdown + '\n' + text.substring(end);
        
        // Переміщуємо курсор після вставленого тексту
        const newPosition = start + markdown.length + 1;
        this.textarea.setSelectionRange(newPosition, newPosition);
        this.textarea.focus();
    }
    
    showProgress() {
        this.dropzone.classList.add('uploading');
        const progress = this.dropzone.querySelector('.upload-progress');
        if (progress) progress.style.display = 'block';
    }
    
    hideProgress() {
        this.dropzone.classList.remove('uploading');
        const progress = this.dropzone.querySelector('.upload-progress');
        if (progress) progress.style.display = 'none';
    }
    
    showError(message) {
        alert(message); // TODO: Красивіше сповіщення
        console.error(message);
    }
}

// Ініціалізація при завантаженні сторінки
document.addEventListener('DOMContentLoaded', () => {
    // Для inline редактора на сторінці поста
    if (document.querySelector('#postEdit')) {
        new ImageUploader('#content', '#imageDropzone');
    }
    
    // Для inline редактора нових постів на timeline
    if (document.querySelector('#newPostForm')) {
        new ImageUploader('#newPostContent', '#newPostDropzone');
    }
});
