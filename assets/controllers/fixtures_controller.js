import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    // static values = {
    //     routeParams: Object,
    // };
    static targets = ['fixturesRow'];

    connect() {
        console.log("CONNECT - do nothing, it's handled in the template")
        console.log("CONNECT", window.AppData.showPastFixtures)
        // this.fixturesRow.hidden = window.AppData.showHistoricFixtures;
        this.fixturesRowTarget.hidden = false;
    }

    show() {
        console.log("SHOW", window.AppData)
    }

    hide() {
        console.log("HIDE", window.AppData)
    }
}