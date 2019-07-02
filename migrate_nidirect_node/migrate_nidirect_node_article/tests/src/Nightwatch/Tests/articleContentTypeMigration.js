module.exports = {
    '@tags': ['nidirect-migrations', 'nidirect-article'],

    'Test whether Article content type exists': browser => {
        browser
            .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
            .drupalRelativeURL('/admin/structure/types/manage/article')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Article');
    },

    'Test whether Article fields exist': browser => {
        browser.drupalRelativeURL('/admin/structure/types/manage/article/fields');
        browser.expect.element('#field-banner-image').to.be.present;
        browser.expect.element('#body').to.be.present;
        browser.expect.element('#comment').to.be.present;
        browser.expect.element('#field-enable-toc').to.be.present;
        browser.expect.element('#field-additional-info').to.be.present;
        browser.expect.element('#field-meta-tags').to.be.present;
        browser.expect.element('#field-photo').to.be.present;
        browser.expect.element('#field-summary').to.be.present;
        browser.expect.element('#field-site-themes').to.be.present;
        browser.expect.element('#field-teaser').to.be.present;
        browser.expect.element('#field-subtheme').to.be.present;
        browser.expect.element('#field-top-level-theme').to.be.present;
    },

    'Test whether Article nodes exist': browser => {
        // See if we have any article nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=article')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Article');
    }
};
