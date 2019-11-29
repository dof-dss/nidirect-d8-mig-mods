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

  // Now test a random sample of actual nodes.
  'Check title of Embargoed Publication node': browser => {
    browser
      .pause(2000, function () {
        browser
          .drupalRelativeURL('/node/' + nid + '/edit')
          .waitForElementVisible('body', 1000)
          .expect.element('#edit-title-0-value')
          .to.have.value.which.contains(node.title);
    });
  }
};
