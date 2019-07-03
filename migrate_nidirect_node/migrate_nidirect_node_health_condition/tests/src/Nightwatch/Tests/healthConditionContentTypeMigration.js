module.exports = {
    '@tags': ['nidirect-migrations', 'nidirect-node-health-condition'],

    'Test whether Health Condition content type exists': browser => {
        browser
            .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
            .drupalRelativeURL('/admin/structure/types/manage/health_condition')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Health condition');
    },

    'Test whether Health Condition fields exist': browser => {
        browser.drupalRelativeURL('/admin/structure/types/manage/health_condition/fields');
        browser.expect.element('#field-parent-condition').to.be.present;
        browser.expect.element('#field-alternative-title').to.be.present;
        browser.expect.element('#field-banner-image').to.be.present;
        browser.expect.element('#body').to.be.present;
        browser.expect.element('#field-hc-body-location').to.be.present;
        browser.expect.element('#field-hc-body-system').to.be.present;
        browser.expect.element('#field-hc-condition-type').to.be.present;
        browser.expect.element('#field-enable-toc').to.be.present;
        browser.expect.element('#field-additional-info').to.be.present;
        browser.expect.element('#field-index-letter').to.be.present;
        browser.expect.element('#field-hc-info-source').to.be.present;
        browser.expect.element('#field-last-review-date').to.be.present;
        browser.expect.element('#field-meta-tags').to.be.present;
        browser.expect.element('#field-next-review-date').to.be.present;
        browser.expect.element('#field-hc-primary-symptom-1').to.be.present;
        browser.expect.element('#field-hc-primary-symptom-2').to.be.present;
        browser.expect.element('#field-hc-primary-symptom-3').to.be.present;
        browser.expect.element('#field-hc-primary-symptom-4').to.be.present;
        browser.expect.element('#field-published-date').to.be.present;
        browser.expect.element('#field-related-conditions').to.be.present;
        browser.expect.element('#field-related-info').to.be.present;
        browser.expect.element('#field-hc-secondary-symptoms').to.be.present;
        browser.expect.element('#field-summary').to.be.present;
        browser.expect.element('#field-site-themes').to.be.present;
        browser.expect.element('#field-teaser').to.be.present;
        browser.expect.element('#field-subtheme').to.be.present;
        browser.expect.element('#field-top-level-theme').to.be.present;
    },

    'Test whether Health Condition nodes exist': browser => {

        // See if we have any health_condition nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=health_condition')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Health condition');
    }
};
