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
            .expect.element('#field_parent_condition').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_alternative_title').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_banner_image').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#body').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_hc_body_location').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_hc_body_system').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_hc_condition_type').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_enable_toc').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_additional_info').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_index_letter').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_hc_info_source').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_last_review_date').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_meta_tags').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_next_review_date').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_hc_primary_symptom_1').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_hc_primary_symptom_2').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_hc_primary_symptom_3').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_hc_primary_symptom_4').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_published_date').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_related_conditions').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_related_info').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_hc_secondary_symptoms').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_summary').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_site_topics').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_teaser').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_subtheme').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/health_condition/fields')
            .expect.element('#field_top_level_theme').to.be.present;
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
