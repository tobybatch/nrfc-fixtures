import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        currentRoute: String,
        routeParams: Object,
        csrf: String,
    };
    static targets = ['content', 'showButton', 'hideButton'];

    connect() {
        this.contentTarget.hidden = false;
        this.showButtonTarget.hidden = true;
        this.hideButtonTarget.hidden = false;
    }

    show() {
        this.contentTarget.hidden = false;
        this.showButtonTarget.hidden = true;
        this.hideButtonTarget.hidden = false;
        this.updateSettings(true);
    }

    hide() {
        this.contentTarget.hidden = true;
        this.showButtonTarget.hidden = false;
        this.hideButtonTarget.hidden = true;
        this.updateSettings(false);
    }

    updateSettings(showHelp) {
        // No need to await this, it'll just happen
        fetch('/user/updatePreferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    credentials: 'same-origin',
                    'X-CSRF-TOKEN': this.crsfValue
                },
                body: JSON.stringify({
                    'showHelp': {
                        'route':this.currentRouteValue,
                        'state':showHelp
                    }
                })
            }).then(response => {
                if (response.ok) {
                    console.log('Success!');
                } else {
                    console.log('Error!', response.statusText);
                }
        });
    }
}