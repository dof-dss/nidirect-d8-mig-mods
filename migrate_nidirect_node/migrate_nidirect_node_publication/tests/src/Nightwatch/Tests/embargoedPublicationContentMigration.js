var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_strip_taxoheir = /^-*/gm;
const regx_strip_html = /<([^>]+)>/ig;
const regx_spaceless_html = /(^|>)[ \n\t]+/g;

module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_content',
    'nidirect_content_migration',
    'nidirect_content_migration_embargoed_publication',
  ],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/embargoed_publication', (response) => {
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

  'Test whether Embargoed Publication nodes exist': browser => {
    // See if we have any landing_page nodes created.
    browser
      .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
      .drupalRelativeURL('/admin/content?type=embargoed_publication')
      .expect.element('#views-form-content-page-1 > table > tbody > tr > td:nth-child(3)')
      .text.to.contain('Embargoed publication');
  },

  'Test whether Embargoed Publication content matches original': browser => {

    browser
      .pause(9000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);

        browser
          .useCss()
          .expect.element('#edit-field-published-date-0-value-date')
          .to.have.value.which.contains(node.published_date.replace(regx_strip_html, ''));

        if (Object.keys(node.summary).length !== 0) {
          browser
            .expect.element('textarea[data-drupal-selector="edit-field-summary-0-value"]')
            .to.have.value.which.contains(node.summary.replace(/(^|>)[ \n\t]+/g, ">"));
        }

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

        if (Object.keys(node.body).length !== 0) {
          browser
            .expect.element('textarea[data-drupal-selector="edit-body-0-value"]')
            .to.have.value.which.contains(node.body.replace(/(^|>)[ \n\t]+/g, ">"));
        }

        if (Object.keys(node.publication_type).length !== 0) {
          browser
            .useXpath()
            .expect.element('//*[@id="edit-field-publication-type"]/option[@selected="selected"]')
            .to.have.value.which.contains(node.publication_type);
        }

      });

  }

};
