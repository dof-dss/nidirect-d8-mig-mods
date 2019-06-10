module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether Health Condition content type exists': browser => {
        browser
            .drupalLogin({name: 'admin', password: 'letmein'});

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Health condition');
    },

    'Test whether Health Condition fields exist': browser => {

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-parent-condition').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-alternative-title').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-banner-image').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#body').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-hc-body-location').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-hc-body-system').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-hc-condition-type').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-enable-toc').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-additional-info').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-index-letter').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-hc-info-source').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-last-review-date').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-meta-tags').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-next-review-date').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-hc-primary-symptom-1').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-hc-primary-symptom-2').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-hc-primary-symptom-3').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-hc-primary-symptom-4').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-published-date').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-related-conditions').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-related-info').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-hc-secondary-symptoms').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-summary').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-site-topics').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-teaser').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-subtheme').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field-top-level-theme').to.be.present;
    },

    'Test whether Article nodes exist': browser => {

        // See if we have any health_condition nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=health_condition')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Article');

        // Now test a random sample of actual nodes.

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/9474')
            .elements('css selector', '#main-area div #contentTypeArticle div:nth-child(2) h1', function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function(res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/9474/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });



        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/1210')
            .elements('css selector', '#main-area div #contentTypeArticle div h1', function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function(res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/1210/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/2488')
            .elements('css selector', '#main-area div #contentTypeArticle div h1', function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function(res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/2488/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });


    }
};
