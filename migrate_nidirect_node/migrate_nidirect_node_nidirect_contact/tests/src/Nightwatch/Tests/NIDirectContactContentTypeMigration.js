module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether NIDirect Contact content type exists': browser => {
        browser
            .drupalLogin({name: 'admin', password: 'letmein'});

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Nidirect contact');
    },

    'Test whether NIDirect Contact fields exist': browser => {

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-additional-info').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-address-01').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-address-02').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-address-03').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-address-04').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-address-05').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#body').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-category').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-email-address').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-emp-svcs-no').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-fax').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-livelink-url').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-meta-tags').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-hours').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-group').to.be.present;
        
        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-phone').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-postcode').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-site-themes').to.be.present;
        
        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-summary').to.be.present;
            
        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-supplementary-contact').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-sms').to.be.present;
            
        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-town-city').to.be.present;

        browser
            .drupalRelativeURL('/admin/structure/types/manage/nidirect_contact/fields')
            .expect.element('#field-contact-website').to.be.present;          
    },

    'Test whether NIDirect contact nodes exist': browser => {

        // See if we have any article nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=nidirect_contact')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Nidirect contact');

        // Now test a random sample of actual nodes.

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/54')
            .elements('xpath', '//*[@id="content"]/section/div[1]/h1', function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function(res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/54/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/351')
            .elements('xpath', '//*[@id="content"]/section/div[1]/h1', function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function(res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/351/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });

        // Extract title from old NIDirect page.
        browser
            .url('https://www.nidirect.gov.uk/node/99')
            .elements('xpath', '//*[@id="content"]/section/div[1]/h1', function(result) {
                result.value.map(function(element, err) {
                    browser.elementIdAttribute(element.ELEMENT, 'innerText', function(res) {
                        // Check that the same title appears in D8 after migration.
                        browser
                            .drupalRelativeURL('/node/99/edit')
                            .expect.element('#edit-title-0-value')
                            .to.have.value.which.contains(res.value);
                    })
                })
            });
    }
};
