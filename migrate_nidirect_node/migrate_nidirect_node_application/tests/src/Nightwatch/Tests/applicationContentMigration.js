var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_strip_taxoheir = /^-*/gm;

module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_content',
    'nidirect_content_migration',
    'nidirect_content_migration_application',
  ],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/application', (response) => {
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

  'Test whether Application content matches original': browser => {

    browser
      .pause(2000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);

        browser
          .element("xpath", "//select[@id='edit-field-subtheme']/option[@selected='selected']", function (element) {
            browser.elementIdAttribute(element.value.ELEMENT, 'innerText', function (text) {
              browser.assert.equal(text.value.replace(regx_strip_taxoheir, ''), node.subtheme);
            })
          });

        if (Object.keys(node.supplementary).length !== 0) {
          browser
            .elements("xpath", "//select[@id='edit-field-site-themes']/option[@selected='selected']", function (elements) {
              if (elements.value.length > 0) {
                let supp_themes = node.supplementary.split('|');

                elements.value.map(function (item) {
                  browser.elementIdAttribute(item.ELEMENT, 'innerText', function (result) {
                    if (result.value.length > 0) {
                      // Strip depth hyphens from the beginning of each term.
                      let text = result.value.replace(regx_strip_taxoheir, '');
                      // Check the D8 form value exists in the D7 data.
                      if (supp_themes.includes(text)) {
                        // It stinks but it's a simple way to show this assertion passes, else fail below.
                        browser.assert.equal(text, text);
                      } else {
                        browser.assert.fail('field-site-themes: data mismatch on : ' + text);
                      }
                    }
                  })
                });

                browser.assert.equal(elements.value.length, supp_themes.length, 'field-site-themes item count missmatch');
              }
            });
        }

        browser
          .expect.element('#edit-field-teaser-0-value')
          .to.have.value.which.contains(node.teaser);

        if (Object.keys(node.summary).length !== 0) {
          browser
            .expect.element('#edit-field-summary-0-value')
            .to.have.value.which.contains(node.summary);
        }

        // if (Object.keys(node.before_you_start).length !== 0) {
        //   browser
        //     .expect.element('#edit-body-0-value')
        //     .to.have.value.which.contains(node.before_you_start);
        // }

        if (Object.keys(node.link_url).length !== 0) {
          browser
            .expect.element('#edit-field-link-0-uri')
            .to.have.value.which.contains(node.link_url);
        }

        if (Object.keys(node.link_title).length !== 0) {
          // Data export renders blank titles with the url.
          if (node.link_url != node.link_title) {
            browser
              .expect.element('#edit-field-link-0-title')
              .to.have.value.which.contains(node.link_title);
          }
        }

        if (Object.keys(node.additional).length !== 0) {
          browser
            .expect.element('#edit-field-additional-info-0-value')
            .to.have.value.which.contains(node.additional);
        }
      })
  }
};
