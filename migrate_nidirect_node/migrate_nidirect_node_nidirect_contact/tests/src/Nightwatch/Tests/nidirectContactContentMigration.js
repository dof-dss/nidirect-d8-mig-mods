var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_strip_taxoheir = /^-*/gm;

module.exports = {
  '@tags': ['nidirect-migrations', 'nidirect-node-nidirect-contact'],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/nidcontact', (response) => {
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

  'Test whether NI Direct Contact content matches original': browser => {

    browser
      .pause(9000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);

        if (Object.keys(node.category).length !== 0) {
          browser
            .element("xpath", "//select[@id='edit-field-contact-category']/*/option[@selected='selected']", function (element) {
              browser.elementIdAttribute(element.value.ELEMENT, 'innerText', function (text) {
                browser.assert.equal(text.value.replace(regx_strip_taxoheir, ''), node.category);
              })
            });
        }

        if (Object.keys(node.parent_contact).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-contact-group-0-target-id')
            .to.have.value.which.contains(node.parent_contact);
        }

        if (Object.keys(node.supplementary_contact).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-supplementary-contact-0-value')
            .to.have.value.which.contains(node.supplementary_contact);
        }

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

        if (Object.keys(node.email).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-email-address-0-value')
            .to.have.value.which.contains(node.email);
        }

        if (Object.keys(node.website_url).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-contact-website-0-uri')
            .to.have.value.which.contains(node.website_url);
        }

        if (Object.keys(node.website_title).length !== 0) {
          if (node.website_title != node.website_url) {
            browser
              .useCss()
              .expect.element('#edit-field-contact-website-0-title')
              .to.have.value.which.contains(node.website_title);
          }
        }

        if (Object.keys(node.fax).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-contact-fax-0-value')
            .to.have.value.which.contains(node.fax);
        }

        if (Object.keys(node.text_number).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-contact-sms-0-value')
            .to.have.value.which.contains(node.text_number);
        }

        if (Object.keys(node.opening_hours).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-contact-hours-0-value')
            .to.have.value.which.contains(node.opening_hours);
        }

        if (Object.keys(node.additional).length !== 0) {
          browser
            .useCss()
            .expect.element('#edit-field-contact-additional-info-0-value')
            .to.have.value.which.contains(node.additional);
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
                        browser.assert.fail('field-site-themes: data mismatch on : ' + text);
                      }
                    }
                  })
                });

                browser.assert.equal(elements.value.length, supp_themes.length, 'field-site-themes item count match');
              }
            });
        }
      });
  }
};
