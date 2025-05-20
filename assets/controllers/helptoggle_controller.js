import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        currentRoute: String,
        routeParams: Object,
        csrf: String,
        pagehelpisvisible: String,
    };
    static targets = ['content', 'showButton', 'hideButton'];

    connect() {
        const toggle = (this.pagehelpisvisibleValue === "true")
        this.contentTarget.hidden =    !toggle;
        this.hideButtonTarget.hidden = !toggle;
        this.showButtonTarget.hidden = toggle;
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
        const currentRoute = this.currentRouteValue;
        const preferencesKey = 'showHelp.' + currentRoute;
        // No need to await this, it'll just happen
        fetch('/user/updatePreferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    credentials: 'same-origin',
                    'X-CSRF-TOKEN': this.crsfValue
                },
                body: JSON.stringify({[preferencesKey]: showHelp})
            }).then(response => {
                if (!response.ok) {
                    console.log('Error!', response.statusText);
                }
        });
    }
}