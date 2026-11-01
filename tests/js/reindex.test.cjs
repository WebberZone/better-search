const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

// Simulate only jQuery's DOM/AJAX surface. Execute the shipped reindex script.
function screen(network = false, minified = false) {
    const requests = [];
    const elements = new Map();
    const ready = [];
    function element(key) {
        if (!elements.has(key)) elements.set(key, {
            length: key === '#bsearch-start-recency-backfill' ? 0 : 1,
            classes: new Set(), styles: {}, events: {}, value: '',
            html(value) { if (value !== undefined) this.value = value; return this; },
            text(value) { if (value !== undefined) this.value = value; return this; },
            css(key, value) { Object.assign(this.styles, typeof key === 'object' ? key : { [key]: value }); return this; },
            prop() { return this; }, show() { return this; },
            addClass(value) { value.split(' ').forEach(c => this.classes.add(c)); return this; },
            removeClass(value) { value.split(' ').forEach(c => this.classes.delete(c)); return this; },
            on(event, handler) { this.events[event] = handler; return this; },
            filter() { return this; }, map() { return { get: () => [] }; },
            is() { return false; }
        });
        return elements.get(key);
    }
    function $(selector) {
        if (typeof selector === 'function') { ready.push(selector); return; }
        return element(selector);
    }
    $.ajax = options => { requests.push(options); return { done() { return this; }, fail() { return this; }, always() { return this; } }; };
    const document = {
        createElement() {
            return { textContent: '', get innerHTML() { return this.textContent.replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;'); } };
        },
        getElementById: () => null, querySelectorAll: () => []
    };
    const context = {
        jQuery: $, document, window: {}, console: { log() {}, error() {} },
        requestAnimationFrame: cb => cb(), setTimeout: cb => cb(), clearTimeout() {}, confirm: () => true,
        bsearchReindexSettings: { ajaxurl: '/ajax', nonce: 'test', isNetworkAdmin: network,
            strings: { starting: 'Starting', completed: 'Complete', completedWithErrors: 'Review failed posts', error: 'Failed', buttonText: 'Index', clickToStop: 'Stop' } }
    };
    const filename = path.join(__dirname, '../../includes/pro/custom-tables/admin/js/reindex' + (minified ? '.min' : '') + '.js');
    vm.runInNewContext(fs.readFileSync(filename, 'utf8'), context);
    ready.forEach(cb => cb());
    return {
        requests, status: element('#bsearch-reindex-status'), bar: element('#bsearch-progress-bar'),
        start() {
            requests[0].success({ success: false, data: {} });
            requests[0].complete();
            element('#bsearch-start-reindex').events.click({ preventDefault() {} });
            return requests.at(-1);
        }
    };
}

for (const minified of [false, true]) {
    const variant = minified ? 'minified' : 'source';
    test(`${variant}: failed completion stays red and preserves the server summary`, () => {
        const ui = screen(false, minified);
        ui.start().success({ success: true, data: { processed: 3, total: 3, failed: 1, complete: true, message: 'Attempted 3; indexed 2; failed 1.' } });
        assert(ui.status.classes.has('error'));
        assert(!ui.status.classes.has('success'));
        assert.match(ui.status.value, /indexed 2; failed 1/);
        assert.equal(ui.bar.styles['background-color'], '#d63638');
        assert(!ui.requests.some(r => r.data.action === 'bsearch_stop_reindex'));
    });
    test(`${variant}: reaching the original total does not prematurely finish cursor traversal`, () => {
        const ui = screen(true, minified);
        ui.start().success({ success: true, data: { processed: 25, total: 25, failed: 0, complete: false, message: 'Attempted 25' } });
        assert.equal(ui.requests.at(-1).data.offset, 25);
        assert.equal(ui.requests.at(-1).data.is_network_admin, true);
        assert.equal(ui.requests[0].data.is_network_admin, true);
        assert(!ui.status.classes.has('success'));
    });
    test(`${variant}: a read failure preserves resumable state`, () => {
        const ui = screen(false, minified);
        ui.start().success({ success: false, data: { error: 'Could not read posts' } });
        assert(ui.status.classes.has('error'));
        assert(!ui.requests.some(r => r.data.action === 'bsearch_stop_reindex'));
    });
    test(`${variant}: an empty successful run completes without a NaN percentage`, () => {
        const ui = screen(false, minified);
        ui.start().success({ success: true, data: { processed: 0, total: 0, failed: 0, complete: true, message: 'No posts' } });
        assert(ui.status.classes.has('success'));
        assert.equal(ui.bar.styles.width, '100%');
    });
}
