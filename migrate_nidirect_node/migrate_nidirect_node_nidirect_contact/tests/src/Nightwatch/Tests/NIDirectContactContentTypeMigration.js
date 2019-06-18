var fs = require('fs');

module.exports = {
    '@tags': ['nidirect-migrations'],

    'Test whether NIDirect Contact content type exists': browser => {
        browser
            .drupalLogin({ name: 'admin', password: 'letmein' });

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
            .expect.element('#field-contact-address-01').to.be.present;

        browser
            .expect.element('#field-contact-address-02').to.be.present;

        browser
            .expect.element('#field-contact-address-03').to.be.present;

        browser
            .expect.element('#field-contact-address-04').to.be.present;

        browser
            .expect.element('#field-contact-address-05').to.be.present;

        browser
            .expect.element('#body').to.be.present;

        browser
            .expect.element('#field-contact-category').to.be.present;

        browser
            .expect.element('#field-email-address').to.be.present;

        browser
            .expect.element('#field-contact-emp-svcs-no').to.be.present;

        browser
            .expect.element('#field-contact-fax').to.be.present;

        browser
            .expect.element('#field-livelink-url').to.be.present;

        browser
            .expect.element('#field-meta-tags').to.be.present;

        browser
            .expect.element('#field-contact-hours').to.be.present;

        browser
            .expect.element('#field-contact-group').to.be.present;

        browser
            .expect.element('#field-contact-phone').to.be.present;

        browser
            .expect.element('#field-contact-postcode').to.be.present;

        browser
            .expect.element('#field-site-themes').to.be.present;

        browser
            .expect.element('#field-summary').to.be.present;

        browser
            .expect.element('#field-supplementary-contact').to.be.present;

        browser
            .expect.element('#field-contact-sms').to.be.present;

        browser
            .expect.element('#field-contact-town-city').to.be.present;

        browser
            .expect.element('#field-contact-website').to.be.present;
    },

    'Test whether NIDirect contact nodes exist': browser => {

        // See if we have any article nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=nidirect_contact')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Nidirect contact');

        // Now test a random sample of actual nodes.

        // Try loading a csv of nids for this content type to test against.
        try {
            fs.existsSync(__dirname + '/nids.csv')

            let nids_file = fs.readFileSync(__dirname + '/nids.csv', 'utf8');
            var nids = JSON.parse("[" + nids_file + "]");

            if (nids.length > 0) {
                // Shuffle the nids and select a sample.
                nids = nids.sort(() => 0.5 - Math.random());
                nids = nids.slice(0, 15);

                nids.forEach(nid => {
                    browser
                        .url('https://www.nidirect.gov.uk/node/' + nid)
                        .elements('xpath', '//*[@id="content"]/section/div[1]/h1', function (result) {
                            result.value.map(function (element, err) {
                                browser.elementIdAttribute(element.ELEMENT, 'innerText', function (res) {
                                    console.log(res.value)
                                    // Check that the same title appears in D8 after migration.
                                    browser
                                        .drupalRelativeURL('/node/' + nid + '/edit')
                                        .expect.element('#edit-title-0-value')
                                        .to.have.value.which.contains(res.value);
                                })
                            })
                        });
                });
            } else {
                console.error('❗️Nids file found, but no nids parsed.')
            }
        } catch (err) {
            console.error('❗️Nids file not found.')
        }
    }
};
