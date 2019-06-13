module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether Driving Instructor content type exists': browser => {
        browser
            .drupalLogin({ name: 'admin', password: 'letmein' });

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Driving instructor');
    },

    'Test whether Driving Instructor fields exist': browser => {

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-di-adi-no').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-di-areas').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-di-categories').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-email-address').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-di-firstname').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-di-lastname').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-contact-phone').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-meta-tags').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-contact-sms').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/driving_instructor/fields')
            .expect.element('#field-link-url').to.be.present;
    },

    'Test whether Driving Instructor nodes exist': browser => {

        // See if we have any article nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=driving_instructor')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Driving instructor');

        // Now test a random sample of actual nodes.

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/11550')
            .elements('xpath', '//*[@id="page-title"]', function (result) {
                result.value.map(function (element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function (res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/11550/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/11622')
            .elements('xpath', '//*[@id="page-title"]', function (result) {
                result.value.map(function (element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function (res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/11622/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/11713')
            .elements('xpath', '//*[@id="page-title"]', function (result) {
                result.value.map(function (element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function (res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/11713/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            })
    }
};
