export const updateSettings = (path, value, crsfValue) => {
        // No need to await this, it'll just happen
        fetch('/user/updatePreferences', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                credentials: 'same-origin',
                'X-CSRF-TOKEN': crsfValue
            },
            body: JSON.stringify({[path]: value})
        }).then(response => {
            if (!response.ok) {
                console.error('Error!', response.statusText);
            } else {
                console.debug('Success!');
            }
        });
    }