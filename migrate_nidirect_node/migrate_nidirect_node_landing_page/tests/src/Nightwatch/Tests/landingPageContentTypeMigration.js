module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_config',
    'nidirect_config_landing_page',
  ],

    'Test whether Landing Page content type exists': browser => {
        browser
            .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
            .drupalRelativeURL('/admin/structure/types/manage/landing_page')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Landing page');
    },

    'Test whether Landing Page fields exist': browser => {
        browser.drupalRelativeURL('/admin/structure/types/manage/landing_page/fields');
        browser.expect.element('#field-banner-image').to.be.present;
        browser.expect.element('#field-banner-image-overlay').to.be.present;
        browser.expect.element('#body').to.be.present;
        browser.expect.element('#field-enable-title').to.be.present;
        browser.expect.element('#field-meta-tags').to.be.present;
        browser.expect.element('#field-summary').to.be.present;
        browser.expect.element('#field-teaser').to.be.present;
        browser.expect.element('#field-subtheme').to.be.present;
        browser.expect.element('#field-top-level-theme').to.be.present;
        browser.expect.element('#field-summary').to.be.present;
    },

    'Test whether Landing Page nodes exist': browser => {
        // See if we have any landing_page nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=landing_page')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Landing page');
    }
};
