// Every form submits to Laravel. These enhancements do not decide validity.
for (const form of document.querySelectorAll('[data-category-filter]')) {
    const parent = form.querySelector('[data-parent-select]');
    const child = form.querySelector('[data-child-select]');
    if (!parent || !child) continue;

    const update = () => {
        const parentId = parent.value;
        const needsParent = child.hasAttribute('data-require-parent');
        let matches = 0;
        for (const option of child.querySelectorAll('[data-category]')) {
            const available = parentId ? option.dataset.category === parentId : !needsParent;
            option.disabled = !available;
            option.hidden = !available;
            if (available) matches += 1;
        }
        for (const group of child.querySelectorAll('optgroup')) {
            group.hidden = !Array.from(group.options ?? group.querySelectorAll('option')).some(option => !option.disabled);
        }
        if (child.selectedOptions[0]?.disabled) child.value = '';
        child.disabled = needsParent && (!parentId || !matches);
        if (needsParent) {
            child.options[0].textContent = !parentId ? 'Choose a category first' : (matches ? 'Choose a subcategory' : 'No subcategories in this category');
        }
    };
    update();
    parent.addEventListener('change', update);
}

const image = document.querySelector('[data-image-preview]');
const upload = document.querySelector('[data-image-input]');
if (image && upload) {
    const original = image.getAttribute('src');
    let preview;
    upload.addEventListener('change', () => {
        if (preview) URL.revokeObjectURL(preview);
        const file = upload.files?.[0];
        preview = file && /^image\/(png|jpeg|webp)$/.test(file.type) && file.size <= 3 * 1024 * 1024 ? URL.createObjectURL(file) : null;
        image.src = preview || original;
    });
    window.addEventListener('pagehide', () => { if (preview) URL.revokeObjectURL(preview); });
}

for (const control of document.querySelectorAll('[data-quantity]')) {
    const input = control.querySelector('input');
    for (const button of control.querySelectorAll('[data-step]')) {
        button.addEventListener('click', () => {
            const next = (Number.parseInt(input.value, 10) || 1) + Number(button.dataset.step);
            input.value = String(Math.max(Number(input.min), Math.min(Number(input.max), next)));
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }
    control.classList.add('has-steps');
}

for (const form of document.querySelectorAll('[data-confirm]')) {
    form.addEventListener('submit', event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
}

const busyButtons = new Map();
for (const form of document.querySelectorAll('form[data-busy]')) {
    form.addEventListener('submit', event => {
        const button = event.submitter || form.querySelector('button[type="submit"]');
        if (!button || event.defaultPrevented) return;
        if (form.dataset.submitting) {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = 'true';
        busyButtons.set(button, button.innerHTML);
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        if (button.dataset.busyLabel) button.textContent = button.dataset.busyLabel;
    });
}
window.addEventListener('pageshow', () => {
    for (const [button, content] of busyButtons) {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.innerHTML = content;
        if (button.form) delete button.form.dataset.submitting;
    }
    busyButtons.clear();
});

for (const menu of document.querySelectorAll('details[data-menu]')) {
    menu.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menu.open) {
            menu.open = false;
            menu.querySelector('summary').focus();
        }
    });
    document.addEventListener('click', event => {
        if (menu.open && !menu.contains(event.target)) menu.open = false;
    });
}
document.querySelector('[data-error-summary]')?.focus();
