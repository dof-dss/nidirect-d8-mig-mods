module.exports = {
    '@tags': ['nidirect-migrations', 'nidirect-embargoed-publication'],

    'Test whether Embargoed Publication content type exists': browser => {
        browser
            .drupalLogin({ name: 'admin', password: 'letmein' });

        browser
            .drupalRelativeURL('/admin/structure/types/manage/embargoed_publication')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Embargoed publication');
    },

    'Test whether Embargoed Publication fields exist': browser => {

        browser
            .drupalRelativeURL('/admin/structure/types/manage/embargoed_publication/fields')
            .expect.element('#body').to.be.present;

        browser
            .expect.element('#field-meta-tags').to.be.present;

        browser
            .expect.element('#field-published-date').to.be.present;

        browser
            .expect.element('#field-secure-attachment').to.be.present;

        browser
            .expect.element('#field-summary').to.be.present;

        browser
            .expect.element('#field-site-themes').to.be.present;

        browser
            .expect.element('#field-subtheme').to.be.present;

        browser
            .expect.element('#field-top-level-theme').to.be.present;

        browser
            .expect.element('#field-publication-type').to.be.present;
    },

    'Test whether Embargoed Publication nodes exist': browser => {

        // See if we have any landing_page nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=embargoed_publication')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Embargoed publication');
    },

    // Now test a random sample of actual nodes.

    'Check title of Embargoed Publication node': browser => {
        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/12016')
            .elements('xpath', "//div[@class='panel-panel panel-banner-top clearfix']/h1", function (result) {
                result.value.map(function (element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function (res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/12016/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });
    }

};
