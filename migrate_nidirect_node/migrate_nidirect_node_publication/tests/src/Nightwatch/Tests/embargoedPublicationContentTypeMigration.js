module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_config',
    'nidirect_config_embargoed_publication',
  ],

    'Test whether Embargoed Publication content type exists': browser => {
        browser
            .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
            .drupalRelativeURL('/admin/structure/types/manage/embargoed_publication')
            // The text match takes a regex, not a literal string.
            .expect.element('h1.page-title').text.to.match(/edit embargoed publication content type/i);
    },

    'Test whether Embargoed Publication fields exist': browser => {
        browser.drupalRelativeURL('/admin/structure/types/manage/embargoed_publication/fields');
        browser.expect.element('#body').to.be.present;
        browser.expect.element('#field-meta-tags').to.be.present;
        browser.expect.element('#field-published-date').to.be.present;
        browser.expect.element('#field-secure-attachment').to.be.present;
        browser.expect.element('#field-summary').to.be.present;
        browser.expect.element('#field-site-themes').to.be.present;
        browser.expect.element('#field-subtheme').to.be.present;
        browser.expect.element('#field-top-level-theme').to.be.present;
        browser.expect.element('#field-publication-type').to.be.present;
    }

};
