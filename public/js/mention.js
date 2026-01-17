document.addEventListener('DOMContentLoaded', () => {
    const textareas = document.querySelectorAll('textarea'); // Apply to all textareas

    textareas.forEach(textarea => {
        setupMention(textarea);
    });
});

function setupMention(textarea) {
    let mentionList = document.createElement('div');
    mentionList.className = 'mention-suggestions hidden';
    document.body.appendChild(mentionList);

    textarea.addEventListener('input', (e) => {
        const value = textarea.value;
        const cursorPosition = textarea.selectionStart;
        const textBeforeCursor = value.substring(0, cursorPosition);

        // Check if words end with @something
        const match = textBeforeCursor.match(/@(\w*)$/);

        if (match) {
            const query = match[1];
            if (query.length === 0) {
                // Suggest popular or empty state? For now, list nothing or recent
                hideSuggestions(mentionList);
                return;
            }

            fetchSuggestions(query, mentionList, textarea, cursorPosition, match[0]);
        } else {
            hideSuggestions(mentionList);
        }
    });

    // Hide on click outside
    document.addEventListener('click', (e) => {
        if (!mentionList.contains(e.target) && e.target !== textarea) {
            hideSuggestions(mentionList);
        }
    });

    textarea.addEventListener('blur', () => {
        // Delay hiding to allow click on suggestion
        setTimeout(() => hideSuggestions(mentionList), 200);
    });
}

function fetchSuggestions(query, mentionList, textarea, cursorPosition, matchText) {
    if (query.length < 1) return;

    console.log('Fetching suggestions for:', query);

    fetch(`/api/users/search?q=${query}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(users => {
            console.log('Users found:', users);
            // Temporary Debugging Alert
            if (users.length > 0) {
                // alert('Debug: Found ' + users.length + ' users matching "' + query + '"'); 
                showSuggestions(users, mentionList, textarea, cursorPosition, matchText);
            } else {
                hideSuggestions(mentionList);
            }
        })
        .catch(e => {
            console.error('Mention fetch error:', e);
        });
}

function showSuggestions(users, mentionList, textarea, cursorPosition, matchText) {
    mentionList.innerHTML = '';
    mentionList.classList.remove('hidden');

    // Position the dropdown near the textarea cursor
    // This is a simplified positioning (relative to textarea bottom-left usually)
    // For exact cursor coordinates, we'd need a mirrored div helper, but let's stick to relative for now.
    const rect = textarea.getBoundingClientRect();
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const scrollLeft = window.scrollX || document.documentElement.scrollLeft;

    // Better positioning? 
    // Quick Hack: Place it below the textarea for now. 
    // Implementing carat coordinates is complex without a library like 'textarea-caret'
    mentionList.style.top = (rect.bottom + scrollTop) + 'px';
    mentionList.style.left = (rect.left + scrollLeft) + 'px';
    mentionList.style.width = rect.width + 'px'; // Match textarea width

    users.forEach(user => {
        const item = document.createElement('div');
        item.className = 'mention-item';

        const avatarPath = user.avatar ? `/uploads/avatars/${user.avatar}` : '/images/default-avatar.png'; // Fallback logic needed in template usually, here in JS

        // Simple avatar placeholder if no image
        const avatarImg = user.avatar
            ? `<img src="/uploads/avatars/${user.avatar}" class="mention-avatar">`
            : `<div class="mention-avatar-placeholder">${user.nickname.charAt(0).toUpperCase()}</div>`;

        item.innerHTML = `
            ${avatarImg}
            <div class="mention-info">
                <span class="mention-nickname">${user.nickname}</span>
                <span class="mention-handle">@${user.nickname}</span>
            </div>
        `;

        item.addEventListener('click', () => {
            insertMention(user.nickname, textarea, cursorPosition, matchText);
            hideSuggestions(mentionList);
        });

        mentionList.appendChild(item);
    });
}

function insertMention(nickname, textarea, cursorPosition, matchText) {
    const value = textarea.value;
    const textBefore = value.substring(0, cursorPosition - matchText.length);
    const textAfter = value.substring(cursorPosition);

    // Insert: @nickname + space
    const newText = textBefore + '@' + nickname + ' ' + textAfter;
    textarea.value = newText;

    // Move cursor
    const newCursorPos = textBefore.length + nickname.length + 2; // +1 for @, +1 for space
    textarea.setSelectionRange(newCursorPos, newCursorPos);
    textarea.focus();
}

function hideSuggestions(mentionList) {
    mentionList.classList.add('hidden');
}
