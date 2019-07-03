var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_strip_taxoheir = /^-*/gm;

module.exports = {
    '@tags': ['nidirect-migrations', 'nidirect-article'],

    before: function (browser) {
        http.get(process.env.TEST_D7_URL + '/migrate/article', (response) => {
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

    'Test whether Article content type exists': browser => {
        browser
            .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
            .drupalRelativeURL('/admin/structure/types/manage/article')
            .expect.element('form > div > #edit-name')
            .value.to.contain('Article');
    },

    'Test whether Article fields exist': browser => {
        browser.drupalRelativeURL('/admin/structure/types/manage/article/fields');
        browser.expect.element('#field-banner-image').to.be.present;
        browser.expect.element('#body').to.be.present;
        browser.expect.element('#comment').to.be.present;
        browser.expect.element('#field-enable-toc').to.be.present;
        browser.expect.element('#field-additional-info').to.be.present;
        browser.expect.element('#field-meta-tags').to.be.present;
        browser.expect.element('#field-photo').to.be.present;
        browser.expect.element('#field-summary').to.be.present;
        browser.expect.element('#field-site-themes').to.be.present;
        browser.expect.element('#field-teaser').to.be.present;
        browser.expect.element('#field-subtheme').to.be.present;
        browser.expect.element('#field-top-level-theme').to.be.present;
    },

    'Test whether Article nodes exist': browser => {

        // See if we have any article nodes created.
        browser
            .drupalRelativeURL('/admin/content?type=article')
            .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
            .text.to.contain('Article');
    },


    'Test whether Article content matches original': browser => {
        browser
            .drupalRelativeURL('/node/' + nid + '/edit')
            .waitForElementVisible('body', 1000)
            .expect.element('#edit-title-0-value')
            .to.have.value.which.contains(node.title);

        if (Object.keys(node.subtheme).length !== 0) {
            browser
                .elements("xpath", "//select[@id='edit-field-subtheme']/option[@selected='selected']", function (elements) {
                    if (elements.value.length > 0) {
                        let subtheme = node.subtheme.split('|');

                        elements.value.map(function (item) {
                            browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                                if (result.value.length > 0) {
                                    let text = result.value.replace(regx_strip_taxoheir, '');

                                    if (subtheme.includes(text)) {
                                        browser.assert.equal(text, text);
                                    } else {
                                        browser.assert.fail('field-site-themes: data mismatch on : ' + text);
                                    }
                                }
                            })
                        });

                        browser.assert.equal(elements.value.length, subtheme.length, 'field-site-themes item count match');
                    }
                });
        }

        if (Object.keys(node.supplementary).length !== 0) {
            browser
                .elements("xpath", "//select[@id='edit-field-site-themes']/option[@selected='selected']", function (elements) {
                    if (elements.value.length > 0) {
                        let supp_themes = node.supplementary.split('|');

                        elements.value.map(function (item) {
                            browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                                if (result.value.length > 0) {
                                    let text = result.value.replace(regx_strip_taxoheir, '');

                                    if (supp_themes.includes(text)) {
                                        browser.assert.equal(text, text);
                                    } else {
                                        browser.assert.fail('field-subtheme: data mismatch on : ' + text);
                                    }
                                }
                            })
                        });

                        browser.assert.equal(elements.value.length, supp_themes.length, 'field-subtheme item count match');
                    }
                });
        }

        if (Object.keys(node.teaser).length !== 0) {
            browser
                .useCss()
                .expect.element('#edit-field-teaser-0-value')
                .to.have.value.which.contains(node.teaser);
        }

        if (Object.keys(node.summary).length !== 0) {
            browser
                .useCss()
                .expect.element('#edit-field-summary-0-value')
                .to.have.value.which.contains(node.summary);
        }

        browser
            .useCss()
            .expect.element('#edit-body-0-value')
            .to.have.value.which.contains(node.body);

        if (Object.keys(node.footer).length !== 0) {
            browser
                .useCss()
                .expect.element('#edit-field-additional-info-0-value')
                .to.have.value.which.contains(node.footer);
        }

        if (node.enable_toc == 1) {
            browser.expect.element('#edit-field-enable-toc-value').to.be.selected;
          } else {
            browser.expect.element('#edit-field-enable-toc-value').to.not.be.selected;
          }
    }
};
