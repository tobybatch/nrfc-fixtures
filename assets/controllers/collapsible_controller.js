import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['content', 'icon'];

    toggle() {
        this.contentTarget.classList.toggle('max-h-0');
        this.contentTarget.classList.toggle('h-auto');
        this.iconTarget.classList.toggle('rotate-90');
        const isExpanded = this.element.getAttribute('aria-expanded') === 'true';
        this.element.setAttribute('aria-expanded', !isExpanded);
    }
}