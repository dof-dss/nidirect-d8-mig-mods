var parser = require('xml2json');
var http = require('http');
var nid, node;
const regx_spaceless_html = /(^|>)[ \n\t]+/g;

module.exports = {
  '@tags': [
    'nidirect',
    'nidirect_content',
    'nidirect_content_migration',
    'nidirect_content_migration_page',
  ],

  before: function (browser) {
    http.get(process.env.TEST_D7_URL + '/migrate/page', (response) => {
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

  'Test whether External Link content matches original': browser => {

    browser
      .pause(9000, function () {
        browser
          .drupalLogin({ name: process.env.TEST_USER, password: process.env.TEST_PASS })
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);

        if (node.enable_toc == 1) {
          browser.expect.element('#edit-field-enable-toc-value').to.be.selected;
        } else {
          browser.expect.element('#edit-field-enable-toc-value').to.not.be.selected;
        }

        if (Object.keys(node.body).length !== 0) {
          browser
            .useCss()
            .expect.element('textarea[data-drupal-selector="edit-body-0-value"]')
            .to.have.value.which.contains(node.body.replace(regx_spaceless_html, ">"));
        }
      })
  }
};
