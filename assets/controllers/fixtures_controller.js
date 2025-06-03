import {Controller} from '@hotwired/stimulus';
import {updateSettings} from '../utilities';

export default class extends Controller {
    static values = {csrf: String};
    static targets = ['hasPastFixtures', 'fixturesRow', 'fixturesRowPast', 'fixturesRowToggle'];

    connect() {
        console.log('Fixtures controller connected', this.hasPastFixturesValue)
        if (this.hasPastFixturesValue === "Yes") {
            window.AppData.showPastFixtures = window.AppData.showPastFixtures ?? true;
            this.fixturesRowPastTarget.hidden = window.AppData.showPastFixtures;
            this.fixturesRowToggleTarget.innerHTML = window.AppData.showPastFixtures ? 'Show past fixtures' : 'Hide past fixtures';
        }
    }

    update() {
        if (this.hasPastFixturesValue === "Yes") {
            this.fixturesRowPastTarget.hidden = !window.AppData.showPastFixtures;
            window.AppData.showPastFixtures = !window.AppData.showPastFixtures;
            this.fixturesRowToggleTarget.innerHTML = window.AppData.showPastFixtures ? 'Show past fixtures' : 'Hide past fixtures';
            updateSettings("showPastFixtures", !window.AppData.showPastFixtures);
        }
    }
}