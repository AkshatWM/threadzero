// 1. Insert >>postId into the reply textarea when a user clicks a post number or Quote link
function quotePost(postId) {
    const textarea = document.getElementById('reply-content');
    if (!textarea) return;

    const quoteTag = '>>' + postId + '\n';
    textarea.value += quoteTag;
    // Notify listeners so the live character counter stays in sync
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.focus();
}

// 2. Theme helpers. localStorage can throw when storage is blocked, so guard every access.
function isDarkTheme() {
    return document.documentElement.getAttribute('data-theme') === 'dark';
}

function storeTheme(theme) {
    try {
        localStorage.setItem('theme', theme);
    } catch (e) {
        // Ignore storage errors; the theme still applies for this page view
    }
}

// 3. Toggle between the Retro Light and Modern Dark themes
function toggleTheme() {
    const nextTheme = isDarkTheme() ? 'light' : 'dark';

    if (nextTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }

    storeTheme(nextTheme);
    updateThemeButtonText();
}

// Keep the navbar button label and ARIA state in sync with the active theme
function updateThemeButtonText() {
    const btn = document.getElementById('theme-toggle');
    if (!btn) return;

    const dark = isDarkTheme();
    btn.textContent = dark ? 'Light Mode' : 'Dark Mode';
    btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
}

// 4. Client-side form polish: live character counters & minimum length checks
document.addEventListener('DOMContentLoaded', function () {
    updateThemeButtonText();

    // Attach a live character counter to every long-form textarea
    const textareas = document.querySelectorAll('textarea[name="content"], textarea[name="bio"]');
    textareas.forEach(function (ta) {
        const maxLen = parseInt(ta.getAttribute('maxlength'), 10) || 5000;
        const counter = document.createElement('small');
        counter.className = 'char-counter';
        counter.textContent = ta.value.length + ' / ' + maxLen + ' chars';
        ta.insertAdjacentElement('afterend', counter);

        ta.addEventListener('input', function () {
            counter.textContent = ta.value.length + ' / ' + maxLen + ' chars';
        });
    });

    // Enforce a minimum trimmed length on thread and reply forms before submit
    const postForms = document.querySelectorAll('form');
    postForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const contentField = form.querySelector('textarea[name="content"]');
            if (contentField && contentField.value.trim().length < 2) {
                e.preventDefault();
                alert('Please enter at least 2 characters before submitting.');
                contentField.focus();
            }
        });
    });
});
