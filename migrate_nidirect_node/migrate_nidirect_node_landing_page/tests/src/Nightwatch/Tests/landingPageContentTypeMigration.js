var parser = require('xml2json');
var http = require('http');
var node, nid;
const regx_strip_taxoheir = /^-*/gm;

module.exports = {
    '@tags': ['nidirect-migrations', 'nidirect-node-landing-page'],

    before: function (browser) {
        http.get(process.env.TEST_D7_URL + '/migrate/landingpage', (response) => {
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

    'Test whether Landing Page content type exists': browser => {
        browser
            .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
            .drupalRelativeURL('/admin/structure/types/manage/landing_page')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Landing page');
    },

    'Test whether Landing Page fields exist': browser => {
        browser.drupalRelativeURL('/admin/structure/types/manage/landing_page/fields');
        browser.expect.element('#field-banner-image').to.be.present;
        browser.expect.element('#field-banner-image-overlay').to.be.present;
        browser.expect.element('#body').to.be.present;
        browser.expect.element('#field-enable-title').to.be.present;
        browser.expect.element('#field-meta-tags').to.be.present;
        browser.expect.element('#field-summary').to.be.present;
        browser.expect.element('#field-teaser').to.be.present;
        browser.expect.element('#field-subtheme').to.be.present;
        browser.expect.element('#field-top-level-theme').to.be.present;
        browser.expect.element('#field-summary').to.be.present;
    },

    'Test whether Landing Page nodes exist': browser => {
        // See if we have any landing_page nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=landing_page')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Landing page');
    },

    'Test whether Landing Page content matches original': browser => {

        browser
            .drupalRelativeURL('/node/' + nid + '/edit')
            .waitForElementVisible('body', 1000)
            .expect.element('#edit-title-0-value')
            .to.have.value.which.contains(node.title);

        if (node.title_visible == 1) {
            browser.expect.element('#edit-field-enable-title-value').to.be.selected;
        } else {
            browser.expect.element('#edit-field-enable-title-value').to.not.be.selected;
        }

        browser
            .element("xpath", "//select[@id='edit-field-subtheme']/option[@selected='selected']", function (element) {
                browser.elementIdAttribute(element.value.ELEMENT, 'innerText', function (text) {
                    browser.assert.equal(text.value.replace(regx_strip_taxoheir, ''), node.subtheme);
                })
            });

        browser
            .expect.element('#edit-field-teaser-0-value')
            .to.have.value.which.contains(node.teaser);

        if (Object.keys(node.summary).length !== 0) {
            browser
                .expect.element('#edit-field-summary-0-value')
                .to.have.value.which.contains(node.summary);
        }

        if (Object.keys(node.body).length !== 0) {
            browser
                .expect.element('#edit-body-0-value')
                .to.have.value.which.contains(node.body);
        }
    }
};
