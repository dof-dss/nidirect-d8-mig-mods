var parser = require('xml2json');
var http = require('http');
var nid;
var node;

module.exports = {
    '@tags': ['nidirect-migrations'],

    before: function (browser) {
        http.get('http://nidirect.lndo.site/migrate/di', (response) => {
            let data = '';
            response.on('data', (chunk) => { data += chunk });

            response.on('end', () => {
                data = JSON.parse(parser.toJson(data));
                node = data.nodes.node;
                nid = node.nid;
            })
        }).on("error", (err) => {
            console.log("Error: " + err.message);
        });
    },


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
            .waitForElementVisible('body', 1000)
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Driving instructor');

        browser
            .drupalRelativeURL('/node/' + nid + '/edit')
            .waitForElementVisible('body', 1000)
            .expect.element('#edit-title-0-value')
            .to.have.value.which.contains(node.title);

        browser
            .expect.element('#edit-field-di-firstname-0-value')
            .to.have.value.which.contains(node.first_name);

        browser
            .expect.element('#edit-field-di-lastname-0-value')
            .to.have.value.which.contains(node.last_name);

        if (Object.keys(node.mobile).length !== 0) {
            browser
                .expect.element('#edit-field-contact-sms-0-value')
                .to.have.value.which.contains(node.mobile);
        }

        if (Object.keys(node.phone).length !== 0) {
            browser
                .expect.element('#edit-field-contact-phone-0-value')
                .to.have.value.which.contains(node.phone);
        }

        if (Object.keys(node.email).length !== 0) {
            browser
                .expect.element('#edit-field-email-address-0-value')
                .to.have.value.which.contains(node.email);
        }

        if (Object.keys(node.website).length !== 0) {
            browser
                .expect.element('#edit-field-link-url-0-uri')
                .to.have.value.which.contains(node.website);
        }

        browser
            .expect.element('#edit-field-di-adi-no-0-value')
            .to.have.value.which.contains(node.adi);

        browser
            .elements('xpath', "//input[starts-with(@id, 'edit-field-di-areas-')][@checked='checked']/following-sibling::label", function (elements) {
                if (elements.value.length > 0) {
                    let areas = node.areas.split(',');

                    elements.value.map(function (item) {
                        browser.elementIdText(item.ELEMENT, function (result) {
                            if (result.value.length > 0) {
                                // Check the D8 form value exists in the D7 data.
                                if (areas.includes(result.value)) {
                                    // It stinks but it's a simple way to show this assertion passes, else fail below. 
                                    browser.assert.equal(result.value, result.value);
                                } else {
                                    browser.assert.fail('field-di-areas: data mismatch on : ' + result.value);
                                }
                            }
                        });
                    });
                }
            });
    }

};
