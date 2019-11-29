var parser = require('xml2json');
var http = require('http');
var node, nid;
const regx_strip_taxoheir = /^-*/gm;
const regx_spaceless_html = /(^|>)[ \n\t]+/g;

module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_content',
    'nidirect_content_migration',
    'nidirect_content_migration_landing_page',
  ],

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

  'Test whether Landing Page content matches original': browser => {

    browser
      .pause(2000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
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
            .expect.element('textarea[data-drupal-selector="edit-field-summary-0-value"]')
            .to.have.value.which.contains(node.summary.replace(regx_spaceless_html, ">"));
        }

        if (Object.keys(node.body).length !== 0) {
          browser
            .expect.element('textarea[data-drupal-selector="edit-body-0-value"]')
            .to.have.value.which.contains(node.body.replace(regx_spaceless_html, ">"));
        }

      });
  }
};
