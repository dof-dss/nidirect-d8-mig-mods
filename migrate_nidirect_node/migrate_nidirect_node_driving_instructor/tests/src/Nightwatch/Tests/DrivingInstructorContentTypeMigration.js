var fs = require('fs');

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
            .expect.element('#field-di-areas').to.be.present;
        browser
            .expect.element('#field-di-categories').to.be.present;
        browser
            .expect.element('#field-email-address').to.be.present;
        browser
            .expect.element('#field-di-firstname').to.be.present;
        browser
            .expect.element('#field-di-lastname').to.be.present;
        browser
            .expect.element('#field-contact-phone').to.be.present;
        browser
            .expect.element('#field-meta-tags').to.be.present;
        browser
            .expect.element('#field-contact-sms').to.be.present;
        browser
            .expect.element('#field-link-url').to.be.present;
    },

    'Test whether Driving Instructor nodes exist': browser => {

        // See if we have any article nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=driving_instructor')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Driving instructor');

        // Try loading a csv of nids for this content type to test against.
        try {
            fs.existsSync(__dirname + '/nids.csv')

            let nids_file = fs.readFileSync(__dirname + '/nids.csv', 'utf8');
            var nids = JSON.parse("[" + nids_file + "]");

            if (nids.length > 0) {
                // Shuffle the nids and select a sample.
                nids = nids.sort(() => 0.5 - Math.random());
                nids = nids.slice(0, 10);

                nids.forEach(nid => {
                    browser
                        .url('https://www.nidirect.gov.uk/node/' + nid)
                        .elements('xpath', '//*[@id="page-title"]', function (result) {
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
